<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates a panel behind its `{panel}.access` permission.
 *
 * Usage: ->middleware('panel:console') or ->middleware('panel:cms')
 */
class EnsurePanelAccess
{
    public function handle(Request $request, Closure $next, string $panel): Response
    {
        $user = $request->user();

        abort_if($user === null, 403);
        abort_unless($user->can($panel.'.access'), 403);

        return $next($request);
    }
}
