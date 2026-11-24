<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'required_with' => 'حقل :attribute مطلوب عند إدخال :values.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالحًا.',
    'unique' => ':attribute مستخدم بالفعل.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'boolean' => 'يجب أن تكون قيمة :attribute صحيحة أو خاطئة.',
    'array' => 'يجب أن يكون :attribute مصفوفة.',
    'date' => 'التاريخ في :attribute غير صالح.',
    'before_or_equal' => 'يجب أن يكون تاريخ :attribute قبل :date أو مساويًا له.',
    'min' => ['numeric' => 'يجب ألا يقل :attribute عن :min.', 'string' => 'يجب ألا يقل :attribute عن :min أحرف.'],
    'max' => ['numeric' => 'يجب ألا يزيد :attribute عن :max.', 'string' => 'يجب ألا يزيد :attribute عن :max حرفًا.'],
    'between' => ['numeric' => 'يجب أن يكون :attribute بين :min و:max.'],
    'in' => 'قيمة :attribute المحددة غير صالحة.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'mimes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'prohibited' => 'لا يُسمح بحقل :attribute.',
    'attributes' => ['name' => 'الاسم', 'username' => 'اسم المستخدم', 'email' => 'البريد الإلكتروني', 'preferred_language' => 'اللغة المفضلة', 'birthday_year' => 'سنة الميلاد', 'birthday_month' => 'شهر الميلاد', 'birthday_day' => 'يوم الميلاد', 'birthday' => 'تاريخ الميلاد', 'avatar' => 'صورة الملف الشخصي', 'cover_image' => 'صورة الغلاف', 'bio' => 'النبذة الشخصية', 'country' => 'البلد', 'region' => 'المنطقة / المدينة', 'occupation' => 'المهنة', 'gender' => 'الجنس'],
];
