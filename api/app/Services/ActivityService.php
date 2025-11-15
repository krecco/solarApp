<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityService
{
    /**
     * Create an activity log entry.
     *
     * @param string $title The title of the activity
     * @param string $content The content/description of the activity
     * @param string|null $userId The user this activity is for
     * @param string|null $contentId The ID of the related content (e.g., investment ID)
     * @param string|null $contentType The type of content (e.g., 'investment', 'file', 'plant')
     * @param string|null $notificationType The type of notification (e.g., 'info', 'success', 'warning', 'danger')
     * @param bool $showOnUserDashboard Whether to show this on the user's dashboard
     * @param array $additionalData Any additional data to merge
     * @return Activity
     */
    public function createActivity(
        string $title,
        string $content,
        ?string $userId = null,
        ?string $contentId = null,
        ?string $contentType = null,
        ?string $notificationType = 'info',
        bool $showOnUserDashboard = true,
        array $additionalData = []
    ): Activity {
        $currentUser = Auth::user();

        $activityData = array_merge([
            'title' => $title,
            'content' => $content,
            'user_id' => $userId,
            'content_id' => $contentId,
            'content_type' => $contentType,
            'notification_type' => $notificationType,
            'show_on_user_dashboard' => $showOnUserDashboard,
            'created_by' => $currentUser ? $currentUser->name : 'System',
            'created_by_id' => $currentUser?->id,
            'rs' => 0,
        ], $additionalData);

        return Activity::create($activityData);
    }

    /**
     * Log investment creation activity.
     */
    public function logInvestmentCreated($investment, User $user): Activity
    {
        return $this->createActivity(
            title: 'Investment Created',
            content: "New investment of $" . number_format($investment->amount, 2) . " created",
            userId: $investment->user_id,
            contentId: $investment->id,
            contentType: 'investment',
            notificationType: 'success'
        );
    }

    /**
     * Log investment update activity.
     */
    public function logInvestmentUpdated($investment, array $oldValues, User $user): Activity
    {
        $changes = [];
        foreach ($oldValues as $key => $oldValue) {
            if (isset($investment->{$key}) && $investment->{$key} != $oldValue) {
                $changes[] = ucfirst(str_replace('_', ' ', $key)) . " changed from {$oldValue} to {$investment->{$key}}";
            }
        }

        $content = empty($changes)
            ? "Investment updated"
            : "Investment updated: " . implode(', ', $changes);

        return $this->createActivity(
            title: 'Investment Updated',
            content: $content,
            userId: $investment->user_id,
            contentId: $investment->id,
            contentType: 'investment',
            notificationType: 'info'
        );
    }

    /**
     * Log investment deletion activity.
     */
    public function logInvestmentDeleted($investment, User $user): Activity
    {
        return $this->createActivity(
            title: 'Investment Deleted',
            content: "Investment of $" . number_format($investment->amount, 2) . " deleted",
            userId: $investment->user_id,
            contentId: $investment->id,
            contentType: 'investment',
            notificationType: 'danger'
        );
    }

    /**
     * Log investment verification activity.
     */
    public function logInvestmentVerified($investment, User $user): Activity
    {
        return $this->createActivity(
            title: 'Investment Verified',
            content: "Investment verified and activated",
            userId: $investment->user_id,
            contentId: $investment->id,
            contentType: 'investment',
            notificationType: 'success'
        );
    }

    /**
     * Log repayment schedule generation activity.
     */
    public function logRepaymentScheduleGenerated($investment, int $repaymentsCount, User $user): Activity
    {
        return $this->createActivity(
            title: 'Repayment Schedule Generated',
            content: "Repayment schedule generated with {$repaymentsCount} payments",
            userId: $investment->user_id,
            contentId: $investment->id,
            contentType: 'investment',
            notificationType: 'info'
        );
    }

    /**
     * Log activity with error.
     */
    public function logError(string $title, string $errorMessage, ?string $userId = null, ?string $contentId = null): Activity
    {
        return $this->createActivity(
            title: $title,
            content: "Error: {$errorMessage}",
            userId: $userId,
            contentId: $contentId,
            contentType: 'error',
            notificationType: 'danger'
        );
    }

    /**
     * Generic log method to replace activity() fluent interface.
     *
     * @param string $message The log message/title
     * @param mixed $performedOn The model this activity is performed on
     * @param mixed $causedBy The user who caused this activity
     * @param array $properties Additional properties to log
     * @param string|null $notificationType Type of notification (info, success, warning, danger)
     * @return Activity
     */
    public function log(
        string $message,
        $performedOn = null,
        $causedBy = null,
        array $properties = [],
        ?string $notificationType = 'info'
    ): Activity {
        $userId = null;
        $contentId = null;
        $contentType = null;

        // Extract information from performedOn model
        if ($performedOn) {
            $contentId = $performedOn->id ?? null;
            $contentType = $this->getModelType($performedOn);

            // Try to get user_id from the model
            if (isset($performedOn->user_id)) {
                $userId = $performedOn->user_id;
            }
        }

        // Get the user who caused this
        $currentUser = $causedBy ?? Auth::user();

        // Create the activity
        return Activity::create([
            'title' => $message,
            'content' => $message,
            'user_id' => $userId,
            'content_id' => $contentId,
            'content_type' => $contentType,
            'notification_type' => $notificationType,
            'show_on_user_dashboard' => true,
            'created_by' => $currentUser ? $currentUser->name : 'System',
            'created_by_id' => $currentUser?->id,
            'rs' => 0,
        ]);
    }

    /**
     * Get the model type from a model instance.
     */
    private function getModelType($model): string
    {
        if (!$model) {
            return 'unknown';
        }

        $className = get_class($model);
        $shortName = class_basename($className);

        // Convert to lowercase and remove "Model" suffix if present
        $type = strtolower(str_replace('Model', '', $shortName));

        return $type;
    }
}
