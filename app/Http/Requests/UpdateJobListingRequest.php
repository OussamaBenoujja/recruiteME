<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Change from false to true
    }
    
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'company' => 'sometimes|required|string|max:255',
            'employment_type' => 'sometimes|required|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gt:salary_min',
            'closing_date' => 'nullable|date|after:today',
            'is_active' => 'sometimes|boolean'
        ];
    }
}
