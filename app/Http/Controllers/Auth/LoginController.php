<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $allowStatus = ['a'];
    protected $redirectLogin = '/login';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username(){
        return 'username';
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        //prepare redirect path
        $redirectToLogin = '/login';

        $username = $request->input('username');

        //check not exists
        $data = DB::SELECT("
                SELECT status, up1_inactive, up2_inactive, up3_inactive
                FROM member
                where username = ?
            ", [
                $username
            ]);


        if($data)
        {
            $up1Inactive = $data[0]->up1_inactive;
            $up2Inactive = $data[0]->up2_inactive;
            $up3Inactive = $data[0]->up3_inactive;
            $status = $data[0]->status;
          

            if ($status == "i" || $up1Inactive == 1 || $up2Inactive == 1 || $up3Inactive == 1) 
            {
                return redirect()->to($redirectToLogin)
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => __('auth.inactive'),
                ]); 
            }
            else
            {
                return redirect()->to($redirectToLogin)
                ->withInput($request->only($this->username()))
                ->withErrors([
                    $this->username() => __('auth.failed'),
                ]);
            }
        }
        else
        {
            return redirect()->to($redirectToLogin)
            ->withInput($request->only($this->username()))
            ->withErrors([
                $this->username() => __('auth.failed'),
            ]);
        }
    }

    protected function credentials(Request $request)
    {
        return ['username'=>$request->{$this->username()},'password'=>$request->password,'status'=>$this->allowStatus,'up1_inactive'=>null,'up2_inactive'=>null,'up3_inactive'=>null];
    }

    protected function authenticated($request,$user)
    {       
        $userId = Auth::id();
        $loginToken = \Session::getId();
        $ip = \Request::ip();
        $username = $request->username;
        $password = $request->password;
        
        \Session::put('login_token',$loginToken);

        $isDuplicateIp = 0;


        $partIp = explode(".", $ip, 4);

        $firstPartIp = $partIp[0];
        $secondPartIp = $partIp[1];


        DB::UPDATE("
            UPDATE member
            set login_token = ?
            ,last_ip = ?
            ,last_login = NOW()
            WHERE id = ?
        ", [
            $loginToken
            ,$ip
            ,$userId
        ]);
        

        //check is it duplicate ip with other user
        $db = DB::select("
                SELECT id 
                FROM member 
                WHERE (SUBSTRING_INDEX(last_ip,'.',1) = ?
                AND SUBSTRING_INDEX(SUBSTRING_INDEX(last_ip,'.',2),'.',-1) = ?)
                ",[$firstPartIp,$secondPartIp]);



        //update duplicate ip
        if(sizeof($db) != 0)
        {
            $isDuplicateIp = 1;

            DB::UPDATE("
                UPDATE member
                SET is_duplicate_ip = ?
                WHERE (SUBSTRING_INDEX(last_ip,'.',1) = ?
                AND SUBSTRING_INDEX(SUBSTRING_INDEX(last_ip,'.',2),'.',-1) = ?)
            ", [
                $isDuplicateIp
                ,$firstPartIp
                ,$secondPartIp
            ]);

        } 



        return redirect($this->redirectTo); 
    }

    public function logout(Request $request) 
    {
        $userId = Auth::id();
        
        Auth::logout();

        DB::UPDATE("
            UPDATE member
            set login_token = NULL
            WHERE id = ?
        ", [
            $userId
        ]);

        return redirect($this->redirectTo); 
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }
}
