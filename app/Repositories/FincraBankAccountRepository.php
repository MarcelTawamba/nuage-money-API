<?php

namespace App\Repositories;

use App\Models\FincraBankAccount;
use App\Repositories\BaseRepository;

class FincraBankAccountRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'account_number',
        'account_name',
        'bank_code'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return FincraBankAccount::class;
    }
}
