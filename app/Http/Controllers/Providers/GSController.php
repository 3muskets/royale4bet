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

class GSController extends Controller
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

    public static function mapProduct()
    {
        $product = array(
                    Providers::Gameplay  => env('GS_PROVIDER_GAMEPLAY_CODE')
                    ,Providers::BBIN  => env('GS_PROVIDER_BBIN_CODE')
                    ,Providers::IBC  => env('GS_PROVIDER_IBC_CODE')
                    ,Providers::ALLBET  => env('GS_PROVIDER_ALLBET_CODE')
                    ,Providers::CQ9  => env('GS_PROVIDER_CQ9_CODE')
                    ,Providers::WM  => env('GS_PROVIDER_WM_CODE')
                    ,Providers::Joker  => env('GS_PROVIDER_JOKER_CODE')
                    ,Providers::PSB4D  => env('GS_PROVIDER_PSB4D_CODE')
                    ,Providers::Spade  => env('GS_PROVIDER_SPADE_CODE')
                    ,Providers::QQKeno  => env('GS_PROVIDER_QQKENO_CODE')
                    ,Providers::CMD  => env('GS_PROVIDER_CMD_CODE')
                    ,Providers::M8BET  => env('GS_PROVIDER_M8BET_CODE')
                    ,Providers::DIGMAAN  => env('GS_PROVIDER_DIGMAAN_CODE')
                    ,Providers::EBET  => env('GS_PROVIDER_EBET_CODE')
                    ,Providers::IA  => env('GS_PROVIDER_IA_CODE')
                    ,Providers::NLIVE22  => env('GS_PROVIDER_NLIVE22_CODE')
                );

        return $product;
    }

    public static function mapGameType($type)
    {
        $product = array(
                    1  => 'LC' //LIVE-CASINO 真人视讯游戏
                    ,2  => 'SL' //SLOTS 老虎机游戏
                    ,3 => 'SB' //SPORTBOOK 体育游戏
                    ,4  => 'FH' //FISH HUNTER 捕鱼游戏
                    ,5  => 'LK' //LOTTO 彩票游戏
                    ,6  => 'ES' //E-GAMES 电子游戏
                    ,7  => 'PK' //POKER 扑克游戏
                    ,8  => 'MG' //MINI GAME 迷你游戏
                    ,9  => 'OT' //OTHERS 其他游戏
                    ,10  => 'CB' //CARD & BOARDGAME 棋牌游戏
                );

        return $product[$type];
    }

    public static function createPlayer()
    {
        try 
        {
            $operatorCode = env('GS_OPERATOR_CODE');
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $method = '/createMember.aspx';

            $username = strtolower(Auth::user()->username);

            $md5 = md5($operatorCode.$username.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&username='.$username.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

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

    public static function getBalance($gameId=null)
    {
        try 
        {
            $createPlayer = self::createPlayer();

            $operatorCode = env('GS_OPERATOR_CODE');
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $password = env('GS_MEMBER_PASSWORD');
            $method = '/getBalance.aspx';
            $username = strtolower(Auth::user()->username);
            $urlArray = [];
            $responseArr = [];
            $providerCode = self::mapProduct();

            if ($gameId==null) 
            {
                foreach ($providerCode as $pKey => $p) 
                {
                    $md5 = md5($operatorCode.$password.$p.$username.$secretKey);
                    $signature = strtoupper($md5);

                    $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&providercode='.$p.'&username='.$username.'&password='.$password.'&signature='.$signature;

                    $urlArray[$pKey] = $url;
                }
            }
            else
            {
                $providerCode = $providerCode[$gameId];

                $md5 = md5($operatorCode.$password.$providerCode.$username.$secretKey);
                    $signature = strtoupper($md5);

                $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&providercode='.$providerCode.'&username='.$username.'&password='.$password.'&signature='.$signature;

                $urlArray[$gameId] = $url;
            }

            foreach ($urlArray as $key => $url) 
            {
                $response = Helper::getData($url);
                $response = json_decode($response,true);

                if ($response['errCode'] != 0) 
                {
                    $responseArr[$key] = null; 
                }
                else
                {
                    $responseArr[$key] = $response['balance']; 
                }
            }

            return $responseArr;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public static function getGameList($gameId=null)
    {
        try 
        {
            $operatorCode = env('GS_OPERATOR_CODE');
            $providerCode = self::mapProduct();
            $providerCode = ($gameId!=null)?$providerCode[$gameId]:$providerCode;
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $password = env('GS_MEMBER_PASSWORD');
            $method = '/getGameList.aspx';

            //unnecessary
            $lang; 
            $html5; 

            foreach ($providerCode as $key => $p) 
            {
                $md5 = md5($operatorCode.$p.$secretKey);
                $signature = strtoupper($md5);

                $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&providercode='.$p.'&signature='.$signature;

                $response = Helper::getData($url);
                $response = json_decode($response,true);

                if ($response['errCode'] != 0) 
                {
                    log::debug('Get game list error: '.$p);
                   continue;
                }

                foreach (json_decode($response['gamelist'],true) as $g) 
                {
                    if ($key == Providers::Gameplay) //Gameplay
                    {
                        $gameName = $g['Game'];
                        $gameNameCn = $g['Chinese Name'];
                        $type = $g['Type'];
                        $gameIdGL = $g['gameid (Game Loader)'];
                        $gameIdH = $g['gameid (History API return)'];
                        $mobileHTML5 = ($g['Mobile (HTML5)']=='yes')?1:0;


                        DB::insert("INSERT INTO gs_games_list (id, prod_id, category, name_en, name_cn, mobile_html5, created_at)
                                VALUES (?,?,?,?,?,?,NOW())"
                                ,[$gameIdGL, $key, $type, $gameName, $gameNameCn, $mobileHTML5]);
                    } 
                    else if ($key == Providers::BBIN) //BBIN //hv different language (taiwan/jap/cn/en/kor/viet/thai) 
                    {
                        $gameName = $g['gn_en'];
                        $gameNameCn = $g['gn_cn'];
                        $gameCategory = $g['GameCategory'];
                        $freegame = $g['freegame'];
                        $gameId = $g['GameID'];

                        DB::insert("INSERT INTO gs_games_list (id, prod_id, category, name_en, name_cn, freegame, created_at)
                                VALUES (?,?,?,?,?,?,NOW())"
                                ,[$gameId, $key, $gameCategory, $gameName, $gameNameCn, $freegame]);
                    }
                }
            }

            

            return ['success' => 1, 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    //undone
    public static function makeTransfer($gameId,$amount)
    {
        try 
        {
            $createPlayer = self::createPlayer();

            $operatorCode = env('GS_OPERATOR_CODE');
            $providerCode = self::mapProduct();
            $providerCode = $providerCode[$gameId];
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $password = env('GS_MEMBER_PASSWORD');
            $method = '/makeTransfer.aspx';
            $type = ($amount>0)?'d':'w'; //for storing

            //hardcode//1 withdraw 0 deposit 
            $username = strtolower(Auth::user()->username);

            DB::insert("INSERT INTO gs_wallet_transfer(prod_game_id, type, amount, status, created_at)
                            VALUES(?,?,?,?,NOW())"
                            ,[$gameId, $type, $amount, 'n']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM gs_wallet_transfer");

            $referenceId = $db[0]->id;

            $type = ($amount>0)?0:1; //for call API

            $md5 = md5($amount.$operatorCode.$password.$providerCode.$referenceId.$type.$username.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&providercode='.$providerCode.'&username='.$username.'&password='.$password.'&referenceid='.$referenceId.'&type='.$type.'&amount='.$amount.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['errCode'] != 0) 
            {
                DB::update("UPDATE gs_wallet_transfer
                            SET status = 'x'
                                AND error_code = ?
                            WHERE id = ?"
                            ,[$referenceId,$response['errMsg']]);

                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            DB::update("UPDATE gs_wallet_transfer
                            SET status = 'a'
                            WHERE id = ?"
                            ,[$referenceId]);

            return ['success' => 1, 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => __('error.dw.internal_error')];
        }
    }

    //undone
    public static function launchGames($gameId,$type,$isMobile)
    {
        try 
        {
            $createPlayer = self::createPlayer();

            if ($createPlayer['success'] == 0) 
            {
                log::debug($createPlayer);
                return '';
            }

            $operatorCode = env('GS_OPERATOR_CODE');
            $providerCode = self::mapProduct();
            $providerCode = $providerCode[$gameId];
            $gameType = self::mapGameType($type);
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $password = env('GS_MEMBER_PASSWORD');
            $method = '/launchGames.aspx';

            //hardcode 'type'
            // $gameType = 'LC';
            // $providerCode = 'G8';

            //unnecessary
            $gameId; 
            $lang; 
            $html5; 

            $username = strtolower(Auth::user()->username);

            $md5 = md5($operatorCode.$password.$providerCode.$gameType.$username.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&password='.$password.'&providercode='.$providerCode.'&username='.$username.'&type='.$gameType.'&signature='.$signature;

            log::debug($url);
            

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            log::debug($response);

            if ($response['errCode'] != 0) 
            {
                return $response['errMsg'];
            }

            return $response['gameUrl'];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public static function checkAgentCredit()
    {
        try 
        {
            $operatorCode = env('GS_OPERATOR_CODE');
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $method = '/checkAgentCredit.aspx';

            $md5 = md5($operatorCode.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['errCode'] != 0) 
            {
                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            return ['success' => 1, 'data' => $response['data'], 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    //beta (undone)
    public static function checkTransaction()
    {
        try 
        {
            $operatorCode = env('GS_OPERATOR_CODE');
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_API_URL');
            $method = '/checkTransaction.aspx';

            //hardcode
            $referenceId = 1;

            $md5 = md5($operatorCode.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&referenceid='.$referenceId.'&signature='.$signature;

            // return $url;

            $response = Helper::getData($url);
            $response = json_decode($response,true);
            return $response;
            if ($response['errCode'] != 0) 
            {
                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            return ['success' => 1, 'data' => $response['data'], 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public static function getBetHistory()
    {
        try 
        {
            $operatorCode = env('GS_OPERATOR_CODE');
            $secretKey = env('GS_SECRET_KEY');
            $apiUrl = env('GS_LOG_URL');
            $method = '/fetchbykey.aspx';
            $key = 0;

            $md5 = md5($operatorCode.$secretKey);
            $signature = strtoupper($md5);

            // $db = DB::select("SELECT version_key FROM gs_betsip_version WHERE id = 1");

            // if (sizeof($db) != 0) 
            // {
            //     $key = $db[0]->version_key;
            // }

            DB::insert("INSERT INTO gs_betsip_version(id, version_key, created_at)
                        VALUES(?,?,NOW())
                        ON DUPLICATE KEY UPDATE
                             version_key = ?
                            , updated_at = NOW()"
                        ,[1,$key,$key]);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&versionkey='.$key.'&signature='.$signature;

            return $url;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            log::debug($response);
            if ($response['errCode'] != 0) 
            {
                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            DB::update("UPDATE gs_betsip_version
                        SET version_key = ?
                        WHERE id = 1"
                        ,[$response['lastversionkey']]);

            foreach (json_decode($response['result'],true) as $g) 
            {
                $id = $g['id'];
                $refNo = $g['ref_no'];
                $site = $g['site'];
                $product = $g['product'];
                $member = $g['member'];
                $gameId = $g['game_id'];
                $startTime = $g['start_time'];
                $matchTime = $g['match_time'];
                $endTime = $g['end_time'];
                $betDetail = $g['bet_detail'];
                $turnover = $g['turnover'];
                $bet = $g['bet'];
                $payout = $g['payout'];
                $commission = $g['commission'];
                $pShare = $g['p_share'];
                $pWin = $g['p_win'];
                $status = $g['status'];

                DB::insert("INSERT INTO gs_debit(id, ref_no, prd_cd, prd_type, member, game_id, start_time, match_time, end_time, bet_detail, turnover, bet, commission, p_share, p_win, status, created_at)
                        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
                        ON DUPLICATE KEY UPDATE
                             bet_detail = VALUES(bet_detail)
                             ,turnover = VALUES(turnover)
                             ,bet = VALUES(bet)
                             ,commission = VALUES(commission)
                             ,p_share = VALUES(p_share)
                             ,p_win = VALUES(p_win)
                             ,status = VALUES(status)
                            , updated_at = NOW()"
                        ,[$id,                    
                            $refNo,
                            $site,
                            $product,
                            $member,
                            $gameId,
                            $startTime,
                            $matchTime,
                            $endTime,
                            $betDetail,
                            $turnover,
                            $bet,
                            $commission,
                            $pShare,
                            $pWin,
                            $status]);

                DB::insert("INSERT INTO gs_credit(id, payout, created_at)
                        VALUES(?,?,NOW())
                        ON DUPLICATE KEY UPDATE
                             payout = VALUES(payout)
                            , updated_at = NOW()"
                        ,[$id,                    
                            $payout]);
            }

            return ['success' => 1, 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    // public static function getSlotGameList($gameId)
    // {
    //     try 
    //     {
    //         $mapPs9Prd = Providers::mapPS9Game();
    //         $mapPs9Pic = Providers::mapPS9PicFolder();
    //         $imageUrl = env('IMAGE_URL');

    //         $prdId = $mapPs9Prd[$gameId];
    //         $prodName = $mapPs9Pic[$gameId];

    //         //ps9
    //         $db = DB::select("SELECT prd_id, id as game_id, name as game_name
    //                         FROM aas_games
    //                         WHERE prd_id = ?
    //                         "
    //                         ,[$prdId]);

    //         $gameListArr = [];

    //         foreach ($db as $d) 
    //         {
    //             $gameId = $d->game_id;

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
