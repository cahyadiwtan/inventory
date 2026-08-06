<?php

use App\Models\Quotation;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    Quotation::query()
        ->whereIn('status', [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT])
        ->where('valid_until', '<', now()->toDateString())
        ->update(['status' => Quotation::STATUS_EXPIRED]);
})->dailyAt('00:10')->name('expire-quotations');

Schedule::command('inventory:notifications')
    ->everySixHours()
    ->withoutOverlapping()
    ->name('inventory-notifications');
