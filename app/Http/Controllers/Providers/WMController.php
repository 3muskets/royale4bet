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

class WMController extends Controller
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
                    'en'  => 'en',
                    'zh-cn'  => 'zh',
                    'ar'  => 'en'
                );

        return $locale[Lang::locale()];
    }

    public static function getGameList()
    {
        try
        {
            $gameServer = env('WM_ICON_SERVERNAME');
            $iconURL = $gameServer.'/resources/Thumbs';

            $db = DB::select('
                SELECT game_id,config_id,game_name,game_name_cn,game_pic_path
                FROM wm_games
                ');

            if (Lang::locale() == 'zh-cn') 
            {
                foreach($db as $d)
                {
                    $d->game_name = $d->game_name_cn;
                }
            }

            foreach($db as $d)
            {
                $d->icon_url = $d->game_pic_path;

            }

            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function isAllowGame($type)
    {
        $db = DB::select('
                SELECT game_id, config_id
                FROM wm_games
                WHERE game_id = ?
                LIMIT 1'
                ,[$type]);

        return $db;
    }

    public static function getGame($type,$isMobile)
    {
        try
        {
            //sanitize type
            $db = self::isAllowGame($type);

            if(sizeOf($db) == 0)
                return '';

            $configId = $db[0]->config_id;
            $userId = Auth::id();
            $token = Helper::generateUniqueId();

            //save token
            self::updateToken($token);

            //prepare variable
            $gameServer = env('WM_SERVERNAME');
            $licenseeId = env('WM_LICENSEEID');
            $authSkin = env('WM_AUTHSKIN');

            $language = self::mapLocale();
            $ageFlag = false;// enables the age alert on startup
            $toggle = true;//that permits to switch on/off orientation hints on mobile devices.
            $display = 'iframe';

            $lobbyUrl = urlencode(route('/')).'/lobby?gameId=4';
            
            $iframe = $gameServer.'/games/real/'.$licenseeId.'/'.$type.'/'.$configId.'/?authuser='.$userId.'&authkey='.$token.'&authskin='.$authSkin.'&language='.$language.'&age='.$ageFlag.'&toggle='.$toggle.'&display='.$display.'&lobbyurl='.$lobbyUrl;

            return $iframe;
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return '';
        }
    }

    public static function getWmBetDetail(Request $request)
    {
        try
        {
            $hostName = env('WM_SERVERNAME');
            $licensesId = env('WM_LICENSEEID');
            $authKey = env('WM_LICENSEE_TOKEN');
            $memberId = $request->input('member_id');
            //game bet id 
            $roundId = $request->input('round_id');
            $gameId = $request->input('game_id');
            $configId = $request->input('config_id');
            $language = Lang::locale();

            if($language == 'zh-cn')
            {
                $language = 'zh';
            }

            $method = '/games/history/'.$licensesId.'/'.$gameId.'/'.$configId.'/';

            $db = DB::SELECT('SELECT a.member_token
                        FROM wm_users a
                        WHERE member_id = ?'
                        ,[$memberId]
                    );

            $authUser = $db[0]->member_token;


            $url = $hostName.$method.'?language='.$language.'&gamebetid='.$roundId.'&authuser='.$authUser.'&authkey='.$authKey.'&display=inline';


            return $url;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
        }
    }

    public static function updateToken($token)
    {
        DB::begintransaction();

        try
        {
            $userId =  Auth::id();

            DB::insert('
                INSERT INTO wm_users(member_id,token)
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
