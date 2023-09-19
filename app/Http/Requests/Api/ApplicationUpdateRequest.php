<?php

namespace App\Http\Requests\Api;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:'.Application::Active.','.Application::Resolved],
            'comment' => ['nullable', 'required_if:status,'.Application::Resolved],
        ];
    }
}
