<?php

namespace App\Http\Requests\Traits;

trait BookValidationMessages
{
    protected function bookMessages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode ter mais que 255 caracteres.',
            'author.required' => 'O autor é obrigatório.',
            'author.string' => 'O autor deve ser um texto.',
            'author.max' => 'O autor não pode ter mais que 255 caracteres.',
            'genre.required' => 'O gênero é obrigatório.',
            'genre.string' => 'O gênero deve ser um texto.',
            'genre.max' => 'O gênero não pode ter mais que 100 caracteres.',
            'year.required' => 'O ano é obrigatório.',
            'year.integer' => 'O ano deve ser um número.',
            'year.min' => 'O ano deve ser pelo menos 1000.',
            'year.max' => 'O ano não pode ser superior ao ano atual.',
            'isbn.required' => 'O ISBN é obrigatório.',
            'isbn.string' => 'O ISBN deve ser um texto.',
            'isbn.regex' => 'O ISBN deve estar no formato válido (10-17 caracteres, pode conter hífens e X).',
            'isbn.unique' => 'Este ISBN já está em uso.',
            'stock.required' => 'O estoque é obrigatório.',
            'stock.integer' => 'O estoque deve ser um número.',
            'stock.min' => 'O estoque deve ser pelo menos 1.',
            'description.string' => 'A descrição deve ser um texto.',
            'description.max' => 'A descrição não pode ter mais que 2000 caracteres.',
        ];
    }
}
