<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCommand extends Command
{
    protected $signature = 'test:command';

    protected $description = 'A simple test command';

    public function handle()
    {
        Log::info('Test command is running!');
        echo "Test command is running!\n";
    }
}
