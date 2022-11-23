<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\UserController;
use App\Http\Controllers\Helper;

use Auth;
use App;
use Log;
use Lang;

class PPController extends Controller
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

    public static function getGameList()
    {
        try
        {
            $db = DB::select('
                SELECT id,game_name 
                FROM pp_games');

            $gameServer = env('PP_GAMESERVER');
            $iconURL = $gameServer.'game_pic/rec/188/';
            
            foreach($db as $d)
            {
                $d->icon_url = $iconURL.$d->id.'.png';
            }
            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en',
                    'zh-cn'  => 'zh'
                );

        return $locale[App::getLocale()];
    }

    public static function isAllowGame($type)
    {
        $db = DB::select('
                SELECT id
                FROM pp_games
                WHERE id = ?
                LIMIT 1'
                ,[$type]);

        if(sizeof($db) == 0) 
            return false;

        return true;
    }

    public static function getGame($type,$isMobile)
    {
        try
        {
            //sanitize type
            if(!self::isAllowGame($type))
                return '';

            //prepare variable
            $gameServer = env('PP_GAMESERVER');

            $token = Helper::generateUniqueId();
            $symbol = $type;
            $technology = 'F';

            $platform = 'WEB';

            if($isMobile)
                $technology = 'H5';
                $platform = 'MOBILE';

            $language = self::mapLocale();

            $cashierUrl = '';
            $lobbyUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]"."/lobby?gameId=3";

            $secureLogin = env('PP_SECURELOGIN');

            //save token
            self::updateToken($token);
            
            //prepare launch game url
            $key = 'token='.$token;
            $key .= '&symbol='.$symbol;
            $key .= '&technology='.$technology;
            $key .= '&platform='.$platform;
            $key .= '&language='.$language;
            $key .= '&cashierUrl='.$cashierUrl;
            $key .= '&lobbyUrl='.$lobbyUrl;

            $key = urlencode($key);
            
            $iframe = $gameServer.'gs2c/playGame.do?';
            $iframe .= 'key='.$key;
            $iframe .= '&stylename='.$secureLogin;

            return $iframe;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function getPPBetDetail(Request $request)
    {
        try
        {
            $hostName = env('PP_HOSTNAME');
            $secureLogin = env('PP_SECURELOGIN');
            $hashKey = env('PP_HASHKEY');

            $method = 'HistoryAPI/OpenHistoryExtended/';

            $memberId = $request->input('member_id');
            $roundId = $request->input('round_id');
            $language = Lang::locale();

            if($language == 'zh-cn')
            {
                $language = 'zh';
            }
    
            $db = DB::SELECT('SELECT username
                        FROM member
                        WHERE id = ?',[$memberId]
                    );

            if(sizeof($db) == 0)
            {
                return '';
            }
            else
            {
                $playerId = $db[0]->username;
            }
    
            $hash = md5('language='.$language.'&playerId='.$playerId.'&roundId='.$roundId.'&secureLogin='.$secureLogin.$hashKey);
            
            $method .= '?secureLogin='.$secureLogin.'&playerId='.$playerId.'&roundId='.$roundId.'&language='.$language.'&hash='.$hash;

            $url = $hostName.$method;

            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $response = Helper::postData($url,'',$header);

            $response = json_decode($response);

            $iframe = $response->{'url'}; 

            return $iframe;
        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return '';
        }
    }

    public static function updateToken($token)
    {
        DB::begintransaction();

        try
        {
            $userId =  Auth::id();

            DB::insert('
                INSERT INTO pp_users(member_id,token)
                VALUES (?,?)
                ON duplicate key UPDATE
                    token = ?'
                ,[  $userId
                    ,$token
                    ,$token]);

            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollback();
        }
    }
}
