<?php

namespace App\Repositories;

use App\Models\CountryAvaillable;
use App\Repositories\BaseRepository;

class CountryAvaillableRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'code'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return CountryAvaillable::class;
    }
}
