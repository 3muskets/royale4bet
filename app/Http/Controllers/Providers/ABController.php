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

    public static function createPlayer()
    {
        try 
        {
            $operatorCode = env('AB_OPERATOR_CODE');
            $secretKey = env('AB_SECRET_KEY');
            $apiUrl = env('AB_API_URL');
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

    public static function getGame($isMobile=0)
    {
        try 
        {
            $createPlayer = self::createPlayer();

            if ($createPlayer['success'] == 0) 
            {
                log::debug($createPlayer);
                return '';
            }

            $operatorCode = env('AB_OPERATOR_CODE');
            $providerCode = env('AB_PROVIDER_CODE');
            $gameType = 'LC';
            $secretKey = env('AB_SECRET_KEY');
            $apiUrl = env('AB_API_URL');
            $password = env('AB_PASSWORD');
            $method = '/launchGames.aspx';

            $username = strtolower(Auth::user()->username);

            $md5 = md5($operatorCode.$password.$providerCode.$gameType.$username.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&password='.$password.'&providercode='.$providerCode.'&username='.$username.'&type='.$gameType.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['errCode'] != 0) 
            {
                return ['status'=>0,'iframe'=>$gameUrl];
            }

             return ['status'=>1,'iframe'=>$response['gameUrl']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    } 

    public static function makeTransfer($amount)
    {
        try 
        {
            $createPlayer = self::createPlayer();

            $operatorCode = env('AB_OPERATOR_CODE');
            $providerCode = env('AB_PROVIDER_CODE');
            $secretKey = env('AB_SECRET_KEY');
            $gameId = Providers::AB;
            $apiUrl = env('AB_API_URL');
            $password = env('AB_PASSWORD');
            $method = '/makeTransfer.aspx';
            $type = ($amount>0)?'d':'w'; //for storing

            //hardcode//1 withdraw 0 deposit 
            $username = strtolower(Auth::user()->username);

            DB::insert("INSERT INTO ab_wallet_transfer(prod_game_id, type, amount, status, created_at)
                            VALUES(?,?,?,?,NOW())"
                            ,[$gameId, $type, $amount, 'n']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM ab_wallet_transfer");

            $referenceId = $db[0]->id;

            $type = ($amount>0)?0:1; //for call API

            $md5 = md5($amount.$operatorCode.$password.$providerCode.$referenceId.$type.$username.$secretKey);
            $signature = strtoupper($md5);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&providercode='.$providerCode.'&username='.$username.'&password='.$password.'&referenceid='.$referenceId.'&type='.$type.'&amount='.$amount.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['errCode'] != 0) 
            {
                DB::update("UPDATE ab_wallet_transfer
                            SET status = 'x'
                                , error_code = ?
                            WHERE id = ?"
                            ,[$response['errMsg'],$referenceId]);

                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            DB::update("UPDATE ab_wallet_transfer
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

    public static function getBetHistory()
    {
        try 
        {
            $operatorCode = env('AB_OPERATOR_CODE');
            $secretKey = env('AB_SECRET_KEY');
            $apiUrl = env('AB_LOG_URL');
            $method = '/fetchbykey.aspx';
            $key = 0;

            $md5 = md5($operatorCode.$secretKey);
            $signature = strtoupper($md5);

            $db = DB::select("SELECT version_key FROM ab_betsip_version WHERE id = 1");

            if (sizeof($db) != 0) 
            {
                $key = $db[0]->version_key;
            }

            DB::insert("INSERT INTO ab_betsip_version(id, version_key, created_at)
                        VALUES(?,?,NOW())
                        ON DUPLICATE KEY UPDATE
                             version_key = ?
                            , updated_at = NOW()"
                        ,[1,$key,$key]);

            $url = $apiUrl.$method.'?operatorcode='.$operatorCode.'&versionkey='.$key.'&signature='.$signature;

            $response = Helper::getData($url);
            $response = json_decode($response,true);

            if ($response['errCode'] != 0) 
            {
                return ['success' => 0, 'error_code' => $response['errMsg']];
            }

            DB::update("UPDATE ab_betsip_version
                        SET version_key = ?
                        WHERE id = 1"
                        ,[$response['lastversionkey']]);

            $mapProduct = self::mapProduct();

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
                $prdId = array_search($site, $mapProduct);

                if ($product == 'LC') 
                {
                    $category = 1;//casino
                }
                else if ($product == 'SB') 
                {
                    $category = 2;//sportbook
                }
                else
                {
                    $category = 3; //slot
                }

                $db = DB::select("SELECT id FROM member where username = ?",[$member]);

                $memberId = $db[0]->id;

                DB::insert("INSERT INTO ab_debit(txn_id, ref_no, prd_id, category, prd_cd, prd_type, member_id, game_id, start_time, match_time, end_time, bet_detail, turnover, bet, commission, p_share, p_win, status, created_at)
                        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
                        ON DUPLICATE KEY UPDATE
                             bet_detail = VALUES(bet_detail)
                             ,turnover = VALUES(turnover)
                             ,bet = VALUES(bet)
                             ,commission = VALUES(commission)
                             ,member_id = VALUES(member_id)
                             ,p_share = VALUES(p_share)
                             ,p_win = VALUES(p_win)
                             ,status = VALUES(status)
                             ,prd_id = VALUES(prd_id)
                             ,category = VALUES(category)
                            , updated_at = NOW()"
                        ,[$id,                    
                            $refNo,
                            $prdId,
                            $category,
                            $site,
                            $product,
                            $memberId,
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

                //calculate PT and COMM
                $db = DB::select('
                    SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                    FROM member a
                    INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                    INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                    WHERE a.id = ?
                        AND b.prd_id = ?'
                    ,[$memberId,$prdId]);

                $tier1PT = $db[0]->tier1_pt;
                $tier2PT = $db[0]->tier2_pt;
                $tier3PT = $db[0]->tier3_pt;
                $tier4PT = $db[0]->tier4_pt;
                $tier4Comm = $db[0]->comm;
                $debitAmt = $bet;
                $amount = $payout;

                $wlAmt = $debitAmt - $amount;

                $tier4PTAmt = $wlAmt * ($tier4PT / 100);
                Helper::removePrecision($tier4PTAmt);

                $tier3PTAmt = $wlAmt * ($tier3PT / 100);
                Helper::removePrecision($tier3PTAmt);

                $tier2PTAmt = $wlAmt * ($tier2PT / 100);
                Helper::removePrecision($tier2PTAmt);

                $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

                $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

                $type = 'c';

                //insert transaction
                $db = DB::insert('
                        INSERT INTO ab_credit
                        (prd_id,txn_id,type,amount
                        ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                        ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                        ,tier4_comm,tier4_comm_amt
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,?,?,?,?
                        ,?,?,?,?
                        ,?,?
                        ,NOW())
                        ON DUPLICATE KEY UPDATE
                        type = VALUES(type)'
                        ,[  $prdId,$id,$type,$payout
                            ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                            ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt
                            ,$tier4Comm,$tier4CommAmt]);
            }

            return ['success' => 1, 'error_code' => $response['errMsg']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }
}
