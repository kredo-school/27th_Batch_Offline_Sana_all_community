<?php

return [
    'required' => ':attribute不能为空。',
    'required_with' => '填写:values时，:attribute不能为空。',
    'email' => ':attribute必须是有效的电子邮件地址。',
    'unique' => ':attribute已被使用。',
    'string' => ':attribute必须是字符串。',
    'integer' => ':attribute必须是整数。',
    'numeric' => ':attribute必须是数字。',
    'boolean' => ':attribute必须为 true 或 false。',
    'array' => ':attribute必须是数组。',
    'date' => ':attribute不是有效日期。',
    'before_or_equal' => ':attribute必须是早于或等于:date的日期。',
    'min' => ['numeric' => ':attribute不能小于:min。', 'string' => ':attribute至少需要:min个字符。'],
    'max' => ['numeric' => ':attribute不能大于:max。', 'string' => ':attribute不能超过:max个字符。'],
    'between' => ['numeric' => ':attribute必须介于:min和:max之间。'],
    'in' => '所选的:attribute无效。',
    'image' => ':attribute必须是图片。',
    'mimes' => ':attribute必须是以下类型的文件: :values。',
    'prohibited' => '不允许使用:attribute字段。',
    'attributes' => ['name' => '姓名', 'username' => '用户名', 'email' => '电子邮件', 'preferred_language' => '首选语言', 'birthday_year' => '出生年份', 'birthday_month' => '出生月份', 'birthday_day' => '出生日期', 'birthday' => '生日', 'avatar' => '头像', 'cover_image' => '封面图片', 'bio' => '个人简介', 'country' => '国家', 'region' => '地区 / 城市', 'occupation' => '职业', 'gender' => '性别'],
];
