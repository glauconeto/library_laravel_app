<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'genre' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1000', 'max:' . date('Y')],
            'isbn' => ['required', 'string', 'max:20', 'unique:books,isbn'],
            'stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
