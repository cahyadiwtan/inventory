<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait UsesUuid
{
    /**
     * Use UUID string as primary key.
     */
    public static function bootUsesUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /**
     * Disable auto-increment as keys are generated manually.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Primary key is a string (UUID), not an integer.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
