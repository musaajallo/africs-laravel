<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

final class ActivityPresenter
{
    private const FIELD_LABELS = [
        'name' => 'Name',
        'type' => 'Type',
        'category' => 'Category',
        'status' => 'Status',
        'email' => 'Email',
        'phone' => 'Phone',
        'website' => 'Website',
        'tax_number' => 'Tax number',
        'currency' => 'Currency',
        'billing_address' => 'Billing address',
        'city' => 'City',
        'country' => 'Country',
        'owner_id' => 'Account manager',
        'notes' => 'Notes',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function present(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'event' => $activity->event,
            'causer' => $activity->causer?->name ?? 'System',
            'at' => $activity->created_at->toIso8601String(),
            'at_human' => $activity->created_at->diffForHumans(),
            'subject_label' => self::subjectLabel($activity),
            'subject_url' => self::subjectUrl($activity),
            'changes' => self::changes($activity),
        ];
    }

    private static function subjectLabel(Activity $activity): ?string
    {
        $subject = $activity->subject;

        return match (true) {
            $subject instanceof Client => $subject->name,
            $subject instanceof Project => $subject->name,
            $subject instanceof Lead => $subject->company ?: $subject->name,
            $subject === null => null,
            default => class_basename($subject).' #'.$subject->getKey(),
        };
    }

    private static function subjectUrl(Activity $activity): ?string
    {
        $subject = $activity->subject;

        return match (true) {
            $subject instanceof Client => route('console.clients.show', $subject->id),
            $subject instanceof Project => route('console.projects.show', $subject->id),
            $subject instanceof Lead => route('console.leads.show', $subject->id),
            default => null,
        };
    }

    /**
     * Field-level diffs for an "updated" event.
     *
     * @return list<array{field: string, from: string, to: string}>
     */
    private static function changes(Activity $activity): array
    {
        if ($activity->event !== 'updated') {
            return [];
        }

        $changes = $activity->attribute_changes ?? collect();
        $new = $changes->get('attributes', []);
        $old = $changes->get('old', []);

        return collect($new)
            ->keys()
            ->map(fn ($field) => [
                'field' => self::FIELD_LABELS[$field] ?? ucfirst(str_replace('_', ' ', $field)),
                'from' => $field === 'owner_id' ? '—' : self::stringify($old[$field] ?? null),
                'to' => $field === 'owner_id' ? '—' : self::stringify($new[$field] ?? null),
            ])
            ->values()
            ->all();
    }

    private static function stringify(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'empty';
        }

        return Str::limit((string) $value, 60);
    }
}
