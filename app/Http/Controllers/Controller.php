<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Shortcut to persist an activity log entry.
     */
    protected function activity(string $event, $subject = null, string $description = '', array $properties = []): void
    {
        app(ActivityLogService::class)->log($event, $subject, $description, $properties);
    }
}
