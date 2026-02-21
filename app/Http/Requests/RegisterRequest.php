<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            // 未入力
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // パスワード8文字未満
            'password.min' => 'パスワードは8文字以上で入力してください',

            // 確認用不一致
            'password.confirmed' => 'パスワードと一致しません',

            // メール重複時の日本語エラーメッセージ
        'email.unique' => 'このメールアドレスは既に登録されています',
        ];
    }
}





