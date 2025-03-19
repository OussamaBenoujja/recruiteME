<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Change from false to true
    }

    public function rules(): array
    {
        return [
            'job_listing_id' => 'required|exists:job_listings,id',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'cover_letter' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'notes' => 'nullable|string'
        ];
    }
}