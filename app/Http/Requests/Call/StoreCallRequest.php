<?php

namespace App\Http\Requests\Call;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCallRequest extends FormRequest
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
        return [
            'applicant'     => 'nullable|string|min:0|max:255',
            'age'           => 'nullable|numeric|min:0',
            'telephone'     => 'nullable|string|size:9',
            'reason'        => 'nullable|string|min:0|max:255',
            'intervention'  => 'nullable|boolean',
            'information'   => 'required|string|min:3|max:5000',
            'commune_id'    => 'required|exists:communes,id',
            'address'       => 'nullable|string|min:0|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'sex' => [
                'nullable',
                Rule::in(['MALE', 'FEMALE', 'UNKNOWN', 'OTHER']),
            ],
        ];
    }
}
