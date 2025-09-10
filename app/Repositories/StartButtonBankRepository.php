<?php

namespace App\Repositories;

use App\Models\StartButtonBank;
use App\Repositories\BaseRepository;

class StartButtonBankRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'startbutton_id',
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
        return StartButtonBank::class;
    }
}
