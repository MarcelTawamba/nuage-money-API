<?php

namespace Tests\Unit;

use App\Services\StartButton\AfricaService;
use Tests\TestCase;

class StartButtonAfricaServiceTest extends TestCase
{
    /**
     * @return void
     */
    public function test_get_list_of_banks()
    {
        $service = new AfricaService();
        $response = $service->getListOfBanks();
        $this->assertTrue($response['success'], "Please check your STARTBUTTON_SECRET_KEY in your .env and phpunit.xml files.");
        $this->assertNotEmpty($response['data']);
    }
}
