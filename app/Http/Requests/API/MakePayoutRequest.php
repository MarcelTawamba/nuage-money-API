<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class MakePayoutRequest extends FormRequest
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
            "currency"=>"required|string|exists:App\Models\WalletType,name",
            "country"=>"required|string|exists:App\Models\CountryAvaillable,code",
            "amount"=>"required|decimal:0,5",
            "ref_id"=>"required|string",
            "msidn"=>"required|string",
        ];
    }
}
