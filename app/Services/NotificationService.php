<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class NotificationService
{
    /**
     * Store an in-app notification. Null $user = broadcast to everyone.
     */
    public function notify(
        string $type,
        string $title,
        string $message,
        ?User $user = null,
        ?Model $subject = null,
    ): AppNotification {
        return AppNotification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'user_id' => $user?->id,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'is_read' => false,
        ]);
    }

    public function unreadCount(?string $userId = null): int
    {
        return AppNotification::unread($userId)->count();
    }
}
