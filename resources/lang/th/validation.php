<?php

return [
    'required' => 'กรุณากรอก:attribute',
    'required_with' => 'กรุณากรอก:attribute เมื่อมี:values',
    'email' => ':attribute ต้องเป็นที่อยู่อีเมลที่ถูกต้อง',
    'unique' => ':attribute นี้ถูกใช้งานแล้ว',
    'string' => ':attribute ต้องเป็นข้อความ',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'numeric' => ':attribute ต้องเป็นตัวเลข',
    'boolean' => ':attribute ต้องเป็น true หรือ false',
    'array' => ':attribute ต้องเป็นอาร์เรย์',
    'date' => ':attribute ต้องเป็นวันที่ที่ถูกต้อง',
    'before_or_equal' => ':attribute ต้องเป็นวันที่ก่อนหรือเท่ากับ :date',
    'min' => ['numeric' => ':attribute ต้องมีค่าอย่างน้อย :min', 'string' => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร'],
    'max' => ['numeric' => ':attribute ต้องมีค่าไม่เกิน :max', 'string' => ':attribute ต้องมีไม่เกิน :max ตัวอักษร'],
    'between' => ['numeric' => ':attribute ต้องอยู่ระหว่าง :min ถึง :max'],
    'in' => 'ค่า:attribute ที่เลือกไม่ถูกต้อง',
    'image' => ':attribute ต้องเป็นรูปภาพ',
    'mimes' => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'prohibited' => 'ไม่อนุญาตให้ใช้:attribute',
    'attributes' => ['name' => 'ชื่อ', 'username' => 'ชื่อผู้ใช้', 'email' => 'อีเมล', 'preferred_language' => 'ภาษาที่ต้องการ', 'birthday_year' => 'ปีเกิด', 'birthday_month' => 'เดือนเกิด', 'birthday_day' => 'วันเกิด', 'birthday' => 'วันเกิด', 'avatar' => 'รูปโปรไฟล์', 'cover_image' => 'รูปภาพหน้าปก', 'bio' => 'ประวัติ', 'country' => 'ประเทศ', 'region' => 'ภูมิภาค / เมือง', 'occupation' => 'อาชีพ', 'gender' => 'เพศ'],
];
