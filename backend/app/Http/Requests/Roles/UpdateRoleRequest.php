<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $role = $this->route('role');

        $this->merge([
            'role_id' => $role?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('guard_name', config('auth.defaults.guard'))
                    ->ignore($this->integer('role_id')),
            ],
            'permissions' => ['required', 'array'],
            'permissions.*' => [
                'string',
                'distinct',
                Rule::exists('permissions', 'name')->where('guard_name', config('auth.defaults.guard')),
            ],
        ];
    }
}
