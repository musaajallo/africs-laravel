<?php

namespace App\Services;

use App\Models\VaultEntry;
use App\Models\VaultFolder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Turns the vault into portable formats:
 *  - KeePass 2 XML  (File → Import → "KeePass 2 XML (2.x)" in KeePass/KeePassXC)
 *  - a real .kdbx    (opens directly; built by a pykeepass helper script)
 */
class VaultExporter
{
    private const DB_NAME = 'Africs Vault';

    /** Build the structured tree once; both exporters consume it. */
    private function tree(): array
    {
        $folders = VaultFolder::query()->orderBy('name')->get();
        $entries = VaultEntry::query()->orderBy('title')->get();

        $groups = $folders->map(fn (VaultFolder $folder) => [
            'name' => $folder->name,
            'entries' => $this->entryRows($entries->where('folder_id', $folder->id)),
        ])->values()->all();

        $unfiled = $this->entryRows($entries->whereNull('folder_id'));
        if ($unfiled !== []) {
            $groups[] = ['name' => 'Unfiled', 'entries' => $unfiled];
        }

        return $groups;
    }

    /**
     * @param  Collection<int, VaultEntry>  $entries
     * @return list<array<string, mixed>>
     */
    private function entryRows(Collection $entries): array
    {
        return $entries->map(function (VaultEntry $entry) {
            $custom = collect($entry->custom_fields ?? [])
                ->map(fn ($f) => [
                    'label' => (string) ($f['label'] ?? ''),
                    'value' => (string) ($f['value'] ?? ''),
                    'secret' => (bool) ($f['secret'] ?? false),
                ])
                ->filter(fn ($f) => $f['label'] !== '')
                ->values()
                ->all();

            return [
                'title' => $entry->title,
                'username' => (string) $entry->username,
                'password' => (string) $entry->password,
                'url' => (string) $entry->url,
                'notes' => (string) $entry->notes,
                'otp' => (string) $entry->totp_secret,
                'custom' => $custom,
            ];
        })->values()->all();
    }

    public function toKeePassXml(): string
    {
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->formatOutput = true;

        $file = $doc->appendChild($doc->createElement('KeePassFile'));

        $meta = $file->appendChild($doc->createElement('Meta'));
        $meta->appendChild($doc->createElement('Generator', 'Africs Console'));
        $meta->appendChild($doc->createElement('DatabaseName', self::DB_NAME));

        $root = $file->appendChild($doc->createElement('Root'));
        $top = $root->appendChild($this->xmlGroup($doc, self::DB_NAME));

        foreach ($this->tree() as $group) {
            $g = $top->appendChild($this->xmlGroup($doc, $group['name']));

            foreach ($group['entries'] as $entry) {
                $g->appendChild($this->xmlEntry($doc, $entry));
            }
        }

        return $doc->saveXML();
    }

    private function xmlGroup(\DOMDocument $doc, string $name): \DOMElement
    {
        $g = $doc->createElement('Group');
        $g->appendChild($doc->createElement('UUID', base64_encode(random_bytes(16))));
        $g->appendChild($doc->createElement('Name'))->appendChild($doc->createTextNode($name));

        return $g;
    }

    private function xmlEntry(\DOMDocument $doc, array $entry): \DOMElement
    {
        $e = $doc->createElement('Entry');
        $e->appendChild($doc->createElement('UUID', base64_encode(random_bytes(16))));

        $this->xmlString($doc, $e, 'Title', $entry['title']);
        $this->xmlString($doc, $e, 'UserName', $entry['username']);
        $this->xmlString($doc, $e, 'Password', $entry['password'], protected: true);
        $this->xmlString($doc, $e, 'URL', $entry['url']);
        $this->xmlString($doc, $e, 'Notes', $entry['notes']);

        if ($entry['otp'] !== '') {
            $this->xmlString($doc, $e, 'otp', $entry['otp'], protected: true);
        }

        foreach ($entry['custom'] as $field) {
            $this->xmlString($doc, $e, $field['label'], $field['value'], protected: $field['secret']);
        }

        return $e;
    }

    private function xmlString(\DOMDocument $doc, \DOMElement $entry, string $key, string $value, bool $protected = false): void
    {
        $s = $entry->appendChild($doc->createElement('String'));
        $s->appendChild($doc->createElement('Key'))->appendChild($doc->createTextNode($key));

        $v = $s->appendChild($doc->createElement('Value'));
        if ($protected) {
            $v->setAttribute('Protected', 'True');
        }
        $v->appendChild($doc->createTextNode($value));
    }

    /**
     * Build a real .kdbx encrypted with $password. Requires a Python
     * interpreter with pykeepass, configured via VAULT_KDBX_PYTHON.
     */
    public function toKdbx(string $password): string
    {
        $python = config('vault.kdbx_python');

        if (! $python || ! is_file($python)) {
            throw new RuntimeException('KDBX export is not configured on this server. See docs/vault.md.');
        }

        $script = base_path('scripts/vault_to_kdbx.py');
        $out = tempnam(sys_get_temp_dir(), 'vault_').'.kdbx';

        $payload = json_encode([
            'name' => self::DB_NAME,
            'groups' => $this->tree(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $process = new Process([$python, $script, $out, $password]);
        $process->setInput($payload);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($out)) {
            @unlink($out);
            Log::warning('Vault KDBX export failed', [
                'exit' => $process->getExitCode(),
                'stderr' => trim($process->getErrorOutput()),
            ]);
            throw new RuntimeException('The .kdbx file could not be built. Try the KeePass 2 XML export instead.');
        }

        return $out;
    }
}
