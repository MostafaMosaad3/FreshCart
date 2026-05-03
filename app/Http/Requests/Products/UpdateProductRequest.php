<?php

namespace App\Http\Requests\Products;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product')) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $payload = [];
        if ($this->has('name'))        $payload['name']        = trim((string) $this->name);
        if ($this->has('description')) $payload['description'] = trim((string) $this->description);
        if ($this->has('price'))       $payload['price']       = (float) $this->price;
        $this->merge($payload);
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'name'          => ['sometimes', 'string', 'max:255'],
            'slug'          => ['sometimes', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'description'   => ['sometimes', 'string'],
            'price'         => ['sometimes', 'numeric', 'min:0'],
            'compare_price' => ['sometimes', 'nullable', 'numeric', 'gt:price'],
            'status'        => ['sometimes', Rule::in(['active', 'draft', 'inactive'])],
            'category_ids'  => ['sometimes', 'array', 'min:1'],
            'category_ids.*'=> ['integer', Rule::exists('categories', 'id')],
        ];
    }
}
