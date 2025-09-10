<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
            "name"=>"required|string",
            "country_code"=>"required|string|exists:lc_countries,iso_alpha_3",
            "email"=>[
                'required',
                "email",
                Rule::unique('users', 'email')->ignore(Auth::user()->id)
            ],
            "phone_code"=>"required|string|exists:lc_countries,international_phone",
            "phone_number"=>"required|string",

        ];
    }
}
