<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

use Auth;
use Log;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public static function getDetails()
    {
        $balance = UserController::getBalance();
        $currency = UserController::getCurrency();
        $details = UserController::getMemberDetails();

        $data = ['balance'=>$balance, 'currency'=>$currency, 'email'=>$details[0]->email, 'created_at'=>$details[0]->created_at, 'last_login'=>$details[0]->last_login];

        return $data;
    }

    public static function editProfile(Request $request)
    {
        try
        {
            $userId = Auth::id();

            $firstName = $request->input('first_name');
            $lastName = $request->input('last_name');
            $birthDate = $request->input('birthdate');
            $email = $request->input('email');
            $mobile = $request->input('mobile');
            $country = $request->input('countryCode');
            $city = $request->input('city');
            $address = $request->input('address');

            $errMsg = [];

            $db = DB::select("SELECT email FROM member WHERE email = ?", [$email]);

            if(sizeof($db) > 0)
            {
                if($db[0]->email != $email)
                {
                    array_push($errMsg, __('error.profile.email_duplicate')); 
                }
            }

            if(!is_numeric($mobile))
            {
                array_push($errMsg, __('error.profile.invalid_mobile')); 
            }

            if (ctype_alpha(str_replace(' ', '', $firstName)) === false) 
            {
                array_push($errMsg, __('error.profile.invalid_first_name')); 
            }

            if (ctype_alpha(str_replace(' ', '', $lastName)) === false) 
            {
                array_push($errMsg, __('error.profile.invalid_last_name')); 
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $db = DB::update("
                    UPDATE member
                    SET first_name = ?
                        ,last_name = ?
                        ,birthdate = ?
                        ,mobile = ?
                        ,email = ?
                        ,country = ?
                        ,city = ?
                        ,address = ?
                    WHERE id = ?"
                    ,[$firstName, $lastName, $birthDate, $mobile, $email, $country, $city, $address, $userId]
                );

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            $response = [
                            'status' => 0
                            ,'error' => __('error.password.internal_error')
                        ];

            Log::debug($e);

            return json_encode($response);
        }
        
    }

    public static function changePassword(Request $request)
    {
        try
        {
            $userId = Auth::id();

            $oldPassword = $request->input('original_pw');
            $newPassword = $request->input('new_password');
            $confirmPassword = $request->input('confirm_password');

            //validation
            $errMsg = [];

            $db = DB::select('
                    SELECT password 
                    FROM member 
                    WHERE id = ?
                    ',[$userId]
                );

            $currentPassword = $db[0]->password;

            if(!Hash::check($oldPassword,$currentPassword) || !$oldPassword)
            {
               array_push($errMsg, __('error.password.invalid_currentpassword')); 
            }

            if(!$newPassword)
            {
                array_push($errMsg, __('error.password.invalid_newpassword'));
            }

            if(Hash::check($newPassword,$currentPassword))
            {
                array_push($errMsg, __('error.password.passwordscannotsame'));
            }

            if($newPassword != $confirmPassword)
            {
                array_push($errMsg, __('error.password.passwordsnotmatch'));
            }

            if (!Helper::checkInputLength($newPassword,8,15)) 
            {
                array_push($errMsg, __('error.password.invalid_password_length'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $newPassword = Hash::make($newPassword);

            $db = DB::update('UPDATE member SET password = ? WHERE id = ?',
                    [$newPassword,$userId]
                  );

            $response = ['status' => 1];

            return json_encode($response);
        }
        catch(\Exception $e)
        {
            $response = [
                            'status' => 0
                            ,'error' => __('error.password.internal_error')
                        ];

            Log::debug($e);

            return json_encode($response);
        }
    }

    public static function editBankInfo(Request $request)
    {
        try
        {
            $userId = Auth::id();
            $bank = $request->input('bank');
            $bankAcc = $request->input('bank_acc');
            $bankAccName = $request->input('bank_acc_name');

            $errMsg = [];

            if(!is_numeric($bankAcc))
            {
                array_push($errMsg, __('error.bank_info.invalid_acc'));
            }

            if($bank == '')
            {
                array_push($errMsg,  __('error.bank_info.emptybankname'));
            }

            if(!Helper::checkInputFormat('alphabetWithSpace',$bank))
            {
                array_push($errMsg,  __('error.bank_info.banknamealphabet'));
            }

            if($errMsg)
            {
                $response = ['status' => 0
                            ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            $db = DB::update('UPDATE member_bank_info
                                SET bank = ?
                                ,acc_no = ?
                                ,name = ?
                                WHERE member_id = ?'
                                ,[$bank, $bankAcc, $bankAccName, $userId]);

            $response = ['status' => 1];

            return json_encode($response);

        }
        catch(\Exception $e)
        {

            $response = [
                            'status' => 0
                            ,'error' => __('error.bank_info.internal_error')
                        ];

            log::debug($e);

            return json_encode($response);
        }
    }
}



















