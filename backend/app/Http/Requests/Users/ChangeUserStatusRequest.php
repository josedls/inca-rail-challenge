<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

final class ChangeUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
