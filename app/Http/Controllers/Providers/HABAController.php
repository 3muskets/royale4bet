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

class HABAController extends Controller
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
            $gameServer = env('HABA_DOMAINNAME');
            $iconURL = $gameServer.'/img/square/188/';

            
            $gameList = DB::select("SELECT game_id
                                        ,game_id as game_pic_id
                                        ,game_name
                                        ,game_name_cn
                                        ,theme
                                        ,game_type
                                      FROM haba_games
                                      ORDER BY game_id ASC");

            //remove GambleBeatDealer game
            foreach ($gameList as  $k => $val) 
            {
                if ($val->game_id == 'GambleBeatDealer') 
                {
                    unset($gameList[$k]); 
                }
            }

            //chinese version game
            if (Lang::locale() == 'zh-cn') 
            {
                foreach ($gameList as $g) 
                {
                    $g->game_pic_url = $iconURL.$g->game_pic_id."_zh-CN";
                    $g->game_name = $g->game_name_cn;
                }
            }
            else
            {
                foreach($gameList as $g)
                {
                    $g->game_pic_url = $iconURL.$g->game_pic_id;
                }
            }

            return $gameList;
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return 'Error loading game menu!';
        }
    }

    public static function getGame($type)
    {
        try
        {
            $keyName = $type;
            $hostname = env('HABA_DOMAINNAME');
            $brandId = env('HABA_BRAND_ID');
            $locale = Lang::locale();
            $token = Helper::generateUniqueId();

            //check status
            $status = Auth::user()->status;
            
            if ($status != 'a') 
            {
                return 'Inactive Member!';
            }

            //save token
            self::updateToken($token);

            $gameUrl = $hostname."/go.ashx?brandid=".$brandId."&keyname=".$keyName."&token=".$token."&mode=real&locale=".$locale."&lobbyurl=".urlencode(route('/'))."/lobby?gameId=2&ifrm=1";

            return $gameUrl;
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return '';
        }
    }

    public static function getHabaBetDetail(Request $request)
    {
        try
        {
            $hostName = env('HABA_DOMAINNAME');
            $brandId = env('HABA_BRAND_ID');
            $apiKey = env('HABA_API_KEY');

            $method = '/games/history';

            $language = Lang::locale();

            $gameId = $request->input('round_id');

            $apiKey = strtolower($apiKey);

            $hash = hash('sha256',$gameId.$brandId.$apiKey);

            $url = $hostName.$method;

            $url = $url.'?brandId='.$brandId.'&gameinstanceid='.$gameId.'&locale='.$language.'&hash='.$hash;

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
                INSERT INTO haba_users(member_id,token)
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
