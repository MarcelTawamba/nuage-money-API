<?php

namespace App\Models\VirtualModels;

use stdClass;

class PostResponse
{
    public bool $is_success;

    public stdClass $result;

    public int $code;

    public string $errorResponsePhrase;

}
