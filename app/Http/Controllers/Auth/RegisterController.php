<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

use App\Http\Controllers\Helper;

use Log;
use Session;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => 'required',
            'password' => 'required|confirmed',
           /* 'reg_cd' => 'required',*/
            // 'captcha' => 'required|captcha',
            // 'name' => 'required',
            'mobile' => 'required',
            'email' => 'required|string|email|max:255',
        ]);
    }

    protected function register(Request $request)
    {
        $validator = $this->validator($request->all());

        $validator->validate();

        //custom validation
        $username = $request->username;
        $password = $request->password;
        $regCd = $request->reg_cd;
        $mobile = $request->mobile;
        // $name = $request->name;
        $email = $request->email;

        $refcode = $request->refcode;

        //check valid reg_cd

        //check valid refer_code
        if($refcode != null)
        {
            $db = DB::select("SELECT id FROM member WHERE id = ?", [$refcode]);

            if(sizeOf($db) == 0)
            {
                $validator->getMessageBag()->add('refcode',__('error.register.ref_code'));
            }

        }

        //check username char
        if(!Helper::checkInputFormat('alphanumericWithDot', $username))
        {
            $validator->getMessageBag()->add('username',__('error.register.username_special_character'));
        }
        else
        {
            //check username in used
            $db = DB::select("SELECT username FROM member WHERE username = ?", [$username]);

            if(sizeof($db) > 0)
            {
                $validator->getMessageBag()->add('username',__('error.register.duplicate_username'));
            }
        }

        if (!Helper::checkInputLength($username,5,10)) 
        {
            $validator->getMessageBag()->add('username',__('error.register.usernamelength'));
        }

        if (!Helper::checkInputLength($password,8,15)) 
        {
            $validator->getMessageBag()->add('password',__('error.register.passwordlength'));
        }

        //check mobile
        if(!is_numeric($mobile))
        {
            $validator->getMessageBag()->add('mobile',__('error.register.mobile_numeric'));
        }

        $db = DB::select("SELECT email FROM member WHERE email = ?", [$email]);

        if(sizeof($db) > 0)
        {
            $validator->getMessageBag()->add('email',__('error.register.duplicate_email'));
        }

        // if($name == '')
        // {
        //     $validator->getMessageBag()->add('name',__('error.register.name.empty'));
        // }

        // //check name only white space
        // if(!Helper::checkInputFormat('alphabetWithSpace', $name))
        // {
        //     $validator->getMessageBag()->add('name',__('error.register.name.invalid'));
        // }

        //got error
        if(sizeOf($validator->getMessageBag()) > 0)
        {

            $optionsCurrency = self::getOptionsCurrency();

            // $defaultRegCd = self::getDefaultRegCd();

            $request->flash();

            return view('auth.register')->withErrors($validator)
                                        ->with(['optionsCurrency' => $optionsCurrency
                                                // ,'defaultRegCd' => $defaultRegCd
                                            ]);
        }

        //no error
        $user = $this->create($request->all());

        //create user failed
        if(!$user)
        {
            $validator->getMessageBag()->add('username',__('error.register.creation_failed'));

            $optionsCurrency = self::getOptionsCurrency();

            // $defaultRegCd = self::getDefaultRegCd();

            $request->flash();

            return view('auth.register')->withErrors($validator)
                                        ->with(['optionsCurrency' => $optionsCurrency
                                                // ,'defaultRegCd' => $defaultRegCd
                                    ]);
        }

        event(new Registered($user));

        $this->guard()->login($user);

        return $this->registered($request, $user)
                        ?: redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        DB::beginTransaction();

        try
        {
            $ip = \Request::ip();

           $loginToken = \Session::getId();
            \Session::put('login_token',$loginToken);

/*            $db = DB::select("SELECT id FROM admin where is_default = 1");
            $adminId = $db[0]->id;*/

            $user = User::create([
                        'username' => strtoupper($data['username']),
                        'password' => Hash::make($data['password']),
                        'status' => 'a',
                        'suspended' => 0,
                        'admin_id' => 0,
                        'referral_id' => $data['refcode'],
                        // 'fullname' => $data['name'],
                        'mobile' => $data['mobile'],
                        'email' => $data['email'],
                        'last_ip' => $ip,
                        'last_login' => NOW(),
                        'login_token' => $loginToken
                    ]);

            //member credit
            DB::insert('INSERT INTO member_credit(member_id,available,dw_turnover)
                    VALUES (?,0,0)'
                    ,[$user->id]);

            //member bank info
            DB::insert('INSERT INTO member_bank_info(member_id)
                    VALUES (?)'
                    ,[$user->id]);

            //member bonus turnover 
            for($i = 1; $i <= 3; $i++)
            {
                DB::insert('INSERT INTO member_bonus_turnover(member_id,category,turnover,created_at)
                        VALUES (?,?,0,Now())'
                        ,[$user->id,$i]);
            }

            //no error
            DB::commit();

            return $user;

        }
        catch(\Exception $e)
        {
            log::Debug($e);
            DB::rollback();

            return 0;
        }

    }

    public function showRegisterForm()
    {
        $optionsCurrency = self::getOptionsCurrency();

        // $defaultRegCd = self::getDefaultRegCd();

        return view('auth.register')->with(['optionsCurrency'=> $optionsCurrency
                                                // ,'defaultRegCd' => $defaultRegCd
                                        ]);
    }

    public static function getDefaultRegCd()
    {
        $db = DB::select("SELECT reg_cd FROM admin WHERE is_default = ?", [1]);

        if(sizeOf($db) > 0)
        {
            return $db[0]->reg_cd;
        }

        return '';
    }

    public static function getOptionsCurrency()
    {
        return [
                    ['MYR', 'MYR']
/*                    ,['CNY', 'CNY']
                    ,['USD', 'USD']*/

                ];
    }
}
