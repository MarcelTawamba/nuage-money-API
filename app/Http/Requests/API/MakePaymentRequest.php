<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class MakePaymentRequest extends FormRequest
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
//            "card_number"=>"required_without:msidn",
//            "card_cvc"=>"numeric|max:3|required_with:card_number",
//            "card_expire"=>"string|required_with:card_number",
//            "name"=>"required_with:card_number|string",
//            "address"=>"required_with:card_number|string",
//            "email"=>"required_with:card_number:email"
        ];
    }
}

//'status' => [Rule::enum(ServerStatus::class)],
