<?php

namespace App\Repositories;

use App\Models\FincraBank;
use App\Repositories\BaseRepository;

class FincraBankRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'code',
        'currency'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return FincraBank::class;
    }
}
