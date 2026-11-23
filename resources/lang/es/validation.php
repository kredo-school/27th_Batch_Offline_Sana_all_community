<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'email' => ':attribute debe ser una dirección de correo válida.',
    'unique' => ':attribute ya está en uso.',
    'string' => ':attribute debe ser una cadena.',
    'integer' => ':attribute debe ser un número entero.',
    'numeric' => ':attribute debe ser un número.',
    'boolean' => ':attribute debe ser verdadero o falso.',
    'array' => ':attribute debe ser un conjunto.',
    'date' => ':attribute no es una fecha válida.',
    'before_or_equal' => ':attribute debe ser una fecha anterior o igual a :date.',
    'min' => ['numeric' => ':attribute debe ser como mínimo :min.', 'string' => ':attribute debe tener al menos :min caracteres.'],
    'max' => ['numeric' => ':attribute no puede ser mayor que :max.', 'string' => ':attribute no puede tener más de :max caracteres.'],
    'between' => ['numeric' => ':attribute debe estar entre :min y :max.'],
    'in' => 'El :attribute seleccionado no es válido.',
    'image' => ':attribute debe ser una imagen.',
    'mimes' => ':attribute debe ser un archivo de tipo: :values.',
    'prohibited' => 'El campo :attribute no está permitido.',
    'attributes' => ['name' => 'nombre', 'username' => 'nombre de usuario', 'email' => 'correo electrónico', 'preferred_language' => 'idioma preferido', 'birthday_year' => 'año de nacimiento', 'birthday_month' => 'mes de nacimiento', 'birthday_day' => 'día de nacimiento', 'birthday' => 'fecha de nacimiento', 'avatar' => 'foto de perfil', 'cover_image' => 'imagen de portada', 'bio' => 'biografía', 'country' => 'país', 'region' => 'región / ciudad', 'occupation' => 'profesión', 'gender' => 'género'],
];
