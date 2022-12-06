<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Helper;
use App\Http\Controllers\Providers;

use Auth;
use App;
use Log;
use Lang;

class SCController extends Controller
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

    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en'
                    ,'zh-cn'  => 'zh-Hans'
                    ,'ar'  => 'en'
                );

        return $locale[App::getLocale()];
    }

    public static function createPlayer()
    {
        try 
        {
            $operatorCode = env('SC_OPERATOR_CODE');
            $secretKey = env('SC_SECRET_KEY');
            $apiUrl = env('SC_API_URL');
            $method = '/createMember.aspx';

            $username = strtolower(Auth::user()->username);

            $md5 = md5($operatorCode.$username.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&username='.$username.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            log::debug($response);

            if ($response['errCode'] != 0 && $response['errMsg'] != "MEMBER_EXISTED") 
            {
                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            return ['success' => 1, 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    //undone
    public static function getGame()
    {
        try 
        {
            $createPlayer = self::createPlayer();

            if ($createPlayer['success'] == 0) 
            {
                log::debug($createPlayer);
                return '';
            }

            $downloadURL = env('SC_DL_URL');

            return $downloadURL;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    // public static function getGame()
    // {
    //     try 
    //     {
    //         $url = env('SC_API_URL').'aio/seamless.verialma/launchgame/getGameUrl';
    //         $opCode = env('SC_OPERATOR_CODE');
    //         $prdCode = env('SC_PROVIDER_CODE');
    //         $type = 'LC';

    //         $username = Auth::user()->username;
    //         $userId = Auth::id();
    //         $password = env('SC_PASSWORD');
    //         $key = env('SC_SECRET_KEY');

    //         $signature = md5($opCode.$userId.$password.$prdCode.$type.$username.$key);

    //         $data = [
    //             'opcode'=>$opCode,
    //             'product'=>$prdCode,
    //             'type'=>$type,
    //             'username'=>$username,
    //             'memberId'=>$userId,
    //             'password'=>$password,
    //             'signature'=>$signature
    //         ];

    //         log::debug($url);
    //         log::debug($data);

    //         $response = Helper::postData($url,$data);

    //         log::debug($response);
    //         $response = json_decode($response);

    //         if ($response->{'error_code'} == 6 ||$response->{'error_code'} == 0) 
    //         {
    //             return true;
    //         }

    //         return false;
    //     } 
    //     catch (Exception $e) 
    //     {
    //         log::debug($e);
    //         return false;
    //     }
    // } 
}
