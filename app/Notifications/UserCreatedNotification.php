<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\MailBrand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $createdBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var User $notifiable */
        $brand = MailBrand::details();
        $expiryMinutes = (int) config('auth.passwords.users.expire', 60);
        $resetUrl = route('admin.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
        $data = $brand + [
            'preheader' => 'Your Cholavin ERP account is ready.',
            'user' => $notifiable,
            'createdBy' => $this->createdBy,
            'resetUrl' => $resetUrl,
            'loginUrl' => route('admin.auth.index'),
            'expiryMinutes' => $expiryMinutes,
            'roleName' => $notifiable->role?->name ?? 'Assigned user',
            'shops' => $notifiable->shops->pluck('name')->join(', '),
            'godowns' => $notifiable->godowns->pluck('name')->join(', '),
            'financialYears' => $notifiable->financialYears->pluck('name')->join(', '),
        ];

        return (new MailMessage)
            ->subject('Your '.$brand['companyName'].' ERP account is ready')
            ->view('emails.user-created', $data)
            ->text('emails.user-created-text', $data);
    }
}
