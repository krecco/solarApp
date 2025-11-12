<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Create a notification for a user.
     */
    public function create(
        User $user,
        string $type,
        string $category,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'category' => $category,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
        ]);
    }

    /**
     * Create notifications for multiple users.
     */
    public function createForUsers(
        Collection|array $users,
        string $type,
        string $category,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null,
        ?string $actionLabel = null
    ): int {
        $count = 0;

        foreach ($users as $user) {
            $this->create($user, $type, $category, $title, $message, $data, $actionUrl, $actionLabel);
            $count++;
        }

        return $count;
    }

    /**
     * Create a system notification for a user.
     */
    public function system(User $user, string $title, string $message, ?string $actionUrl = null): Notification
    {
        return $this->create($user, 'info', 'system', $title, $message, null, $actionUrl, 'View Details');
    }

    /**
     * Create a subscription notification for a user.
     */
    public function subscription(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null
    ): Notification {
        return $this->create($user, $type, 'subscription', $title, $message, null, $actionUrl, 'View Subscription');
    }

    /**
     * Create a tenant notification for a user.
     */
    public function tenant(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null
    ): Notification {
        return $this->create($user, $type, 'tenant', $title, $message, null, $actionUrl, 'View Tenant');
    }

    /**
     * Create an error notification for a user.
     */
    public function error(User $user, string $title, string $message, ?array $data = null): Notification
    {
        return $this->create($user, 'error', 'system', $title, $message, $data);
    }

    /**
     * Create a success notification for a user.
     */
    public function success(User $user, string $title, string $message, ?string $actionUrl = null): Notification
    {
        return $this->create($user, 'success', 'system', $title, $message, null, $actionUrl);
    }

    /**
     * Create a warning notification for a user.
     */
    public function warning(User $user, string $title, string $message, ?string $actionUrl = null): Notification
    {
        return $this->create($user, 'warning', 'system', $title, $message, null, $actionUrl);
    }

    /**
     * Notify all admins.
     */
    public function notifyAdmins(
        string $title,
        string $message,
        string $type = 'info',
        ?string $actionUrl = null
    ): int {
        $admins = User::role('admin')->get();

        return $this->createForUsers(
            $admins,
            $type,
            'system',
            $title,
            $message,
            null,
            $actionUrl,
            'View Details'
        );
    }

    /**
     * Clean up old read notifications.
     */
    public function cleanupOldNotifications(int $daysOld = 30): int
    {
        return Notification::read()
            ->where('read_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
