<?php

return [
    'required' => 'Поле «:attribute» обязательно.',
    'required_with' => 'Поле «:attribute» обязательно, если указано поле «:values».',
    'email' => 'В поле «:attribute» должен быть указан действительный адрес электронной почты.',
    'unique' => 'Значение поля «:attribute» уже используется.',
    'string' => 'Поле «:attribute» должно быть строкой.',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'numeric' => 'Поле «:attribute» должно быть числом.',
    'boolean' => 'Поле «:attribute» должно иметь значение true или false.',
    'array' => 'Поле «:attribute» должно быть массивом.',
    'date' => 'Поле «:attribute» содержит недопустимую дату.',
    'before_or_equal' => 'Дата в поле «:attribute» должна быть не позже :date.',
    'min' => ['numeric' => 'Значение «:attribute» должно быть не меньше :min.', 'string' => 'Поле «:attribute» должно содержать не менее :min символов.'],
    'max' => ['numeric' => 'Значение «:attribute» не может быть больше :max.', 'string' => 'Поле «:attribute» не может содержать более :max символов.'],
    'between' => ['numeric' => 'Значение «:attribute» должно быть от :min до :max.'],
    'in' => 'Выбранное значение поля «:attribute» недопустимо.',
    'image' => 'Поле «:attribute» должно быть изображением.',
    'mimes' => 'Поле «:attribute» должно быть файлом одного из типов: :values.',
    'prohibited' => 'Поле «:attribute» запрещено.',
    'attributes' => ['name' => 'имя', 'username' => 'имя пользователя', 'email' => 'электронная почта', 'preferred_language' => 'предпочтительный язык', 'birthday_year' => 'год рождения', 'birthday_month' => 'месяц рождения', 'birthday_day' => 'день рождения', 'birthday' => 'дата рождения', 'avatar' => 'фотография профиля', 'cover_image' => 'обложка', 'bio' => 'биография', 'country' => 'страна', 'region' => 'регион / город', 'occupation' => 'профессия', 'gender' => 'пол'],
];
