<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\StoreUserRequest;
use App\Http\Requests\Console\UpdateUserRequest;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->trim()->value();

        $users = User::query()
            ->with('roles:id,name')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"),
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => $this->present($user));

        return Inertia::render('Console/Users/Index', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Console/Users/Create', [
            'availableRoles' => $this->availableRoles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        DB::transaction(function () use ($request) {
            $user = User::create($request->userAttributes());
            // Internal accounts are created pre-verified by an administrator.
            $user->markEmailAsVerified();
            $user->syncRoles($request->roles());
        });

        return redirect()
            ->route('console.users.index')
            ->with('success', 'User created.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Console/Users/Edit', [
            'user' => [
                ...$this->present($user),
                'roles' => $user->getRoleNames(),
            ],
            'availableRoles' => $this->availableRoles(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        DB::transaction(function () use ($request, $user) {
            $user->update($request->userAttributes());
            $user->syncRoles($request->roles());
        });

        return redirect()
            ->route('console.users.index')
            ->with('success', 'User updated.');
    }

    /**
     * Deactivate (soft) or reactivate a user. Hard deletes are intentionally
     * not exposed.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Guarded here as well as in the policy: a super-admin bypasses policy
        // checks via Gate::before, so the self-deactivation lock-out guard
        // must be explicit.
        abort_if($request->user()->is($user), 403, 'You cannot deactivate your own account.');

        $this->authorize('delete', $user);

        $user->forceFill([
            'deactivated_at' => $user->isDeactivated() ? null : now(),
        ])->save();

        return redirect()
            ->route('console.users.index')
            ->with('success', $user->isDeactivated() ? 'User deactivated.' : 'User reactivated.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
            'deactivated' => $user->isDeactivated(),
            'is_self' => $user->is(request()->user()),
        ];
    }

    /**
     * Roles the current user is allowed to assign. Non super-admins cannot
     * hand out the super-admin role.
     */
    protected function availableRoles(): array
    {
        return Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->reject(fn (string $role) => $role === Rbac::ROLE_SUPER_ADMIN
                && ! request()->user()->hasRole(Rbac::ROLE_SUPER_ADMIN))
            ->values()
            ->all();
    }
}
