<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\UsesCustomErrorMessage;
use Illuminate\Foundation\Http\FormRequest;


class FCMTokenRequest extends FormRequest
{
    use UsesCustomErrorMessage;

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
        $listType = implode(',', array_values(\Config::get('constants.TYPE_NOTIFY')));
        $listPlatform = implode(',', array_values(\Config::get('constants.TYPE_PLATFORM')));
        return [
            'uc_id' => 'required',
            'device_token' => 'required',
            'platform' => 'required|in:' . $listPlatform,
            'type' => 'required|in:' . $listType
        ];
    }
}
