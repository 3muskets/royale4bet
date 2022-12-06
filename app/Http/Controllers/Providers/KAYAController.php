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
class KAYAController extends Controller
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
        // $this->aes = env('KAYA_AES');
        // $this->md5 = env('KAYA_MD5');
        // $this->apiUrl = env('KAYA_API_URL');
        // $this->apiUser = env('KAYA_USER');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public static function encrypt($input, $key) 
    {
        $cipher = "aes-128-ecb";
        $data = openssl_encrypt($input,$cipher,$key,OPENSSL_PKCS1_PADDING);

        return $data;
    }

    private static function pkcs5_pad ($text, $blocksize) 
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public static function AESEncode($data)
    {
        try 
        {
            $data = json_encode($data,true);

            $aesEncode = md5(base64_encode(self::encrypt((string)$data, env('KAYA_AES'))).env('KAYA_MD5'));
            
            return $aesEncode;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return '';
        }
    }

    public static function createUser()
    {
        try 
        {
            $memberId = Auth::id();

            $db = DB::select("SELECT login_id, ft_password
                            FROM noe_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if (sizeof($db) !== 0) 
            {
                return ['success' => 1, 'login_id' => $db[0]->login_id, 'ft_password' => $db[0]->ft_password];
            }

            $url = env('KAYA_API_URL').'/v1/accountcreate';
            $name = 'Royal'.Auth::user()->username;
            $password = 'Ryl'.Helper::generateRandomString(8);
            $accountDisplay = 'Royale_'.Auth::user()->username;
            $timestamp = time();
            $agentId = env('KAYA_USER');

            $data = ['agentID' => $agentId
                    ,'accountname' => $name
                    ,'accountPW' => $password
                    ,'accountDisplay' => $accountDisplay
                    ,'timeStamp' => $timestamp
                ];

            $signMsg = self::AESEncode($data);
            $header = array(
                "AES-ENCODE:".$signMsg,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            );

            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            if ($response['rtStatus'] == 1) 
            {
                DB::insert("INSERT INTO kaya_users (member_id, login_id, ft_password)
                            VALUES(?,?,?)"
                            ,[$memberId, $response['accountName'], $password]);

                return ['success' => 1, 'login_id' => $response['accountName'], 'ft_password' => $password];
            }
            else
            {
                return ['success' => 0, 'error_code' => $response['errorCode']];
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
            $userdlUrl = env('KAYA_GAMEDL_URL');

            $response = self::createUser();

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

    public static function getBalance()
    {
        try 
        {
            $url = env('KAYA_API_URL').'/v1/accountbalance';
            $name = 'Royal'.Auth::user()->username;
            $timestamp = time();
            $agentId = env('KAYA_USER');
            $memberId = Auth::id();

            $data = ['agentID' => $agentId
                    ,'accountname' => $name
                    ,'timeStamp' => $timestamp
                ];

            $signMsg = self::AESEncode($data);
            $header = array(
                "AES-ENCODE:".$signMsg,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            );

            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            if ($response['rtStatus'] == 1) 
            {
                return ['success' => 1, 'balance' => ($response['balance']/10000)];
            }
            else
            {
                return ['success' => 0, 'error_code' => $response['errorCode']];
            }
        } 
        catch (Exception $e) 
        {
            return ['success' => 0, 'error_code' => 'INTERNAL_ERROR'];
        }
    } 

    public static function depositAllAmount()
    {
        try 
        {
            $balanceBef = DB::select("SELECT available 
                        FROM member_credit
                        WHERE member_id = ?
                        FOR UPDATE"
                        ,[$memberId]);

            $amount = $balanceBef[0]->available;

            $response = self::dw($amount);

            return $response;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'NOE deposit all: Internal Error'];
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
                $response = self::dw(-$getbalance['balance']);
            }
            else
            {
                return ['success' => 0, 'error_code' => 'Get NOE balance failed'];
            }

            $responseArr[Providers::KAYA] = $response;

            return $responseArr;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => 'NOE withdraw all: Internal Error'];
        }
    }

    //add credit to member
    public static function dw($amount)
    {
        try 
        {
            $memberId = Auth::id();

            $db = DB::select("SELECT login_id
                            FROM kaya_users
                            WHERE member_id = ?"
                            ,[$memberId]);

            if(sizeof($db) == 0)
            {
                $response = self::createAccount();

                if ($response['success'] == 0) 
                {
                    return ['success' => 0, 'error_code' => $response['error_code'], 'launch_url' => '', 'login_id' => '', 'ft_password' => ''];
                }

                $username = $response['login_id'];
                $actionUser = $response['login_id'];
            }
            else
            {
                $username = $db[0]->login_id;
                $actionUser = $db[0]->login_id;
            }

            $type = ($amount>0)?'d':'w';

            if ($type == 'd') 
            {
                $url = env('KAYA_API_URL').'/v1/transferdeposit';
            }
            else
            {
                $url = env('KAYA_API_URL').'/v1/transferwithdraw';
            }
            
            $name = 'Royal'.Auth::user()->username;
            $timestamp = time();
            $agentId = env('KAYA_USER');

            $balanceBef = DB::select("SELECT available 
                        FROM member_credit
                        WHERE member_id = ?
                        FOR UPDATE"
                        ,[$memberId]);

            $balanceBef = $balanceBef[0]->available;

            DB::insert("INSERT INTO kaya_wallet_transfer(prd_id, member_id, transfer_to, type, amount, status, created_at)
                            VALUES(?,?,?,?,?,?,NOW())"
                            ,[Providers::KAYA, Auth::id(), 'm', $type, $amount, 'n']);

            $db = DB::select("SELECT DISTINCT last_insert_id() as id FROM kaya_wallet_transfer");

            $txnId = $db[0]->id;

            $data = ['agentID' => $agentId
                    ,'accountname' => $name
                    ,'transAmount' => abs((int)$amount*10000)
                    ,'transAgentID' => $txnId
                    ,'timeStamp' => $timestamp
                ];

            $signMsg = self::AESEncode($data);
            $header = array(
                "AES-ENCODE:".$signMsg,
                "Cache-Control: no-cache",
                "Content-Type: application/json"
            );

            $response = Helper::postData($url,$data,$header);
            $response = json_decode($response,true);

            return $response;

            if ($response['success'] == 'true') 
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        ,available = available - ?
                        WHERE member_id = ?"
                        ,[$amount,$memberId]);

                DB::update("UPDATE kaya_wallet_transfer
                            SET status = 'a'
                            WHERE id = ?"
                            ,[$orderId]);

                Helper::storeTxnCredit($orderId,Providers::KAYA,$memberId,$balanceBef,$amount);
            }
            else
            {
                DB::update("UPDATE member_credit 
                        SET updated_at = NOW()
                        WHERE member_id = ?"
                        ,[$memberId]);

                DB::update("UPDATE kaya_wallet_transfer
                            SET status = 'x'
                                , error_code = ?
                            WHERE id = ?"
                            ,[$response['errorCode'],$orderId]);

                return ['success' => 0, 'error_code' => $response['errorCode']];
            }

            return ['success' => 1, 'error_code' => $response['errorCode'], 'balance' => $response['money']];
        } 
        catch (Exception $e) 
        {
            log::debug($e);
            return ['success' => 0, 'error_code' => __('error.dw.internal_error')];
        }
    }
}
