<?php

return [

    //register
    'register.invalid_reg_cd' => 'Invalid register code',
    'register.ref_code' => 'Invalid Referral Code',
    'register.invalid_currency' => 'Currency does not match',
    'register.duplicate_username' => 'Duplicate username',
    'register.duplicate_email' => 'Duplicate email address',
    'register.username_special_character' => 'Username must be in alphanumeric with/without dot',
    'register.mobile_numeric' => 'Mobile no. must be an digit number',
    'register.txn_pin_numeric' => 'Witdrawal PIN must be a 4-digit number',
    'register.creation_failed' => 'Member creation failed',
    'register.name.invalid' => 'Real name must be in alphabet only',
    'register.usernamelength' => 'Username must between 5-10 characters',
    'register.passwordlength' => 'Password must between 8-15 characters',
    'register.name.empty' => 'Real name cannot be empty',

    //login
    'login.multiple_login' => 'You have signed in other device',
    'login.account_inactive'=> 'Your account is inactive. Please contact your upline',

    //dw
    'dw.invalid_type' => 'Invalid type',
    'dw.amount_zero' => 'Amount must be more than 0',
    'dw.invalid_credit_length' => 'Credit amount cannot exceed 15 digits',
    'dw.invalid_credit' => 'Invalid credit',
    'dw.insufficient_funds' => 'Insufficient funds',
    'dw.internal_error' => 'Internal error',
    'dw.invalid_process' => 'Invalid process',
    'dw.txn_processed' => 'Transaction already processed',
    'dw.emptybankname' => 'Bank name cannot be empty',
    'dw.emptymemberaccname' => 'Account holder name cannot be empty',
    'dw.invalidbankname' => 'Invalid bank name',
    'dw.invalidmemberaccname' => 'Invalid account holder name',
    'dw.invalidbankacc' => 'Invalid account no.',
    'dw.invalidtxn' => 'Invalid transaction ID',
    'dw.invalid_crypto_amount' => 'Withdraw amount need to more than ',

    //msg
    'msg.nomember' =>'No Members Selected',
    'msg.insertmsg' =>'Please Insert The Message',
    'msg.insertsubject' =>'Please Insert The Subject',
    'msg.invalidmember' => 'Invalid Member',
    'msg.internal_error' => 'Internal Error',

    //change pw
    'password.invalid_currentpassword' => 'Invalid current password',
    'password.invalid_newpassword' => 'Invalid new password',
    'password.passwordscannotsame' => 'New password and old password must be different',
    'password.passwordsnotmatch' => 'Password confirmation does not match',
    'password.invalid_password_length' => 'Invalid password length (8-15 characters)',
    'password.internal_error' => 'Internal Error',

    //bank info
    'bank_info.invalid_acc' => 'Invalid bank account',
    'bank_info.bankaccnotmatch' => 'Bank acccount confirmation does not match',
    'bank_info.banknamealphabet' => 'Invalid bank name',
    'bank_info.emptybankname' => 'Please fill in bank name',
    'bank_info.invalid_address_length' => 'Bank branch address exceeds maximum length(50)',
    'bank_info.internal_error' => 'Internal Error',
];