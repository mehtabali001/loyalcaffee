<?php

// Arabic validation messages for the public forms (reservation + contact).
return [
    'required' => 'حقل :attribute مطلوب.',
    'required_without' => 'يرجى إدخال :attribute أو :values.',
    'string' => 'يجب أن يكون :attribute نصاً.',
    'email' => 'يرجى إدخال بريد إلكتروني صحيح.',
    'integer' => 'يجب أن يكون :attribute رقماً صحيحاً.',
    'numeric' => 'يجب أن يكون :attribute رقماً.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'in' => 'القيمة المختارة في :attribute غير صحيحة.',
    'exists' => 'القيمة المختارة في :attribute غير موجودة.',
    'date_format' => 'صيغة :attribute غير صحيحة.',
    'after_or_equal' => 'يجب أن يكون :attribute اليوم أو بعده.',
    'before' => 'يمكن الحجز حتى 90 يوماً مقدماً فقط.',
    'prohibited' => 'تعذّر إرسال النموذج.',
    'min' => [
        'numeric' => 'يجب ألا يقل :attribute عن :min.',
        'string' => 'يجب ألا يقل :attribute عن :min أحرف.',
    ],
    'max' => [
        'numeric' => 'يجب ألا يزيد :attribute عن :max.',
        'string' => 'يجب ألا يزيد :attribute عن :max حرفاً.',
    ],
    'attributes' => [
        'name' => 'الاسم',
        'phone' => 'رقم الهاتف',
        'email' => 'البريد الإلكتروني',
        'guests' => 'عدد الضيوف',
        'date' => 'التاريخ',
        'time' => 'الوقت',
        'seating' => 'نوع الجلسة',
        'event_id' => 'المباراة',
        'notes' => 'الملاحظات',
        'subject' => 'الموضوع',
        'message' => 'الرسالة',
    ],
];
