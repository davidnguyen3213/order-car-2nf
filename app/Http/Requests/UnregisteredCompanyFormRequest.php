<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnregisteredCompanyFormRequest extends FormRequest
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
        $currentUnregisteredCompanyId = $this->request->get('unregistered_company_store_id');

        if ($currentUnregisteredCompanyId != null) {
            $phoneRule = ['required', 'min:12', 'max:13', 'regex:/^(\d{2,3}-\d{4}-\d{4})|(\d{4}-\d{2}-\d{4})$/', 'unique:unregistered_companies,phone,' . $currentUnregisteredCompanyId];
        } else {
            $phoneRule = ['required', 'min:12', 'max:13', 'regex:/^(\d{2,3}-\d{4}-\d{4})|(\d{4}-\d{2}-\d{4})$/', 'unique:unregistered_companies,phone'];
        }

        return [
            'unregistered_company_store_display_order' => ['nullable', 'numeric'],
            'unregistered_company_store_name' => ['required', 'max:50', 'regex:/^[\pL\s\d]+$/u'],
            'unregistered_company_store_phone' => $phoneRule,
            'unregistered_company_store_address' => ['nullable'],
            'unregistered_company_store_corresponding_area' => ['nullable'],
            'unregistered_company_store_base_price' => ['nullable'],
            'unregistered_company_store_company_pr' => ['nullable'],
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
            'unregistered_company_store_display_order.numeric' => '表示順は番号のみを入力してください。',
            'unregistered_company_store_name.required' => '会社名に値を入力してください。',
            'unregistered_company_store_name.regex' => '会社名は半角英数字のみを入力してください。',
            'unregistered_company_store_name.max' => '会社名は1文字以上50文字以下で入力してください。',
            'unregistered_company_store_phone.required' => '電話番号に値を入力してください。',
            'unregistered_company_store_phone.regex' => '電話番号はハイフン付き12桁または13桁で入力して下さい。',
            'unregistered_company_store_phone.min' => '電話番号はハイフン付き12桁または13桁で入力して下さい。',
            'unregistered_company_store_phone.max' => '電話番号はハイフン付き12桁または13桁で入力して下さい。',
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
            'unregistered_company_store_created_at' => '登録日',
            'unregistered_company_store_display_order' => '表示順',
            'unregistered_company_store_name' => '会社名',
            'unregistered_company_store_phone' => '電話番号',
            'unregistered_company_store_address' => '所在地',
            'unregistered_company_store_corresponding_area' => '対応エリア',
            'unregistered_company_store_base_price' => '基本料金',
            'unregistered_company_store_company_pr' => '会社PR',
        ];
    }
}
