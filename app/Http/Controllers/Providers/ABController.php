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

class ABController extends Controller
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

    public static function getGame()
    {
        try 
        {
            $url = env('AB_API_URL').'https://ams.allbetgaming.net/aio/seamless.verialma/launchgame/getGameUrl';
            $opCode = env('AB_OPERATOR_CODE');
            $prdCode = env('AB_PROVIDER_CODE');
            $type = 'LC';

            $username = Auth::user()->username;
            $userId = Auth::id();
            $password = env('AB_PASSWORD');
            $key = env('AB_SECRET_KEY');

            $signature = md5($opCode.$userId.$password.$prdCode.$type.$username.$key);

            $data = [
                'opcode'=>$opCode,
                'product'=>$prdCode,
                'type'=>$type,
                'username'=>$username,
                'memberId'=>$userId,
                'password'=>$password,
                'signature'=>$signature
            ];

            log::debug($url);
            log::debug($data);

            $response = Helper::postData($url,$data);

            log::debug($response);
            $response = json_decode($response);

            if ($response->{'error_code'} == 6 ||$response->{'error_code'} == 0) 
            {
                return true;
            }

            return false;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return false;
        }
    } 
}
