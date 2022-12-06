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

//transfer
class MEGAController extends Controller
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
                    'en'  => 'en-US'
                    ,'zh-cn'  => 'zt-CN'
                );

        return $locale[App::getLocale()];
    }

    public static function createMember()
    {
        try 
        {
            $db = DB::select("SELECT member_id FROM mega_users WHERE member_id = ?",[Auth::id()]);

            if (sizeof($db) != 0) 
            {
                return ['success' => 1, 'error_code' => null];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $digest = md5($random.$sn.$secretCode);
            $method = 'open.mega.user.create';
            $id = $random;

            //username
            $username = Auth::user()->username;

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"nickname" => $username
                        ,"agentLoginId" => $agentLoginId
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            $loginId = $response['result']['loginId'];

            self::updateMegaUsers($loginId);

            return ['success' => 1, 'error_code' => $response['error']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    public static function getBalance()
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.balance.get';
            $id = $random;

            $db = DB::select("SELECT login_id FROM mega_users WHERE member_id = ?",[Auth::id()]);

            $loginId = $db[0]->login_id;

            $digest = md5($random.$sn.$loginId.$secretCode);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"loginId" => $loginId
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            return ['success' => 1, 'error_code' => $response['error'], 'balance' => $response['result']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    //hard code
    public static function balanceTransfer($amount)
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');
            $type = ($amount>0)?'d':'w';

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.balance.transfer';
            $id = $random;
            $memberId = Auth::id();

            $db = DB::select("SELECT login_id FROM mega_users WHERE member_id = ?",[$memberId]);

            $loginId = $db[0]->login_id;

            $digest = md5($random.$sn.$loginId.$amount.$secretCode);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"loginId" => $loginId
                        ,"amount" => $amount
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            $balanceBef = DB::select("SELECT available 
                        FROM member_credit
                        WHERE member_id = ?
                        FOR UPDATE"
                        ,[$memberId]);

            $balanceBef = $balanceBef[0]->available;

            DB::insert("INSERT INTO mega_wallet_transfer(type, amount, status, created_at)
                            VALUES(?,?,?,NOW())"
                            ,[$type, $amount, 'n']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM mega_wallet_transfer");

            $referenceId = $db[0]->id;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE mega_wallet_transfer
                            SET status = 'x'
                                AND error_code = ?
                            WHERE id = ?"
                            ,[$referenceId,$response['errMsg']]);

                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            $megaId = $response['id'];
            
            DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        ,available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

            DB::update("UPDATE mega_wallet_transfer
                            SET status = 'a'
                            ,mega_id = ?
                            WHERE id = ?"
                            ,[$megaId,$referenceId]);

            return ['success' => 1, 'error_code' => $response['error'], 'balance' => $response['result']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => __('error.dw.internal_error')];
        }
    }

    public static function updateMegaUsers($loginId)
    {
        DB::begintransaction();

        try
        {
            $userId =  Auth::id();

            DB::insert('
                INSERT INTO mega_users (member_id,login_id)
                VALUES (?,?)
                ON duplicate key UPDATE
                    login_id = ?'
                ,[  $userId
                    ,$loginId
                    ,$loginId]);

            DB::commit();
        }
        catch(\Exception $e)
        {
            log::debug($e);
            DB::rollback();
        }
    }

    //open game
    public static function launchGames()
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.app.url.download';
            $id = $random;

            $digest = md5($random.$sn.$secretCode);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"agentLoginId" => $agentLoginId
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['status' => 0, 'error_code' => $response['error']['code']];
            }

            $db = DB::select("SELECT login_id FROM mega_users WHERE member_id = ?",[Auth::id()]);

            $loginId = $db[0]->login_id;

            return ['status' => 1, 'error_code' => $response['error'], 'iframe' => $response['result'], 'login_id' => $loginId];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public function balanceTransferQuery(Request $request)
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.balance.transfer.query';
            $id = $random;

            $db = DB::select("SELECT login_id FROM mega_users WHERE member_id = ?",[Auth::id()]);

            $loginId = $db[0]->login_id;

            $digest = md5($random.$sn.$secretCode);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"loginId" => $loginId
                        ,"agentLoginId" => $agentLoginId
                        ,"startTime" => ""
                        ,"endTime" => ""
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);


            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            return ['success' => 1, 'error_code' => $response['error'], 'results' => $response['result']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public static function withdrawAllAmount()
    {
        try 
        {
            $getbalance = self::getBalance();
            $responseArr = [];

            if ($getbalance['success'] == 1) 
            {
                $response = self::balanceTransfer(-$getbalance['balance']);
            }
            else
            {
                return ['success' => 0, 'error_code' => 'Get MEGA balance failed'];
            }

            $responseArr[Providers::MEGA] = $response;

            return $responseArr;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'MEGA withdraw all: Internal Error'];
        }
    }

    //hardcode (starttime/endtime)
    public function getTotalReport(Request $request)
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.player.total.report';
            $id = $random;

            $digest = md5($random.$sn.$agentLoginId.$secretCode);
            $endTime = date('Y-m-d H:i:s', time() + 86400);

            // return($endTime);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"agentLoginId" => $agentLoginId
                        ,"type" => 1
                        ,"startTime" => "2022-03-01 00:00:00"
                        ,"endTime" => $endTime
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            return($response);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            $id = $response['id'];
            $timeTaken = '';

            $date = strtotime("now");

            foreach ($response['result'] as $r) 
            {
                $loginId = $r['loginId'];
                $bet = $r['bet'];
                $yield = $r['yield'];
                $name = $r['name'];
                $tel = $r['tel'];
                $idx = $r['idx'];
                $win = $r['win'];

                $db = DB::select("SELECT bet, updated_at, created_at
                                FROM mega_total_record
                                WHERE login_id = ?"
                                ,[$loginId]);

                if (sizeof($db) == 0 || $db[0]->bet != $bet) 
                {
                    DB::insert("INSERT INTO mega_total_record(id,login_id,bet,yield,win,name,tel,idx,created_at)
                            VALUES
                            (?,?,?,?,?,?,?,?,NOW())
                            ON DUPLICATE KEY UPDATE
                               bet = VALUES(bet)
                               ,yield = VALUES(yield)
                               ,win = VALUES(win)
                               ,idx = VALUES(idx)
                               ,updated_at = ?"
                            ,[$id,$loginId,$bet,$yield,$win,$name,$tel,$idx,NOW()]);

                    $datetime = strtotime($db[0]->updated_at);

                    if ($db[0]->updated_at == NULL) 
                    {
                        $datetime = strtotime($db[0]->created_at);
                    }

                    $now = strtotime("now");
                    $timeTaken = $now - $datetime;
                    $timeTaken = date('i',$timeTaken); 
                    // return $date;
                }
            }
            

            return ['success' => 1, 'error_code' => $response['error'], 'results' => $response['result'], 'time_taken' => $timeTaken];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    //hardcode (starttime/endtime)
    public function getOrderPage(Request $request)
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.game.order.page';
            $id = $random;
            $loginId = '127908694674';

            $digest = md5($random.$sn.$loginId.$secretCode);
            $endTime = date('Y-m-d H:i:s', time() + 86400);

            // return($endTime);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"loginId" => $loginId
                        ,"startTime" => "2022-09-01 00:00:00"
                        ,"endTime" => $endTime
                        ,"pageSize" => 100000
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            log::debug($url);
            log::debug($data);

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            return($response);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            $id = $response['id'];
            $timeTaken = '';

            $date = strtotime("now");

            foreach ($response['result'] as $r) 
            {
                $loginId = $r['loginId'];
                $bet = $r['bet'];
                $yield = $r['yield'];
                $name = $r['name'];
                $tel = $r['tel'];
                $idx = $r['idx'];
                $win = $r['win'];

                $db = DB::select("SELECT bet, updated_at, created_at
                                FROM mega_total_record
                                WHERE login_id = ?"
                                ,[$loginId]);

                if (sizeof($db) == 0 || $db[0]->bet != $bet) 
                {
                    DB::insert("INSERT INTO mega_total_record(id,login_id,bet,yield,win,name,tel,idx,created_at)
                            VALUES
                            (?,?,?,?,?,?,?,?,NOW())
                            ON DUPLICATE KEY UPDATE
                               bet = VALUES(bet)
                               ,yield = VALUES(yield)
                               ,win = VALUES(win)
                               ,idx = VALUES(idx)
                               ,updated_at = ?"
                            ,[$id,$loginId,$bet,$yield,$win,$name,$tel,$idx,NOW()]);

                    $datetime = strtotime($db[0]->updated_at);

                    if ($db[0]->updated_at == NULL) 
                    {
                        $datetime = strtotime($db[0]->created_at);
                    }

                    $now = strtotime("now");
                    $timeTaken = $now - $datetime;
                    $timeTaken = date('i',$timeTaken); 
                    // return $date;
                }
            }
            

            return ['success' => 1, 'error_code' => $response['error'], 'results' => $response['result'], 'time_taken' => $timeTaken];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public function getGameLogURL(Request $request)
    {
        try 
        {
            $createMember = self::createMember();

            if ($createMember['success'] == 0) 
            {
                return ['success' => 0, 'error_code' => 'CREATE_MEMBER_FAILED'];
            }

            //credential
            $apiUrl = env('MEGA_API_URL');
            $secretCode = env('MEGA_SECRET_CODE');
            $sn = env('MEGA_SN');
            $agentLoginId = env('MEGA_ACCOUNT');
            $jsonRpc = env('MEGA_JSON_RPC');

            //prepare credential
            $random = Helper::generateUniqueId(64);
            $method = 'open.mega.player.game.log.url.get';
            $id = $random;
            $loginId = '127908694674';

            $digest = md5($random.$sn.$loginId.$secretCode);
            $endTime = date('Y-m-d H:i:s', time() + 86400);

            // return($endTime);

            $data = [
                "id" => $id
                ,"method" => $method
                ,"params" =>
                    [
                        "random" => $random
                        ,"digest" => $digest
                        ,"sn" => $sn
                        ,"loginId" => $loginId
                        ,"startTime" => "2022-07-23 00:00:00"
                        ,"endTime" => $endTime
                ],
                "jsonrpc" => $jsonRpc
            ];

            $url = $apiUrl.$method;

            log::debug($url);
            log::debug($data);

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            return($response);

            if (isset($response['error']) && $response['error'] != NULL) 
            {
                return ['success' => 0, 'error_code' => $response['error']['code']];
            }

            $id = $response['id'];
            $timeTaken = '';

            $date = strtotime("now");

            foreach ($response['result'] as $r) 
            {
                $loginId = $r['loginId'];
                $bet = $r['bet'];
                $yield = $r['yield'];
                $name = $r['name'];
                $tel = $r['tel'];
                $idx = $r['idx'];
                $win = $r['win'];

                $db = DB::select("SELECT bet, updated_at, created_at
                                FROM mega_total_record
                                WHERE login_id = ?"
                                ,[$loginId]);

                if (sizeof($db) == 0 || $db[0]->bet != $bet) 
                {
                    DB::insert("INSERT INTO mega_total_record(id,login_id,bet,yield,win,name,tel,idx,created_at)
                            VALUES
                            (?,?,?,?,?,?,?,?,NOW())
                            ON DUPLICATE KEY UPDATE
                               bet = VALUES(bet)
                               ,yield = VALUES(yield)
                               ,win = VALUES(win)
                               ,idx = VALUES(idx)
                               ,updated_at = ?"
                            ,[$id,$loginId,$bet,$yield,$win,$name,$tel,$idx,NOW()]);

                    $datetime = strtotime($db[0]->updated_at);

                    if ($db[0]->updated_at == NULL) 
                    {
                        $datetime = strtotime($db[0]->created_at);
                    }

                    $now = strtotime("now");
                    $timeTaken = $now - $datetime;
                    $timeTaken = date('i',$timeTaken); 
                    // return $date;
                }
            }
            

            return ['success' => 1, 'error_code' => $response['error'], 'results' => $response['result'], 'time_taken' => $timeTaken];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }
}
