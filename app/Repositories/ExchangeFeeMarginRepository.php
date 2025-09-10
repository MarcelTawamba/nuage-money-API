<?php

namespace App\Repositories;

use App\Models\ExchangeFeeMargin;
use App\Repositories\BaseRepository;

class ExchangeFeeMarginRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'currency',
        'amount',
        'exchange_request'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return ExchangeFeeMargin::class;
    }
}
