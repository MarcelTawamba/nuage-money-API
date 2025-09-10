<?php

namespace App\Repositories;


use App\Models\WalletType;

class CurrencyRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'decimals'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return WalletType::class;
    }
}
