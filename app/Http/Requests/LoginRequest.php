<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '正しいメールアドレス形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

    public function authenticate(): void
    {
        if (! Auth::attempt($this->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません',
            ]);
        }

        $user = Auth::user();

        // 🔥 未認証チェック
        if (! $user->hasVerifiedEmail()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'メール認証が完了していません。',
            ]);
        }

        $this->session()->regenerate();
    }
}
