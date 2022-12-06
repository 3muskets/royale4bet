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
use DateTime;

//transfer
class SCRController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // log::debug('here');
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function createUser()
    {
        try 
        {
            $url = env('SCR_API_URL').'/player';
            $apiPassword = env('SCR_API_PASSWORD');
            $apiUserId =  env('SCR_API_USER');
            $playerName = Auth::user()->username;
            $playerPassword = Helper::generateRandomString(10);
            $operation = 'addnewplayer';

            $memberId = Auth::id();

            $db = DB::select("SELECT login_id, ft_password
                            FROM scr_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if (sizeof($db) !== 0) 
            {
                return ['success' => 1, 'login_id' => $db[0]->login_id, 'ft_password' => $db[0]->ft_password];
            }

            // $time = floor(microtime(true) * 1000);
            $data = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation
                    ,'playername' => 'royale_'.$playerName
                    ,'playertelno' => '0123456789'
                    ,'playerdescription' => ''
                    ,'playerpassword' => $playerPassword
                ];

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['returncode'] == 0) 
            {
                $playerId = $response['playerid'];

                DB::insert("INSERT INTO scr_users (member_id, login_id, ft_password)
                            VALUES(?,?,?)"
                            ,[$memberId, $playerId, $playerPassword]);

                return ['success' => 1, 'login_id' => $playerId, 'ft_password' => $playerPassword];
            }
            else
            {
                return ['success' => 0, 'error_code' => $response['message']];
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    public static function getPlayerInfo()
    {
        try 
        {
            $url = env('SCR_API_URL').'/player';
            $apiPassword = env('SCR_API_PASSWORD');
            $apiUserId =  env('SCR_API_USER');
            $memberId = Auth::id();
            $operation = 'getplayerinfo';

            $db = DB::select("SELECT login_id, ft_password
                            FROM scr_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if (sizeof($db) == 0) 
            {
                $user = self::createUser();

                if ($user['success'] == 1) 
                {
                    $playerId = $user['login_id'];
                    $password = $user['ft_password'];
                }
                else
                {
                    return ['success' => 0, 'error_code' => $user['error_code']];
                }
            }
            else
            {
                $playerId = $db[0]->login_id;
                $password = $db[0]->ft_password;
            }

            // $time = floor(microtime(true) * 1000);
            $data = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation
                    ,'playerid' => $playerId
                ];

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['returncode'] == 0) 
            {
                $id = $response['id'];
                $balance = $response['balance'];

                return ['success' => 1, 'login_id' => $playerId, 'ft_password' => $password, 'balance' => $balance];
            }
            else
            {
                return ['success' => 0, 'error_code' => $response['message']];
            }
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    }

    public static function launchGames()
    {
        try 
        {
            //mobile
            $userdlUrl = env('SCR_GAMEDL_URL');

            $response = self::getPlayerInfo();

            if ($response['success'] == 0) 
            {
                return ['status' => 0, 'error_code' => $response['error_code'], 'iframe' => '', 'login_id' => '', 'ft_password' => ''];
            }

            return ['status' => 1, 'iframe' => $userdlUrl, 'login_id' => $response['login_id'], 'ft_password' => $response['ft_password']];

        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }

    //add credit
    public static function deposit()
    {
        try 
        {
            //predeposit then deposit to member
            $url = env('SCR_API_URL').'/funds';
            $apiPassword = env('SCR_API_PASSWORD');
            $apiUserId =  env('SCR_API_USER');
            $memberId = Auth::id();
            $operation = 'predeposit'; //1st
            $operation2 = 'deposit'; //2nd

            $db = DB::select("SELECT login_id
                            FROM scr_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if(sizeof($db) == 0)
            {
                $response = self::createAccount();

                if ($response['success'] == 0) 
                {
                    return ['success' => 0, 'error_code' => $response['error_code']];
                }

                $loginId = $response['login_id'];
            }
            else
            {
                $loginId = $db[0]->login_id;
            }

            //pre deposit
            $predata = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation
                ];

            //deposit
            $data = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation2
                    ,'playerid' => $loginId
                ];

            //deposit
            $type = 'd';

            $balanceBef = DB::select("SELECT available 
                        FROM member_credit
                        WHERE member_id = ?
                        FOR UPDATE"
                        ,[$memberId]);

            $amount = $balanceBef[0]->available;
            $balanceBef = $balanceBef[0]->available;

            if ($amount == 0) 
            {
                return ['success' => 0, 'error_code' => 'INSUFFICIENT_FUNDS'];
            }

            DB::insert("INSERT INTO scr_wallet_transfer(prd_id, member_id, transfer_to, type, amount, status, created_at)
                            VALUES(?,?,?,?,?,?,NOW())"
                            ,[Providers::SCR, Auth::id(), 'm', $type, $amount, 'p']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM noe_wallet_transfer");

            $orderId = $db[0]->id;

            $response = Helper::postData($url,$predata);
            $response = json_decode($response,true);

            if ($response['returncode'] == 0) 
            {
                $tid = $response['tid'];
            }
            else
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE scr_wallet_transfer
                            SET status = 'x'
                                , error_code = ?
                            WHERE id = ?"
                            ,[$response['message'],$orderId]);

                return ['success' => 0, 'error_code' => $response['message']];
            }

            $data['tid'] = $tid;
            $data['amount'] = $amount;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['returncode'] == 0) 
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        ,available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

                DB::update("UPDATE scr_wallet_transfer
                            SET status = 'a'
                            WHERE id = ?"
                            ,[$orderId]);

                Helper::storeTxnCredit($orderId,Providers::SCR,$memberId,$balanceBef,$amount);
            }
            else
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE scr_wallet_transfer
                            SET status = 'x'
                                , error_code = ?
                            WHERE id = ?"
                            ,[$response['message'],$orderId]);

                return ['success' => 0, 'error_code' => $response['message']];
            }

            return ['success' => 1, 'error_code' => $response['message']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => __('error.dw.internal_error')];
        }
    }

    public static function withdrawAll()
    {
        try 
        {
            //predeposit then deposit to member
            $url = env('SCR_API_URL').'/funds';
            $apiPassword = env('SCR_API_PASSWORD');
            $apiUserId =  env('SCR_API_USER');
            $memberId = Auth::id();
            $operation = 'prewithdrawall'; //1st
            $operation2 = 'withdrawall'; //2nd

            $db = DB::select("SELECT login_id
                            FROM scr_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if(sizeof($db) == 0)
            {
                $response = self::createAccount();

                if ($response['success'] == 0) 
                {
                    return ['success' => 0, 'error_code' => $response['error_code']];
                }

                $loginId = $response['login_id'];
            }
            else
            {
                $loginId = $db[0]->login_id;
            }

            //pre withdraw
            $predata = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation
                ];

            //withdraw
            $data = ['apiuserid' => $apiUserId
                    ,'apipassword' => $apiPassword
                    ,'operation' => $operation2
                    ,'playerid' => $loginId
                ];

            //withdraw
            $type = 'w';

            $balanceBef = DB::select("SELECT available 
                        FROM member_credit
                        WHERE member_id = ?
                        FOR UPDATE"
                        ,[$memberId]);

            $balanceBef = $balanceBef[0]->available;

            $playerInfo = self::getPlayerInfo();

            if ($playerInfo['success'] == 1) 
            {
                $amount = $playerInfo['balance'];
            }
            else
            {
                return ['success' => 0, 'error_code' => 'PLAYER_INFO_FAILED'];
            }

            DB::insert("INSERT INTO scr_wallet_transfer(prd_id, member_id, transfer_to, type, amount, status, created_at)
                            VALUES(?,?,?,?,?,?,NOW())"
                            ,[Providers::SCR, Auth::id(), 'm', $type, $amount, 'p']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM noe_wallet_transfer");

            $orderId = $db[0]->id;

            $response = Helper::postData($url,$predata);
            $response = json_decode($response,true);

            if ($response['returncode'] == 0) 
            {
                $tid = $response['tid'];
            }
            else
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE scr_wallet_transfer
                            SET status = 'x'
                                , error_code = ?
                            WHERE id = ?"
                            ,[$response['message'],$orderId]);

                return ['success' => 0, 'error_code' => $response['message']];
            }

            $data['tid'] = $tid;

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['returncode'] == 0) 
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        ,available = available + ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

                DB::update("UPDATE scr_wallet_transfer
                            SET status = 'a'
                            WHERE id = ?"
                            ,[$orderId]);

                Helper::storeTxnCredit($orderId,Providers::SCR,$memberId,$balanceBef,$amount);
            }
            else
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE scr_wallet_transfer
                            SET status = 'x'
                                , error_code = ?
                            WHERE id = ?"
                            ,[$response['message'],$orderId]);

                return ['success' => 0, 'error_code' => $response['message']];
            }

            return ['success' => 1, 'error_code' => $response['message']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => __('error.dw.internal_error')];
        }
    }
}
