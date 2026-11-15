<?php

namespace App\Http\Requests;

use App\Http\Requests\Traits\BookValidationMessages;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    use BookValidationMessages;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1000', 'max:' . date('Y')],
            'isbn' => ['required', 'string', 'regex:/^[0-9\\-X]{10,17}$/', 'unique:books,isbn'],
            'stock' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return $this->bookMessages();
    }
}
