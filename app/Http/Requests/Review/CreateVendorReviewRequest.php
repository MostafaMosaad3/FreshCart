<?php

namespace App\Http\Requests\Review;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateVendorReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Unlike product reviews, vendor reviews require no purchase history —
     * any authenticated user may review a vendor, but only once.
     */
    public function authorize(): bool
    {
        $vendor = $this->route('vendor');
        $user = $this->user();

        $alreadyExist = $user->reviews()
            ->where('reviewable_type', 'vendor')
            ->where('reviewable_id', $vendor->id)
            ->exists();

        return ! $alreadyExist;
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
