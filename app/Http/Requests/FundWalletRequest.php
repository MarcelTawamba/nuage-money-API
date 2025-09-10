<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FundWalletRequest extends FormRequest
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

            "method"=>"required|string|exists:App\Models\Operator,id",
            "currency"=>"required|string|exists:App\Models\WalletType,name",
            "amount"=>"required|decimal:0,5",
            "email"=>"email|required_without:msidn",
            "msidn"=>"required_without:email"
        ];
    }
}
