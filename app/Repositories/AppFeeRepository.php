<?php

namespace App\Repositories;

use App\Models\CustomFee;
use App\Repositories\BaseRepository;

class AppFeeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'company_id',
        'method_id',
        'fee_type',
        'fee'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CustomFee::class;
    }
}
