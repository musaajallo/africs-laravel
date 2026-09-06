<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\VaultEntryRequest;
use App\Models\VaultEntry;
use App\Models\VaultFolder;
use App\Services\VaultExporter;
use App\Support\ActivityPresenter;
use App\Support\Rbac;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VaultController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', VaultEntry::class);

        $filters = [
            'search' => $request->string('search')->trim()->value(),
            'folder' => $request->integer('folder') ?: null,
        ];

        $entries = VaultEntry::query()
            ->with('folder:id,name')
            ->search($filters['search'])
            ->when($filters['folder'], fn ($q) => $q->where('folder_id', $filters['folder']))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (VaultEntry $entry) => $this->summary($entry));

        return Inertia::render('Console/Vault/Index', [
            'entries' => $entries,
            'filters' => $filters,
            'folders' => VaultFolder::withCount('entries')->orderBy('name')->get(['id', 'name']),
            'unlocked' => $this->isUnlocked($request),
            'unlockTtl' => $this->ttl(),
            'canManage' => $request->user()->can(Rbac::PERM_VAULT_MANAGE),
            'kdbxEnabled' => (bool) config('vault.kdbx_python'),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', VaultEntry::class);

        return Inertia::render('Console/Vault/Create', $this->formOptions());
    }

    public function store(VaultEntryRequest $request): RedirectResponse
    {
        $this->authorize('create', VaultEntry::class);

        $entry = new VaultEntry($request->entryAttributes());
        $entry->created_by = $request->user()->id;
        $entry->save();

        return redirect()
            ->route('console.vault.show', $entry)
            ->with('success', 'Entry saved.');
    }

    public function show(Request $request, VaultEntry $entry): Response
    {
        $this->authorize('view', $entry);

        $entry->load('folder:id,name', 'createdBy:id,name');

        return Inertia::render('Console/Vault/Show', [
            'entry' => $this->present($entry),
            'unlocked' => $this->isUnlocked($request),
            'unlockTtl' => $this->ttl(),
            'canManage' => $request->user()->can(Rbac::PERM_VAULT_MANAGE),
            'activity' => Activity::forSubject($entry)
                ->with('causer:id,name')
                ->latest()
                ->limit(25)
                ->get()
                ->map(fn (Activity $a) => ActivityPresenter::present($a)),
        ]);
    }

    public function edit(Request $request, VaultEntry $entry): Response
    {
        $this->authorize('update', $entry);

        $unlocked = $this->isUnlocked($request);

        if ($unlocked) {
            activity()->performedOn($entry)->causedBy($request->user())->event('revealed')
                ->log("Opened “{$entry->title}” for editing");
        }

        return Inertia::render('Console/Vault/Edit', [
            'entry' => $this->present($entry, withSecrets: $unlocked),
            'unlocked' => $unlocked,
            ...$this->formOptions(),
        ]);
    }

    public function update(VaultEntryRequest $request, VaultEntry $entry): RedirectResponse
    {
        $this->authorize('update', $entry);
        $this->assertUnlocked($request);

        $entry->update($request->entryAttributes());

        return redirect()
            ->route('console.vault.show', $entry)
            ->with('success', 'Entry updated.');
    }

    public function destroy(VaultEntry $entry): RedirectResponse
    {
        $this->authorize('delete', $entry);

        $entry->delete();

        return redirect()
            ->route('console.vault.index')
            ->with('success', 'Entry deleted.');
    }

    public function restore(int $entry): RedirectResponse
    {
        $entry = VaultEntry::onlyTrashed()->findOrFail($entry);
        $this->authorize('restore', $entry);

        $entry->restore();

        return redirect()->route('console.vault.show', $entry)->with('success', 'Entry restored.');
    }

    /** Re-confirm the current user's password to unlock reveals for a while. */
    public function unlock(Request $request): JsonResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_VAULT_VIEW), 403);

        $request->validate(['password' => ['required', 'string']]);

        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->input('password'),
        ])) {
            throw ValidationException::withMessages(['password' => __('auth.password')]);
        }

        $request->session()->put('vault.unlocked_at', now()->timestamp);

        return response()->json(['unlocked_until' => now()->addSeconds($this->ttl())->toIso8601String()]);
    }

    public function lock(Request $request): JsonResponse
    {
        $request->session()->forget('vault.unlocked_at');

        return response()->json(['locked' => true]);
    }

    /** Return the decrypted secret values for one entry (audited). */
    public function reveal(Request $request, VaultEntry $entry): JsonResponse
    {
        $this->authorize('reveal', $entry);
        $this->assertUnlocked($request);

        activity()
            ->performedOn($entry)
            ->causedBy($request->user())
            ->event('revealed')
            ->log("Revealed “{$entry->title}”");

        return response()->json([
            'password' => $entry->password,
            'notes' => $entry->notes,
            'totp_secret' => $entry->totp_secret,
            'custom' => collect($entry->custom_fields ?? [])
                ->map(fn ($f) => [
                    'label' => $f['label'] ?? '',
                    'value' => $f['value'] ?? '',
                    'secret' => (bool) ($f['secret'] ?? false),
                ])->all(),
        ]);
    }

    public function exportXml(Request $request, VaultExporter $exporter): StreamedResponse
    {
        $this->authorize('viewAny', VaultEntry::class);
        abort_unless($request->user()->can(Rbac::PERM_VAULT_MANAGE), 403);
        $this->assertUnlocked($request);

        activity()->causedBy($request->user())->event('exported')->log('Vault exported as KeePass 2 XML');

        $xml = $exporter->toKeePassXml();

        return response()->streamDownload(
            fn () => print $xml,
            'africs-vault-'.now()->format('Y-m-d').'.xml',
            ['Content-Type' => 'application/xml'],
        );
    }

    public function exportKdbx(Request $request, VaultExporter $exporter): StreamedResponse
    {
        $this->authorize('viewAny', VaultEntry::class);
        abort_unless($request->user()->can(Rbac::PERM_VAULT_MANAGE), 403);
        $this->assertUnlocked($request);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:512'],
        ]);

        try {
            $path = $exporter->toKdbx($data['password']);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['password' => $e->getMessage()]);
        }

        activity()->causedBy($request->user())->event('exported')->log('Vault exported as .kdbx');

        return response()->streamDownload(
            function () use ($path) {
                readfile($path);
                @unlink($path);
            },
            'africs-vault-'.now()->format('Y-m-d').'.kdbx',
            ['Content-Type' => 'application/octet-stream'],
        );
    }

    // ---- folders -------------------------------------------------------

    public function folderStore(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_VAULT_MANAGE), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);

        VaultFolder::create($data);

        return back()->with('success', 'Folder added.');
    }

    public function folderUpdate(Request $request, VaultFolder $folder): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_VAULT_MANAGE), 403);
        $folder->update($request->validate(['name' => ['required', 'string', 'max:120']]));

        return back()->with('success', 'Folder renamed.');
    }

    public function folderDestroy(Request $request, VaultFolder $folder): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_VAULT_MANAGE), 403);
        $folder->delete(); // entries fall back to "unfiled"

        return back()->with('success', 'Folder deleted.');
    }

    // ---- helpers ------------------------------------------------------

    private function ttl(): int
    {
        return (int) config('vault.unlock_ttl', 300);
    }

    private function isUnlocked(Request $request): bool
    {
        $at = $request->session()->get('vault.unlocked_at');

        return $at !== null && (now()->timestamp - (int) $at) < $this->ttl();
    }

    private function assertUnlocked(Request $request): void
    {
        abort_unless($this->isUnlocked($request), 423, 'Vault is locked.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'folders' => VaultFolder::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(VaultEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'username' => $entry->username,
            'url' => $entry->url,
            'folder' => $entry->folder?->name,
            'folder_id' => $entry->folder_id,
            'has_password' => filled($entry->getRawOriginal('password')),
            'has_otp' => filled($entry->getRawOriginal('totp_secret')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function present(VaultEntry $entry, bool $withSecrets = false): array
    {
        $custom = collect($entry->custom_fields ?? [])->map(fn ($f) => [
            'label' => $f['label'] ?? '',
            'value' => $withSecrets || ! ($f['secret'] ?? false) ? ($f['value'] ?? '') : null,
            'secret' => (bool) ($f['secret'] ?? false),
        ])->all();

        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'folder_id' => $entry->folder_id,
            'folder' => $entry->folder?->name,
            'related_subscription_id' => $entry->related_subscription_id,
            'username' => $entry->username,
            'url' => $entry->url,
            'notes' => $withSecrets ? $entry->notes : null,
            'totp_secret' => $withSecrets ? $entry->totp_secret : null,
            'password' => $withSecrets ? $entry->password : null,
            'has_password' => filled($entry->getRawOriginal('password')),
            'has_notes' => filled($entry->getRawOriginal('notes')),
            'has_otp' => filled($entry->getRawOriginal('totp_secret')),
            'custom' => $custom,
            'created_by' => $entry->createdBy?->name,
            'created_at' => $entry->created_at?->toDateString(),
            'updated_at' => $entry->updated_at?->toDateString(),
            'archived' => $entry->trashed(),
        ];
    }
}
