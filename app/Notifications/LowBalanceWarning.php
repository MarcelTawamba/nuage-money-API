<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public string $provider;
    public string $currency;
    public float $balance;
    public float $threshold;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $provider, string $currency, float $balance, float $threshold)
    {
        $this->provider = $provider;
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
                    ->subject('Low Balance Warning: ' . $this->provider . ' - ' . $this->currency)
                    ->line('The balance for ' . $this->currency . ' on ' . $this->provider . ' is below the threshold.')
                    ->line('Provider: ' . $this->provider)
                    ->line('Currency: ' . $this->currency)
                    ->line('Current Balance: ' . number_format($this->balance, 2))
                    ->line('Threshold: ' . number_format($this->threshold, 2))
                    ->line('Shortfall: ' . number_format($this->threshold - $this->balance, 2))
                    ->action('Check Dashboard', url('/admin/balances'))
                    ->line('Please take action to top up the balance as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'provider' => $this->provider,
            'currency' => $this->currency,
            'balance' => $this->balance,
            'threshold' => $this->threshold,
            'shortfall' => $this->threshold - $this->balance,
        ];
    }
}
