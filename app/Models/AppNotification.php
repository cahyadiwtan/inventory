<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class AppNotification extends BaseModel
{
    protected $table = 'notifications';

    protected $fillable = [
        'type',
        'title',
        'message',
        'user_id',
        'subject_type',
        'subject_id',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }

    public function scopeUnread(Builder $query, ?string $userId = null): Builder
    {
        return $query->where('is_read', false)
            ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $userId));
    }
}
