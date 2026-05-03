<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->email))
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }


    public function attemptLogin() : array
    {
        $user = User::where('email' , $this->validated('email'))->first();

        if(!$user || ! Hash::check($this->validated('password'), $user->password)){
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        $ability = match ($user->role) {
            'admin'    => '*',
            'vendor'   => 'vendor',
            'customer' => 'customer',
        };

        return [
            'user'  => $user,
            'token' => $user->createToken('auth-token', [$ability])->plainTextToken,
        ];
    }
}
