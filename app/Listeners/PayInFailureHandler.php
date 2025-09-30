<?php

namespace App\Listeners;

use App\Events\PayInFailureEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PayInFailureHandler implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PayInFailureEvent $event): void
    {
        Log::info('PayInFailureEvent handled for Achat ID: ' . $event->achat->id);
    }
}
