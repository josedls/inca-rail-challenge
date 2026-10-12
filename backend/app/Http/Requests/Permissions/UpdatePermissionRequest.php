<?php

namespace App\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $permission = $this->route('permission');

        $this->merge([
            'permission_id' => $permission?->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where('guard_name', config('auth.defaults.guard'))
                    ->ignore($this->integer('permission_id')),
            ],
        ];
    }
}
