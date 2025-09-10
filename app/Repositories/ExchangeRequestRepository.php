<?php

namespace App\Repositories;

use App\Models\ExchangeRequest;
use App\Repositories\BaseRepository;

class ExchangeRequestRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'from_currency',
        'to_currency',
        'amount',
        'market_rate',
        'rate',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ExchangeRequest::class;
    }
}
