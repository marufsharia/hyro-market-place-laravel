<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated and cannot review their own plugin
        return auth()->check() && 
               $this->route('plugin')->user_id !== auth()->id();
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Please provide a rating.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'comment.max' => 'Comment cannot exceed 1000 characters.',
        ];
    }
}
