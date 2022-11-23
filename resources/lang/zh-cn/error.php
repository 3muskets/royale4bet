<?php

return [

    //register
    'register.invalid_reg_cd' => '代理号码不存在',
    'register.invalid_currency' => '错误的货币选择',
    'register.username_special_character' => '用戶名不能含有特別符號',
    'register.mobile_numeric' => '手机号码必须是号码',
    'register.txn_pin_numeric' => '提款密码必须是4位数号码',
    'register.duplicate_username' => '用户名已存在',
    'register.duplicate_email' => '电子邮件已存在',
    'register.creation_failed' => '创建账号失败',
    'register.name.invalid' => '真实姓名只能含有字母',
    'register.usernamelength' => '用戶名只能在于5-10字符之间',
    'register.passwordlength' => '密码只能在于8-15字符之间',
    'register.name.empty' => '真实姓名不能空格',

    //login
    'login.multiple_login' => '您的戶口正在被其他设备使用, 请从新登录',
    'login.account_inactive'=> '您的户口已关闭 请联系上家',

    //dw
    'dw.invalid_type' => '类型错误',
    'dw.amount_zero' => '金额不能少于0',
    'dw.invalid_credit_length' => '金额不能多于15位数',
    'dw.invalid_credit' => '金额错误',
    'dw.insufficient_funds' => '余额不足',
    'dw.internal_error' => '内部错误',
    'dw.invalid_process' => '处理无效',
    'dw.txn_processed' => '交易已处理',
    'dw.emptybankname' => '请填写银行名字',
    'dw.emptymemberaccname' => '请填写银行账户注册姓名',
    'dw.invalidbankname' => '银行名字错误',
    'dw.invalidmemberaccname' => '银行账户注册姓名错误',
    'dw.invalidbankacc' => '银行账户错误',
    'dw.invalidtxn' => '转账收据错误',
    'dw.invalid_crypto_amount' => '提款金额不能少于',

    //msg
    'msg.nomember' =>'请选择',
    'msg.insertmsg' =>'请填写信息',
    'msg.insertsubject' =>'请填写主题',
    'msg.invalidmember' => '用户无效',
    'msg.internal_error' => '内部错误',

    //change pw
    'password.invalid_currentpassword' => '旧密码错误',
    'password.invalid_newpassword' => '新密码规格错误',
    'password.passwordscannotsame' => '新密码不能与旧密码相同',
    'password.passwordsnotmatch' => '密码与确认密码不相符',
    'password.invalid_password_length' => '密码只能在于8-15字符之间',
    'password.internal_error' => '内部错误',

    //bank info
    'bank_info.invalid_acc' => '银行户口错误',
    'bank_info.bankaccnotmatch' => '银行户口与确认银行户口不相符',
    'bank_info.banknamealphabet' => '银行名字错误',
    'bank_info.emptybankname' => '请填写银行名字',
    'bank_info.invalid_address_length' => '银行地址不能超过50个字母',
    'bank_info.internal_error' => '内部错误',
];