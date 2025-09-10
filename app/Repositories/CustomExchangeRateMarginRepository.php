<?php

namespace App\Repositories;

use App\Models\CustomExchangeRateMargin;
use App\Repositories\BaseRepository;

class CustomExchangeRateMarginRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'exchange_margin_id',
        'margin'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CustomExchangeRateMargin::class;
    }
}
