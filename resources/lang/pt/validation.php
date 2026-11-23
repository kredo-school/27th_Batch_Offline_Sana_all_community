<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'required_with' => 'O campo :attribute é obrigatório quando :values está presente.',
    'email' => ':attribute deve ser um endereço de e-mail válido.',
    'unique' => ':attribute já está a ser utilizado.',
    'string' => ':attribute deve ser uma cadeia de caracteres.',
    'integer' => ':attribute deve ser um número inteiro.',
    'numeric' => ':attribute deve ser um número.',
    'boolean' => ':attribute deve ser verdadeiro ou falso.',
    'array' => ':attribute deve ser uma matriz.',
    'date' => ':attribute não é uma data válida.',
    'before_or_equal' => ':attribute deve ser uma data anterior ou igual a :date.',
    'min' => ['numeric' => ':attribute deve ser, no mínimo, :min.', 'string' => ':attribute deve ter pelo menos :min caracteres.'],
    'max' => ['numeric' => ':attribute não pode ser superior a :max.', 'string' => ':attribute não pode ter mais de :max caracteres.'],
    'between' => ['numeric' => ':attribute deve estar entre :min e :max.'],
    'in' => 'O :attribute selecionado é inválido.',
    'image' => ':attribute deve ser uma imagem.',
    'mimes' => ':attribute deve ser um ficheiro do tipo: :values.',
    'prohibited' => 'O campo :attribute não é permitido.',
    'attributes' => ['name' => 'nome', 'username' => 'nome de utilizador', 'email' => 'e-mail', 'preferred_language' => 'idioma preferido', 'birthday_year' => 'ano de nascimento', 'birthday_month' => 'mês de nascimento', 'birthday_day' => 'dia de nascimento', 'birthday' => 'data de nascimento', 'avatar' => 'fotografia de perfil', 'cover_image' => 'imagem de capa', 'bio' => 'biografia', 'country' => 'país', 'region' => 'região / cidade', 'occupation' => 'profissão', 'gender' => 'género'],
];
