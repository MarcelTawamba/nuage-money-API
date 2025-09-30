<?php

namespace App\Listeners;

use App\Events\PayOutSuccessEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PayOutSuccessHandler implements ShouldQueue
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
    public function handle(PayOutSuccessEvent $event): void
    {
        Log::info('PayOutSuccessEvent handled for Achat ID: ' . $event->achat->id);
    }
}
