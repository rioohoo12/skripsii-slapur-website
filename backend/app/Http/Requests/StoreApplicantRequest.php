<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // allow all for now (public registration)
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'usia' => ['required', 'integer', 'min:11', 'max:21'], // Aturan usia, e.g., SMP-SMA range
            'email' => ['nullable', 'email', 'max:100', 'unique:applicants,email'],
            'no_hp' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'usia.min' => 'Usia minimal adalah 11 tahun.',
            'usia.max' => 'Usia maksimal adalah 21 tahun.',
            'no_hp.regex' => 'Format nomor telepon tidak valid.',
        ];
    }
}
