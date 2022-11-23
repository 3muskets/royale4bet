<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use App\Http\Controllers\Provider;
use App\Http\Controllers\UserController;
use Log;
use DateTime;
use Auth;
use App;
use DES;

class SAController extends Controller
{
    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en_US'
                    ,'zh-cn'  => 'zh-Hans'
                    ,'ar'  => 'en'
                );

        return $locale[App::getLocale()];
    }

    public static function getGame($isMobile)
    {
        try 
        {
            $username = Auth::user()->username;
            //check status
            $status = Auth::user()->status;
            
            if ($status != 'a') 
            {
                return 'Inactive Member!';
            }

            $hostName = env('SA_HOSTNAME');
            $loader = env('SA_LOADER');
            $secretKey = env('SA_SECRET_KEY');
            $key = env('SA_ENCRYPTKEY');
            $md5key = env('SA_MD5Key');
            $lobbyCode = env('SA_LOBBY_CODE');
            $date = date('YmdHis', time());
            $currency = env('CURRENCY');
            $language = self::mapLocale();

            // $isMobile = false;

            // $language = 'en';
            $qs = "method=LoginRequest&Key=".$secretKey."&Time=".$date."&Username=".$username."&CurrencyType=".$currency;
            $s = md5($qs.$md5key.$date.$secretKey);

            $q = self::encrypt($qs,$key);

            $data = http_build_query(array('q' => $q, 's' => $s));

            $header = array('Content-Type: application/x-www-form-urlencoded');

            $response = Helper::postData($hostName,$data,$header);

            $xml = simplexml_load_string($response) or die("Error: Cannot create object");

            if ($xml->ErrorMsgId == 0)
            {
                $token = $xml->Token;
                $displayName = $xml->DisplayName;

                $launchURL = $loader.'?username='.$displayName.'&token='.$token.'&lobby='.$lobbyCode.'&mobile='.$isMobile;

                log::debug($launchURL);
                

                $response = ['status' => 1,
                            'iframe' => $launchURL];
            }
            else
            {
                $response = ['status' => 0,
                                'error' => 'INVALID_PARAMETER'];
            }

            return $response;

        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return $response = ['status' => 0,
                                'error' => 'INVALID_PARAMETER'];
        }
    }

    public static function encrypt($str,$key) 
    {
        return base64_encode(openssl_encrypt($str, 'DES-CBC', $key, OPENSSL_RAW_DATA, $key));
    }

    public static function decrypt($str,$key) 
    {
        $str = urldecode($str);

        $str = openssl_decrypt(base64_decode($str), 'DES-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $key);

        return rtrim($str, "\x1\x2\x3\x4\x5\x6\x7\x8");
    }
}