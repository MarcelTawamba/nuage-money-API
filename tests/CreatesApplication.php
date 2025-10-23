<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        if (file_exists(dirname(__DIR__) . '/.env.testing')) {
            \Dotenv\Dotenv::createImmutable(dirname(__DIR__), '.env.testing')->load();
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
