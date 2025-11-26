<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $resetUrl = url("/reset-password/{$this->token}?username={$notifiable->username}");

        return (new MailMessage)
                    ->subject('Reset Your Password')
                    ->greeting('Hello '.$notifiable->username)
                    ->line('You requested a password reset. Click the button below to reset your password.')
                    ->action('Reset Password', $resetUrl)
                    ->line('If you did not request a password reset, no further action is required.');
    }
}
