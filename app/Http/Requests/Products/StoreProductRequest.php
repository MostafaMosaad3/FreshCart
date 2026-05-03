<?php

namespace App\Http\Requests\Products;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'        => trim((string) $this->name),
            'description' => trim((string) ($this->description ?? '')),
            'price'       => $this->price !== null ? (float) $this->price : null,
            'status'      => $this->status ?? 'draft',
            'slug'        => $this->slug ?? Str::slug($this->name) . '-' . Str::random(6),
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
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', Rule::unique('products', 'slug')],
            'description'   => ['nullable', 'string'],
            'price'         => ['required', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'gt:price'],
            'status'        => ['sometimes', Rule::in(['active', 'draft', 'inactive'])],
            'category_ids'  => ['required', 'array', 'min:1'],
            'category_ids.*'=> ['integer', Rule::exists('categories', 'id')],
        ];
    }
}
