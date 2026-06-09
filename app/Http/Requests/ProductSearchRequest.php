<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer'],
            'vendor_id' => ['nullable', 'integer'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'rating_min' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:price_asc,price_desc,rating_desc,newest'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
