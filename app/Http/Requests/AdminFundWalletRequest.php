<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminFundWalletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            "country"=>"required|string|exists:App\Models\CountryAvaillableillables,code",
            "currency"=>"required|string|exists:App\Models\WalletType,name",
            "amount"=>"required|decimal:0,5",
            "reference"=>"required|string",
            "description"=>"required|string"
        ];
    }
}
