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

class AASController extends Controller
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

    public static function getGame($gameId,$type,$isMobile)
    {
        try
        {
            $locale = self::mapLocale();

            $gameList = Providers::mapPS9Game();
            $gameId = $gameList[$gameId];

            if($gameId == 9)
                $locale = 'en';

            $userId =  Auth::id();
            $userName =  Auth::user()->username;
            $balance = UserController::getBalance();
            $currency = env('CURRENCY');
            // $currency = UserController::getCurrency();

            $hostName = env('AAS_HOSTNAME');
            $token = env('AAS_TOKEN');
            $agent = env('AAS_AGENT');

            $method = 'auth';

            $hostname = 'http://ditto8.com';
            $domainUrl = $hostName . 'slots/'.$gameId.'/'.$type.'?isMobile=false';

            $url = $hostName.$method;

            if($isMobile == 1)
            {
                $isMobile = true;
            }
            else
            {
                $isMobile = false;
            }

            $header = array('Content-Type: application/json'
                            ,'Ag-Code: '.$agent
                            ,'Ag-Token:'.$token);
            
            $data = array(
                'user' => array(
                    'id' => $userId
                    ,'name' => $userName
                    ,'balance' => $balance
                    ,'language' => $locale
                    ,'currency' => $currency
                    ,'domain_url' => $domainUrl
                    )
                ,'prd' => array(
                    'id' => $gameId
                    ,'type' => $type
                    ,'is_mobile' => $isMobile
                    )
                );
           
            $response = Helper::postData($url,$data,$header);

            $response = json_decode($response);

            if($response->{'status'} == 0)
            {
                return '';
            }

            $iframe = $response->{'launch_url'};
            $aasId = $response->{'user_id'};

            self::updateAASUsers($aasId);

            return $iframe;
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return '';
        }
    }

    public static function getGameList()
    {
        try
        {
            $locale = self::mapLocale();
            $game = Providers::mapPS9Game();

            $hostName = env('AAS_HOSTNAME');
            $token = env('AAS_TOKEN');
            $agent = env('AAS_AGENT');

            $method = 'gamelist';


            // $domainUrl = $hostName . 'slots/'.$gameId.'/'.$type.'?isMobile=false';

            $url = $hostName.$method;


            $header = array('Content-Type: application/json'
                            ,'Ag-Code: '.$agent
                            ,'Ag-Token:'.$token);
            
            $data = array(
                'language' => $locale
                );
           
            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            if($response['status'] == 0)
            {
                return;
            }

            $gameList = $response['game_list'];

            $params = [];
            foreach ($gameList as $aasPrdId => $gl) 
            {
                foreach ($gl as $value) 
                {
                    $gameId = $value['game_id'];
                    $gameName = $value['game_name'];
                    $isEnabled = $value['is_enabled'];
                    
                    array_push($params, [$aasPrdId,$gameId,$gameName,$isEnabled]);
                }
            }

            $sql = "INSERT INTO aas_games (prd_id, id, name, is_enabled, created_at)
                    VALUES :(?,?,?,?,NOW()):
                    ON DUPLICATE KEY UPDATE
                        name = VALUES(name)
                        ,is_enabled = VALUES(is_enabled)
                        ,updated_at = NOW()";

            if (!empty($params)) 
            {
                foreach (array_chunk($params,1000) as $t)  
                {
                     // insert events
                    $pdo = Helper::prepareBulkInsert($sql,$t);

                    $db = DB::insert($pdo['sql'],$pdo['params']);
                }
            }
        }
        catch(\Exception $e)
        {
            log::Debug($e);
        }
    }

    public static function updateAASUsers($aasId)
    {
        DB::begintransaction();

        try
        {
            $userId =  Auth::id();

            DB::insert('
                INSERT INTO aas_users (member_id,aas_id)
                VALUES (?,?)
                ON duplicate key UPDATE
                    aas_id = ?'
                ,[  $userId
                    ,$aasId
                    ,$aasId]);

            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollback();
        }
    }

    public static function getEvoBetDetail(Request $request)
    {
        try
        {
            $hostName = env('AAS_HOSTNAME');
            $agentCode = env('AAS_AGENT');
            $secretKey = env('AAS_SECRET_KEY');
            $token = env('AAS_TOKEN');

            $prdId = $request->input('prd_id');

            $txnId = $request->input('txn_id');

            // $language = Lang::locale();

            $language = 'en';

            $url = $hostName.'betresults';

            $data = [
                        "lang" => $language,
                        "prdId" => $prdId,
                        "txnId" => $txnId
                    ];

            $header = [
                        'Content-Type: application/json',
                        'ag-code: '.$agentCode,
                        'token: '.$token,
                        'secret-key: '.$secretKey
                    ];

            $response = Helper::postData($url,$data,$header);

            return $response;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return false;
        }
    }

    public static function getSlotGameList($prdId)
    {
        try 
        {
            $mapPs9Prd = Providers::mapPS9Game();
            $mapPs9Pic = Providers::mapPS9PicFolder();
            $imageUrl = env('IMAGE_URL');

            $prodName = $mapPs9Pic[$prdId];
            $aasPrdId = $mapPs9Prd[$prdId];

            //ps9
            $db = DB::select("SELECT id as game_id, name as game_name
                            FROM aas_games
                            WHERE prd_id = ?
                            "
                            ,[$aasPrdId]);

            $gameListArr = [];

            foreach ($db as $d) 
            {
                $gameId = $d->game_id;

                $d->prd_id = $prdId;
                $d->img_url = $imageUrl.'/ps9/slots/'.$prodName.'/'.$gameId.'.png';
            }

            return $db;

        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return [];
        }
    }
}
