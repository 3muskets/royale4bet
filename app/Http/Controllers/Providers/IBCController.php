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

class IBCController extends Controller
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

    public static function createMember()
    {
        try 
        {
            $hostname = env('IBC_API_URL');
            $url = $hostname.'/CreateMember';

            $data = [
                'vendor_id'=>env('IBC_VENDOR_ID'),
                'vendor_member_id'=>env('IBC_OPERATOR_ID').'_'.Auth::id().'_test',
                'operatorId'=>env('IBC_OPERATOR_ID'),
                'username'=>env('IBC_OPERATOR_ID').'_'.Auth::id().'_test',
                'oddstype'=>env('IBC_ODDS_TYPE'),
                'currency'=>20
            ];

            $response = self::postData($url,$data);
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

    public static function getGame()
    {
        try
        {
            $locale = self::mapLocale();

            if (!self::createMember()) 
            {
                return ['status' => 0];
            }
            // if($isMobile == 1)
            // {
            //     $isMobile = true;
            // }
            // else
            // {
            //     $isMobile = false;
            // }

            $hostname = env('IBC_API_URL');
            $url = $hostname.'/GetSabaUrl';

            $vendorId = env('IBC_VENDOR_ID');
            $vendorMemberId = env('IBC_OPERATOR_ID').'_'.Auth::id().'_test';

            $isMobile = 1;

            $data = [
                'vendor_id'=>$vendorId,
                'vendor_member_id'=>$vendorMemberId,
                'platform'=>1, //1: 桌机  // 2: 手机 h5 // 3: 手机纯文字
            ];

            $response = self::postData($url,$data);
            $response = json_decode($response);

            if ($response->{'error_code'} == 0) 
            {
                $response = ['status' => 1,
                                'iframe' => $response->{'Data'}
                            ];
                return $response;
            }
            else
            {
                return ['status' => 0];
            }
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return ['status' => 0];
        }
    }

    public static function postData($url,$data)
    {
        try 
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => array(
                'Cookie: TbtNpCD33ifIBgVKlQO8iUu8ecU0+Ip0KImc7w__=v1RqIqgw__7re'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }

    // public static function getGame($gameId,$type,$isMobile)


    // public static function getGameList()
    // {
    //     try
    //     {
    //         $locale = self::mapLocale();
    //         $game = Providers::mapPS9Game();

    //         $hostName = env('AAS_HOSTNAME');
    //         $token = env('AAS_TOKEN');
    //         $agent = env('AAS_AGENT');

    //         $method = 'gamelist';


    //         // $domainUrl = $hostName . 'slots/'.$gameId.'/'.$type.'?isMobile=false';

    //         $url = $hostName.$method;


    //         $header = array('Content-Type: application/json'
    //                         ,'Ag-Code: '.$agent
    //                         ,'Ag-Token:'.$token);
            
    //         $data = array(
    //             'language' => $locale
    //             );
           
    //         $response = Helper::postData($url,$data,$header);
    //         $response = json_decode($response,true);

    //         if($response['status'] == 0)
    //         {
    //             return;
    //         }

    //         $gameList = $response['game_list'];

    //         $params = [];
    //         foreach ($gameList as $aasPrdId => $gl) 
    //         {
    //             foreach ($gl as $value) 
    //             {
    //                 $gameId = $value['game_id'];
    //                 $gameName = $value['game_name'];
    //                 $isEnabled = $value['is_enabled'];
                    
    //                 array_push($params, [$aasPrdId,$gameId,$gameName,$isEnabled]);
    //             }
    //         }

    //         $sql = "INSERT INTO aas_games (prd_id, id, name, is_enabled, created_at)
    //                 VALUES :(?,?,?,?,NOW()):
    //                 ON DUPLICATE KEY UPDATE
    //                     name = VALUES(name)
    //                     ,is_enabled = VALUES(is_enabled)
    //                     ,updated_at = NOW()";

    //         if (!empty($params)) 
    //         {
    //             foreach (array_chunk($params,1000) as $t)  
    //             {
    //                  // insert events
    //                 $pdo = Helper::prepareBulkInsert($sql,$t);

    //                 $db = DB::insert($pdo['sql'],$pdo['params']);
    //             }
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         log::Debug($e);
    //     }
    // }

    // public static function updateAASUsers($aasId)
    // {
    //     DB::begintransaction();

    //     try
    //     {
    //         $userId =  Auth::id();

    //         DB::insert('
    //             INSERT INTO aas_users (member_id,aas_id)
    //             VALUES (?,?)
    //             ON duplicate key UPDATE
    //                 aas_id = ?'
    //             ,[  $userId
    //                 ,$aasId
    //                 ,$aasId]);

    //         DB::commit();
    //     }
    //     catch(\Exception $e)
    //     {
    //         DB::rollback();
    //     }
    // }

    // public static function getEvoBetDetail(Request $request)
    // {
    //     try
    //     {
    //         $hostName = env('AAS_HOSTNAME');
    //         $agentCode = env('AAS_AGENT');
    //         $secretKey = env('AAS_SECRET_KEY');
    //         $token = env('AAS_TOKEN');

    //         $prdId = $request->input('prd_id');

    //         $txnId = $request->input('txn_id');

    //         // $language = Lang::locale();

    //         $language = 'en';

    //         $url = $hostName.'betresults';

    //         $data = [
    //                     "lang" => $language,
    //                     "prdId" => $prdId,
    //                     "txnId" => $txnId
    //                 ];

    //         $header = [
    //                     'Content-Type: application/json',
    //                     'ag-code: '.$agentCode,
    //                     'token: '.$token,
    //                     'secret-key: '.$secretKey
    //                 ];

    //         $response = Helper::postData($url,$data,$header);

    //         return $response;

    //     }
    //     catch(\Exception $e)
    //     {
    //         Log::Debug($e);

    //         return false;
    //     }
    // }

    // public static function getSlotGameList($prdId)
    // {
    //     try 
    //     {
    //         $mapPs9Prd = Providers::mapPS9Game();
    //         $mapPs9Pic = Providers::mapPS9PicFolder();
    //         $imageUrl = env('IMAGE_URL');

    //         $prodName = $mapPs9Pic[$prdId];
    //         $aasPrdId = $mapPs9Prd[$prdId];

    //         //ps9
    //         $db = DB::select("SELECT id as game_id, name as game_name
    //                         FROM aas_games
    //                         WHERE prd_id = ?
    //                         "
    //                         ,[$aasPrdId]);

    //         $gameListArr = [];

    //         foreach ($db as $d) 
    //         {
    //             $gameId = $d->game_id;

    //             $d->prd_id = $prdId;
    //             $d->img_url = $imageUrl.'/ps9/slots/'.$prodName.'/'.$gameId.'.png';
    //         }

    //         return $db;

    //     } 
    //     catch (Exception $e) 
    //     {
    //         log::debug($e);
    //         return [];
    //     }
    // }
}
