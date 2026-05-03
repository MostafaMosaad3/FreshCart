<?php

namespace App\Http\Requests\Auth;

use App\Rules\EgyptianPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->email)),
            'name'  => trim((string) $this->name),
            'phone' => $this->phone ? trim((string) $this->phone) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', Rule::unique('users', 'email')],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role'       => ['sometimes', Rule::in(['customer', 'vendor'])],
            'phone'      => ['nullable', 'string', new EgyptianPhone()],
            'store_name' => ['required_if:role,vendor', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
        ];
    }
}
