<?php

namespace App\Repositories;

use App\Models\ExchangeRateMargin;
use App\Repositories\BaseRepository;

class ExchangeRateMarginRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'from_currency',
        'to_currency',
        'margin'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ExchangeRateMargin::class;
    }
}
