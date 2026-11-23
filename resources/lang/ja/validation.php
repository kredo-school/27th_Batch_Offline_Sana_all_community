<?php

return [
    'required' => ':attributeは必須です。',
    'required_with' => ':valuesが入力されている場合、:attributeは必須です。',
    'email' => ':attributeには有効なメールアドレスを入力してください。',
    'unique' => 'この:attributeはすでに使用されています。',
    'string' => ':attributeは文字列で入力してください。',
    'integer' => ':attributeは整数で入力してください。',
    'numeric' => ':attributeは数値で入力してください。',
    'boolean' => ':attributeはtrueまたはfalseで入力してください。',
    'array' => ':attributeは配列で入力してください。',
    'date' => ':attributeには有効な日付を入力してください。',
    'before_or_equal' => ':attributeには:date以前の日付を入力してください。',
    'min' => ['numeric' => ':attributeは:min以上で入力してください。', 'string' => ':attributeは:min文字以上で入力してください。'],
    'max' => ['numeric' => ':attributeは:max以下で入力してください。', 'string' => ':attributeは:max文字以内で入力してください。'],
    'between' => ['numeric' => ':attributeは:minから:maxの間で入力してください。'],
    'in' => '選択された:attributeは無効です。',
    'image' => ':attributeには画像を指定してください。',
    'mimes' => ':attributeは次の形式のファイルを指定してください: :values。',
    'prohibited' => ':attributeは指定できません。',
    'attributes' => [
        'name' => '名前', 'username' => 'ユーザー名', 'email' => 'メールアドレス',
        'preferred_language' => '優先言語', 'birthday_year' => '誕生年', 'birthday_month' => '誕生月',
        'birthday_day' => '誕生日', 'birthday' => '誕生日', 'avatar' => 'プロフィール写真',
        'cover_image' => 'カバー画像', 'bio' => '自己紹介', 'country' => '国',
        'region' => '地域 / 都市', 'occupation' => '職業', 'gender' => '性別',
    ],
];
