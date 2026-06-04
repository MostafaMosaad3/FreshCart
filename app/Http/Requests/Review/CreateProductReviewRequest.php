<?php

namespace App\Http\Requests\Review;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateProductReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $product = $this->route('product');
        $user = $this->user();

        $alreadyExist = $user->reviews()
            ->where('reviewable_type', 'product')
            ->where('reviewable_id', $product->id)
            ->exists();

        if ($alreadyExist) {
            return false;
        }

        return $user->orders()
            ->whereIn('status', ['paid', 'shipped', 'delivered'])
            ->whereHas('items.variant', fn ($q) => $q->where('product_id', $product->id))
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
