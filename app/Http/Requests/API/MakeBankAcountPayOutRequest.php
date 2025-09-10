<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class MakeBankAcountPayOutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "service"=>"required|string|exists:App\Models\Client,id",
            "country"=>"required|string|exists:App\Models\CountryAvaillable,code",
            "currency"=>"required|string|exists:App\Models\WalletType,name",
            "amount"=>"required|decimal:0,5",
            "ref_id"=>"required|string",
            "account_number"=>"required|string",
            "account_name"=>"required|string",
            "bank_code"=>"required|string|exists:App\Models\StartButtonBank,code"
        ];
    }
}
