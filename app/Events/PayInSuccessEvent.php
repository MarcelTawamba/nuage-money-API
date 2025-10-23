<?php

namespace App\Events;


use App\Models\Achat;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayInSuccessEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  Achat $achat;

    /**
     * Create a new event instance.
     */
    public function __construct(Achat $achat)
    {
        //
        $this->achat = $achat;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
