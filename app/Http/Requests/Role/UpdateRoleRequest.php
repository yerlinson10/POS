<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles:edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($roleId)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con este nombre.',
            'permissions.*.exists' => 'Uno o más permisos seleccionados no son válidos.',
        ];
    }
}
