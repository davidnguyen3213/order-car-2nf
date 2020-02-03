<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UserFormRequest extends FormRequest
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
        $currentUserId = $this->request->get('user_store_id');

        if ($currentUserId != null) {
            $phoneRule = [
                'required',
                'size:13',
                'regex:/^\d{3}-\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) use ($currentUserId) {
                    $countUser = DB::table('users')->where('is_deleted', '=', config('constants.USER_DELETED.ACTIVE'))
                            ->where('phone', '=', $value)
                            ->where('id', '<>', $currentUserId)->count();

                    if ($countUser > 0) {
                        return $fail('入力された電話番号はすでに登録されています。');
                    }
                }
            ];
        } else {
            $phoneRule = [
                'required',
                'size:13',
                'regex:/^\d{3}-\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    $countUser = DB::table('users')->where('is_deleted', '=', config('constants.USER_DELETED.ACTIVE'))
                        ->where('phone', '=', $value)->count();

                    if ($countUser > 0) {
                        return $fail('入力された電話番号はすでに登録されています。');
                    }
                }
            ];
        }

        return [
            'user_store_name' => ['required', 'max:50', 'regex:/^[\pL\s\d]+$/u'],
            'user_store_password' => ['required', 'min:8' , 'max:16', 'regex:/^[_A-z0-9]*((-|\s)*[_A-z0-9])*$/'],
            'user_store_phone' => $phoneRule,
            'user_store_status' => ['required', 'numeric'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_store_name.required' => 'ユーザー名に値を入力してください',
            'user_store_name.max' => 'ユーザー名は1文字以上50文字以下で入力してください。',
            'user_store_name.regex' => 'ユーザー名は半角英数字のみを入力してください。',
            'user_store_password.required' => 'パスワードに値を入力してください',
            'user_store_password.min' => 'パスワードは8文字以上16文字以下で入力してください。',
            'user_store_password.max' => 'パスワードは8文字以上16文字以下で入力してください。',
            'user_store_password.regex' => 'パスワードは半角英数字のみを入力してください。',
            'user_store_phone.required' => '電話番号に値を入力してください',
            'user_store_phone.size' => '電話番号はハイフン付き13桁で入力して下さい。',
            'user_store_phone.regex' => '電話番号はハイフン付き13桁で入力して下さい。',
            'user_store_status.required' => 'レコードステータスを入力してください。',
            'user_store_status.numeric' => 'レコードステータスを正しく入力してください。',
        ];
    }
}
