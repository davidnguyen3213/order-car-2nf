<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\UsesCustomErrorMessage;
use Illuminate\Foundation\Http\FormRequest;


class RegisterUserRequest extends FormRequest
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
        return [
            'name' => 'required|min:1|max:50|alpha_spaces',
            'phone' => 'required|user_phone',
            'password' => 'required|alpha_dash|without_spaces|min:8|max:16'
        ];
    }
}
