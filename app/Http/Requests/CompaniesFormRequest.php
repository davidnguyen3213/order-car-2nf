<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class CompaniesFormRequest extends FormRequest
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
        $currentCompanyId = $this->request->get('company_store_id');
        $currentCompanyPassword = $this->request->get('company_store_password');

        if ($currentCompanyId != null) {
            $phoneRule = [
                'required',
                'min:12',
                'max:13',
                'regex:/^(\d{2,3}-\d{4}-\d{4})|(\d{4}-\d{2}-\d{4})$/',
                function ($attribute, $value, $fail) use ($currentCompanyId, $currentCompanyPassword) {
                    $countCompany = DB::table('companies')->where('is_deleted', '=', config('constants.COMPANY_DELETED.ACTIVE'))
                        ->where('phone_to_login', '=', $value)
                        ->where('raw_pass', '=', $currentCompanyPassword)
                        ->where('id', '<>', $currentCompanyId)->count();

                    if ($countCompany > 0) {
                        return $fail('入力された電話番号とパスワードの組み合わせはすでに登録されています。');
                    }
                }
            ];
        } else {
            $phoneRule = [
                'required',
                'min:12',
                'max:13',
                'regex:/^(\d{2,3}-\d{4}-\d{4})|(\d{4}-\d{2}-\d{4})$/',
                function ($attribute, $value, $fail) use ($currentCompanyPassword) {
                    $countCompany = DB::table('companies')->where('is_deleted', '=', config('constants.COMPANY_DELETED.ACTIVE'))
                        ->where('phone_to_login', '=', $value)
                        ->where('raw_pass', '=', $currentCompanyPassword)->count();

                    if ($countCompany > 0) {
                        return $fail('入力された電話番号とパスワードの組み合わせはすでに登録されています。');
                    }
                }
            ];
        }

        return [
            'company_store_status_login' => ['required', 'numeric'],
            'company_store_name' => ['required', 'max:50', 'regex:/^[\pL\s\d]+$/u'],
            'company_store_person_charged' => ['nullable'],
            'company_store_email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) {
                    $explode = explode("@",$value);
                    $countDot = substr_count($explode[0], ".");
                    if ($countDot > 3) {
                        return $fail('メールアドレスが不正です');
                    }
                }
            ],
            'company_store_password' => ['required', 'min:8' , 'max:16', 'regex:/^[_A-z0-9]*((-|\s)*[_A-z0-9])*$/', 'without_spaces'],
            'company_store_phone' => $phoneRule,
            'company_store_address' => ['nullable'],
            'company_store_corresponding_area' => ['nullable'],
            'company_store_base_price' => ['nullable'],
            'company_store_company_pr' => ['nullable'],
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
            'company_store_name.required' => '会社名に値を入力してください。',
            'company_store_name.regex' => '会社名は半角英数字のみを入力してください。',
            'company_store_name.max' => '会社名は1文字以上50文字以下で入力してください。',
            'company_store_phone.required' => '電話番号に値を入力してください',
            'company_store_phone.max' => '電話番号はハイフン付き12桁または13桁で入力して下さい。',
            'company_store_phone.min' => '電話番号はハイフン付き12桁または13桁で入力して下さい。',
            'company_store_phone.regex' => '電話番号はハイフン付き12桁または13桁で入力して下さい。',
            'company_store_email.email' => 'メールアドレスが不正です',
            'company_store_password.required' => 'パスワードに値を入力してください',
            'company_store_password.regex' => 'パスワードは半角英数字のみを入力してください。',
            'company_store_password.min' => 'パスワードは8文字以上16文字以下で入力してください。',
            'company_store_password.without_spaces' => 'パスワードはは半角英数字のみを入力してください。',
            'company_store_password.max' => 'パスワードは8文字以上16文字以下で入力してください。',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'company_store_created_at' => '登録日',
            'company_store_status_login' => 'レコードステータス',
            'company_store_name' => '会社名',
            'company_store_person_charged' => '担当者名',
            'company_store_email' => 'メールアドレス',
            'company_store_password' => 'パスワード',
            'company_store_phone' => '電話番号',
            'company_store_address' => '所在地',
            'company_store_corresponding_area' => '対応エリア',
            'company_store_base_price' => '基本料金',
            'company_store_company_pr' => '会社PR',
        ];
    }
}
