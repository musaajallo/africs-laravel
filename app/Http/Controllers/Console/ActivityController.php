<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Support\ActivityPresenter;
use App\Support\Rbac;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can(Rbac::PERM_ACTIVITY_VIEW), 403);

        $activities = Activity::query()
            ->with(['causer:id,name', 'subject'])
            ->latest()
            ->paginate(30)
            ->through(fn (Activity $activity) => ActivityPresenter::present($activity));

        return Inertia::render('Console/Activity/Index', [
            'activities' => $activities,
        ]);
    }
}
