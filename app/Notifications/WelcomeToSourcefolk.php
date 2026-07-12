<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LogicException;

class WelcomeToSourcefolk extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

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
    public function toMail(object $notifiable): MailMessage
    {
        if (! $notifiable instanceof User) {
            throw new LogicException('The welcome notification requires a user.');
        }

        return (new MailMessage)
            ->subject('Welcome to Sourcefolk')
            ->greeting("Welcome, {$notifiable->name}!")
            ->line('Sourcefolk is an independent place to follow the people, packages, projects, and ideas moving the Laravel ecosystem forward.')
            ->line('Choose your public username, review the community terms, and make your profile yours.')
            ->action('Complete your Sourcefolk profile', route('legal.acceptance.show'))
            ->line('You received this transactional email because a Sourcefolk account was created with this address.');
    }
}
