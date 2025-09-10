<?php

namespace App\Http\Requests\API;

use App\Models\Currency;
use InfyOm\Generator\Request\APIRequest;

class UpdatecurrencyAPIRequest extends APIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Currency::$rules;
        $rules['name'] = $rules['name'].",".$this->route("currency");$rules['code'] = $rules['code'].",".$this->route("currency");
        return $rules;
    }
}
