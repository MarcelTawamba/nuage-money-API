<?php

namespace App\Repositories;

use App\Models\SystemLedger;
use App\Repositories\BaseRepository;

class SystemLegerRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return SystemLedger::class;
    }
}
