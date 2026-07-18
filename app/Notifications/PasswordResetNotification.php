<?php

namespace App\Notifications;

use App\Support\MailBrand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $brand = MailBrand::details();
        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);
        $resetUrl = route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $data = $brand + [
            'preheader' => 'Use this secure link to reset your Cholavin ERP password.',
            'user' => $notifiable,
            'resetUrl' => $resetUrl,
            'expiryMinutes' => $expiryMinutes,
        ];

        return (new MailMessage)
            ->subject('Reset your '.$brand['companyName'].' ERP password')
            ->view('emails.password-reset', $data)
            ->text('emails.password-reset-text', $data);
    }
}
