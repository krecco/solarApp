<?php

namespace App\Modules\CarRentals\Notifications;

use App\Modules\CarRentals\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentalStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Rental $rental,
        public string $template
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $this->rental->document_language ?? app()->getLocale();

        return (new MailMessage)
            ->subject(trans("car_rentals.notifications.{$this->template}.subject", [], $locale))
            ->greeting(trans("car_rentals.notifications.{$this->template}.greeting", ['name' => $notifiable->name], $locale))
            ->line(trans("car_rentals.notifications.{$this->template}.message", [
                'rental_number' => $this->rental->rental_number,
                'vehicle' => $this->rental->vehicle->getFullName(),
            ], $locale))
            ->action(
                trans('car_rentals.notifications.view_rental', [], $locale),
                url("/rentals/{$this->rental->id}")
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'rental_id' => $this->rental->id,
            'rental_number' => $this->rental->rental_number,
            'template' => $this->template,
            'status' => $this->rental->status->value,
        ];
    }
}
