<?php

return [

    //register
    'register.invalid_reg_cd' => 'رمز التسجيل غير صالحة',
    'register.invalid_currency' => 'لا تتطابق مع العملة',
    'register.duplicate_username' => 'اسم المستخدم مكررة',
    'register.duplicate_email' => 'البريد الإلكتروني مكررة',
    'register.username_special_character' => 'اسم المستخدم يجب أن يكون في أبجدية مع / بدون نقطة',
    'register.mobile_numeric' => 'رقم الموبايل. يجب أن يكون عدد  أرقام',
    'register.txn_pin_numeric' => 'يجب أن يكون الانسحاب PIN عدد من 4 أرقام',
    'register.creation_failed' => 'فشل إنشاء الأعضاء',
    'register.name.invalid' => 'يجب أن يكون الاسم الحقيقي في الأبجدية فقط',
    'register.name.empty' => 'الاسم الحقيقي لا يمكن أن يكون فارغا',

    //login
    'login.multiple_login' => 'كنت قد وقعت في جهاز آخر',
    'login.account_inactive'=> 'حسابك غير نشط. يرجى الاتصال بلين الخاص',

    //dw
    'dw.invalid_type' => 'نوع غير صالح',
    'dw.amount_zero' => 'يجب أن يكون المبلغ أكثر من 0',
    'dw.invalid_credit_length' => 'مبلغ الائتمان لا يمكن أن يتجاوز 15 رقما',
    'dw.invalid_credit' => 'الائتمان غير صالح',
    'dw.insufficient_funds' => 'رصيد غير كاف',
    'dw.internal_error' => 'خطأ داخلي',
    'dw.invalid_process' => 'عملية غير صالحة',
    'dw.txn_processed' => 'عملية معالجة بالفعل',
    'dw.emptybankname' => 'يرجى ملء اسم البنك',
    'dw.emptymemberaccname' => 'يرجى ملء الاسم المسجل للحسابالمصرفي',
    'dw.invalidbankname' => 'اسم البنك غير صحيح',
    'dw.invalidmemberaccname' => 'اسم تسجيل الحساب المصرفي غيرصحيح',
    'dw.invalidbankacc' => 'خطأ في الحساب المصرفي',
    'dw.invalidtxn' => 'إيصال نقل خاطئ',

    //msg
    'msg.nomember' => 'تحديد أي الأعضاء',
    'msg.insertmsg' => 'الرجاء إدخال رسالة',
    'msg.insertsubject' => 'الرجاء إدخال موضوع',
    'msg.invalidmember' => 'عضو غير صالح',
    'msg.internal_error' => 'خطأ داخلي',

    //change pw
    'password.invalid_currentpassword' => 'كلمة مرور غير صحيحة',
    'password.invalid_newpassword' => 'كلمة مرور جديدة غير صالحة',
    'password.passwordscannotsame' => 'يجب أن تكون كلمة المرور الجديدة وكلمة المرور القديمة مختلفة',
    'password.passwordsnotmatch' => 'لا يتطابق مع تأكيد كلمة المرور',
    'password.invalid_password_length' => 'طول كلمة المرور غير صالحة (8-15 حرفا)',
    'password.internal_error' => 'خطأ داخلي',

    //bank info
    'bank_info.invalid_acc' => 'حساب مصرفي غير صالح',
    'bank_info.bankaccnotmatch' => 'لا يتطابق تأكيدا حساب مصرفي',
    'bank_info.banknamealphabet' => 'اسم البنك غير صالح',
    'bank_info.emptybankname' => 'يرجى ملء اسم البنك',
    'bank_info.internal_error' => 'خطأ داخلي',
];