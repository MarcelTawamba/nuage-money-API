<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public string $currency;
    public float $balance;
    public float $threshold;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $currency, float $balance, float $threshold)
    {
        $this->currency = $currency;
        $this->balance = $balance;
        $this->threshold = $threshold;
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
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Low Balance Warning: ' . $this->currency)
                    ->line('The balance for ' . $this->currency . ' is below the threshold.')
                    ->line('Current Balance: ' . $this->balance)
                    ->line('Threshold: ' . $this->threshold)
                    ->line('Please take action to top up the balance.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'currency' => $this->currency,
            'balance' => $this->balance,
            'threshold' => $this->threshold,
        ];
    }
}
