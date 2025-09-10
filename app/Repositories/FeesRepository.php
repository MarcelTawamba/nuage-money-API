<?php

namespace App\Repositories;

use App\Models\Operator;
use App\Repositories\BaseRepository;

class FeesRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'country_id',
        'currency_id',
        'method',
        'fees'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Operator::class;
    }
}
