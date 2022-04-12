<?php

namespace App\Http\Requests\MobileInService;

use App\Models\Samu\MobileInService;
use App\Models\Samu\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MobileInServiceUpdateRequest extends FormRequest
{
    public function __construct()
    {
        $this->shift = Shift::whereStatus(true)->first();
    }
    
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
            'position'      => [
                'required',
                Rule::unique('samu_mobiles_in_service', 'position')
                    ->where('shift_id',$this->shift->id)
                    ->ignore($this->route('mobileInService')->id)
            ],
            'mobile_id'     => 'required|exists:samu_mobiles,id',
            'type_id'       => 'required|exists:samu_mobile_types,id',
            'o2'            => 'nullable|string|min:0|max:255',
            'observation'   => 'nullable|string|min:0|max:5000',
            'status'        => 'nullable|string'
        ];
    }
}
