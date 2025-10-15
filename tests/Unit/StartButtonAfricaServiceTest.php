<?php

namespace Tests\Unit;

use App\Services\StartButtonAfricaService;
use Tests\TestCase;

namespace Tests\Unit;

use App\Services\StartButtonAfricaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StartButtonAfricaServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function test_get_list_of_banks()
    {
        $service = new StartButtonAfricaService();
        $response = $service->getListOfBanks();
        dd($response);
        $this->assertTrue($response['success'], "Please check your STARTBUTTON_SECRET_KEY in your .env and phpunit.xml files.");
        $this->assertNotEmpty($response['data']);
    }
}
