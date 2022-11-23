<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;

use Auth;
use App;
use Log;
use Lang;

class CryptoController extends Controller
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

    public static function createAccessToken($data)
    {
        $key = env('CRYPTO_KEY');
        $sign_str = '';

        ksort($data);

        foreach($data as $k=>$v)
        {
            if($k!=="access_tonken")
                $sign_str .= $k."|".$v;
        }

        return MD5($sign_str.$key);
    }

    public static function createUser()
    {
        try
        {
            $url = env('CRYPTO_URL');
            $method = 'walletopen.create_user';
            $time = time();

            //user credential
            $userId = Auth::id();

            $data = array(
                "access_tonken" => "",
                "method" => $method,
                "time" => $time,
                "out_user_id" => $userId,
                );

            //create access Token
            $accessTonken = self::createAccessToken($data);

            $data['access_tonken'] = $accessTonken;

            $str = "";
            foreach ($data as $key => $val) 
            {
                $str = $str . $key . "=" . $val . "&";
            }

            $createUserUrl = $url.'?'.$str;

            $response = Helper::getData($createUserUrl);
            $response = json_decode($response,true);

            if ($response['status'] == 1) 
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return false;
        }
    }

    public static function getUserAddress()
    {
        try
        {
            $url = env('CRYPTO_URL');
            $method = 'walletopen.get_user_address';
            $time = time();

            $createUser = self::createUser();

            if (!$createUser) 
            {
                return '';
            }

            //user credential
            $userId = Auth::id();

            $db = DB::select("SELECT created_at 
                            FROM member_dw
                            WHERE member_id = ?
                                AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
                            ORDER BY created_at DESC"
                            ,[$userId]);

            //Limit not to debit 3 times in 1 munites
            if (sizeOf($db) >= 3)
            {
                return '';
            }

            $data = array(
                "access_tonken" => "",
                "method" => $method,
                "time" => $time,
                "out_user_id" => $userId,
                );

            //create access Token
            $accessTonken = self::createAccessToken($data);

            $data['access_tonken'] = $accessTonken;

            $str = "";
            foreach ($data as $key => $val) 
            {
                $str = $str . $key . "=" . $val . "&";
            }

            $userAddressUrl = $url.'?'.$str;

            log::debug($userAddressUrl);

            $response = Helper::getData($userAddressUrl);
            $response = json_decode($response,true);

            log::debug($response);

            if ($response['status'] == 1) 
            {
                $address = $response['data']['address'];

                return $address;
            }
            else
            {
                return '';
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return '';
        }
    }

    public static function getRate()
    {
        try
        {
            $url = env('CRYPTO_URL');
            $method = 'walletopen.get_rate';
            $time = time();

            $data = array(
                "access_tonken" => "",
                "method" => $method,
                "time" => $time,
                );

            //create access Token
            $accessTonken = self::createAccessToken($data);

            $data['access_tonken'] = $accessTonken;

            $str = "";
            foreach ($data as $key => $val) 
            {
                $str = $str . $key . "=" . $val . "&";
            }

            $getRateUrl = $url.'?'.$str;

            log::debug($getRateUrl);

            $response = Helper::getData($getRateUrl);
            $response = json_decode($response,true);

            log::debug($getRateUrl);
            

            if ($response['status'] == 1) 
            {
                return $response['data'];
            }
            else
            {
                return '';
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return '';
        }
    }
}
