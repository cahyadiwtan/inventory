<?php

namespace App\Models\Concerns;

use App\Services\ActivityLogService;

trait HasActivityLog
{
    /**
     * Record model events into activity log (audit trail).
     */
    public static function bootHasActivityLog(): void
    {
        $events = [
            'created' => 'create',
            'updated' => 'update',
            'deleted' => 'delete',
            'restored' => 'restore',
        ];

        foreach ($events as $eloquentEvent => $logEvent) {
            if (method_exists(static::class, $eloquentEvent)) {
                static::$eloquentEvent(function ($model) use ($logEvent) {
                    app(ActivityLogService::class)->log($logEvent, $model);
                });
            }
        }
    }
}
