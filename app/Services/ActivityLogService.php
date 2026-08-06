<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Persist a single activity entry (audit trail).
     */
    public function log(
        string $event,
        ?Model $subject = null,
        string $description = '',
        array $properties = [],
        ?int $causerId = null,
    ): ActivityLog {
        $causerId ??= auth()->id();

        $properties['attributes'] = $subject?->getAttributes();

        return ActivityLog::create([
            'log_name' => $subject ? class_basename($subject) : 'system',
            'description' => $description ?: $event,
            'event' => $event,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causerId ? app(config('auth.providers.users.model'))->getMorphClass() : null,
            'causer_id' => $causerId,
            'properties' => $properties,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
