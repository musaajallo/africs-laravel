<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ApiTokenRequest;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(Rbac::PERM_API_TOKENS_MANAGE), 403);

        $tokens = $request->user()->tokens()
            ->latest()
            ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at'])
            ->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used' => $token->last_used_at?->diffForHumans(),
                'created' => $token->created_at->toDateString(),
            ]);

        return Inertia::render('Console/ApiTokens/Index', [
            'tokens' => $tokens,
            'availableAbilities' => Rbac::apiAbilities(),
        ]);
    }

    public function store(ApiTokenRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_API_TOKENS_MANAGE), 403);

        $token = $request->user()->createToken($request->string('name')->trim(), $request->abilities());

        return redirect()
            ->route('console.api-tokens.index')
            ->with('success', 'Token created — copy it now, it will not be shown again.')
            ->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, int $token): RedirectResponse
    {
        abort_unless($request->user()->can(Rbac::PERM_API_TOKENS_MANAGE), 403);

        $request->user()->tokens()->whereKey($token)->delete();

        return redirect()
            ->route('console.api-tokens.index')
            ->with('success', 'Token revoked.');
    }
}
