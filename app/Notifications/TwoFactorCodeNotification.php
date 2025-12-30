<?php

namespace App\Notifications;

use App\Mail\TwoFactorCodeMail;
use App\Models\TwoFactorCode;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TwoFactorCode $twoFactorCode
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new TwoFactorCodeMail($this->twoFactorCode))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'code_id' => $this->twoFactorCode->id,
            'expires_at' => $this->twoFactorCode->expires_at,
        ];
    }
}
