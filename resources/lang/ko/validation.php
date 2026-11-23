<?php

return [
    'required' => ':attribute 필드는 필수입니다.',
    'required_with' => ':values가 있으면 :attribute 필드는 필수입니다.',
    'email' => ':attribute은(는) 유효한 이메일 주소여야 합니다.',
    'unique' => '이미 사용 중인 :attribute입니다.',
    'string' => ':attribute은(는) 문자열이어야 합니다.',
    'integer' => ':attribute은(는) 정수여야 합니다.',
    'numeric' => ':attribute은(는) 숫자여야 합니다.',
    'boolean' => ':attribute은(는) true 또는 false여야 합니다.',
    'array' => ':attribute은(는) 배열이어야 합니다.',
    'date' => ':attribute은(는) 유효한 날짜여야 합니다.',
    'before_or_equal' => ':attribute은(는) :date 이전 또는 같은 날짜여야 합니다.',
    'min' => ['numeric' => ':attribute은(는) 최소 :min이어야 합니다.', 'string' => ':attribute은(는) 최소 :min자여야 합니다.'],
    'max' => ['numeric' => ':attribute은(는) :max 이하여야 합니다.', 'string' => ':attribute은(는) :max자를 초과할 수 없습니다.'],
    'between' => ['numeric' => ':attribute은(는) :min에서 :max 사이여야 합니다.'],
    'in' => '선택한 :attribute이(가) 유효하지 않습니다.',
    'image' => ':attribute은(는) 이미지 파일이어야 합니다.',
    'mimes' => ':attribute은(는) 다음 파일 형식이어야 합니다: :values.',
    'prohibited' => ':attribute 필드는 사용할 수 없습니다.',
    'attributes' => [
        'name' => '이름', 'username' => '사용자 이름', 'email' => '이메일',
        'preferred_language' => '선호 언어', 'birthday_year' => '출생 연도', 'birthday_month' => '출생 월',
        'birthday_day' => '출생일', 'birthday' => '생일', 'avatar' => '프로필 사진',
        'cover_image' => '커버 이미지', 'bio' => '소개', 'country' => '국가',
        'region' => '지역 / 도시', 'occupation' => '직업', 'gender' => '성별',
    ],
];
