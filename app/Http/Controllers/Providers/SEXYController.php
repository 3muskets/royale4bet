<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use App\Http\Controllers\Providers;
use App\Http\Controllers\UserController;
use Log;
use DateTime;
use Auth;
use App;

class SEXYController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $refId;

    public function __construct(Request $request)
    {
        // $this->middleware('auth');

        $this->prdId = Providers::SexyGaming;
    }

    public static function mapLocale()
    {
        $locale = [
            'en' => 'en',
            'ko' => 'ko',
            'th' => 'th',
            'ja' => 'jp'
        ];

        return $locale[App::getLocale()];
    }

    //***********************************
    //  Call from merchant
    //***********************************

    public static function getGame()
    {
        try
        {
            $userId =  Auth::id();
            $userName =  Auth::user()->username;

            //check status
            $status = Auth::user()->status;
            
            if ($status != 'a') 
            {
                return 'Inactive Member!';
            }
            // $userId =  '1';
            // $userName =  'pikachu';
            $currency = env('CURRENCY');
            $language = self::mapLocale();

            if (self::createMember($userId,$userName,$language,$currency)) 
            {
                $src = self::getGameURL($userId, $isMobile, $language);

                if ($src == '') 
                {
                    return ['status' => 0, 
                            'error' => 'Error Loading Game!'];
                }

                return ['status' => 1
                            ,'iframe' => $src];
            }
            else
            {
                return ['status' => 0, 
                    'error' => 'Error Create User!'];
            }
        }
        catch(\Exception $e)
        {
            log::debug($e);
            return ['status' => 0, 
                    'error' => 'Error Loading Game!'];
        }
    }

    public static function createMember($memberId,$memberName,$language,$currency)
    {
        //map log
        // $prdId = Providers::SexyGaming;
        // $log = Helper::prepareLog($prdId);

        //map sxg credentials
        $hostName = env('SXG_HOSTNAME');
        $cert = env('SXG_CERT');
        $agentId = env('SXG_AGENTID');
        
        $arrBetLimit = ['A'=>'280901','B'=>'280902','C'=>'280903','D'=>'280904','E'=>'280905','F'=>'280906','G'=>'280907'];
        $envBetLimit = env('SXG_BET_LIMIT','B');
        $betLimit = '{"SEXYBCRT":{"LIVE":{"limitId":['.$arrBetLimit[$envBetLimit].']}}}';

        $method = 'wallet/createMember';
        $header = ['Content-Type: application/x-www-form-urlencoded'];

        $data = 'cert='.$cert.
                '&agentId='.$agentId.
                '&userId='.$memberId.
                '&currency='.strtoupper($currency).
                '&betLimit='.$betLimit;

        $url = $hostName.$method;

        //log
        // Helper::storeRequestInDatabase($log['txnId'], 'auth_create', 'r', $log['uriSegment'], '', $data, $memberId, $prdId);

        $response = Helper::postData($url,$data, $header);
        $response = json_decode($response,true);

        log::debug($response);

        //log
        // Helper::storeRequestInDatabase($log['txnId'], 'auth_create', 'x', $log['uriSegment'], '', $response, $memberId, $prdId);

        // status 1001 is account exitsted, 0000 is success
        if ($response['status'] == '1001' || $response['status'] == '0000') 
        {
            return true;
        } 
        else 
        {
            return false;
        }
    }

    public static function getGameURL($memberId,$mobile,$language)
    {
        try 
        {
            //map log
            // $prdId = Providers::SexyGaming;
            // $log = Helper::prepareLog($prdId);
            
            // $locale = self::mapLocale($language);
            $locale = 'th';
            $hostName = env('SXG_HOSTNAME');
            $cert = env('SXG_CERT');
            $agentId = env('SXG_AGENTID');

            $hostName  = helper::appendSlashToURL($hostName);
            $method = 'wallet/doLoginAndLaunchGame';
            $header = ['Content-Type: application/x-www-form-urlencoded'];

            // game detail from document excel and hardcode
            $gameCode = 'MX-LIVE-001';
            $gameType = 'LIVE';
            $platform = 'SEXYBCRT';
            $mobile = (boolval($mobile) ? 'true' : 'false');

            // language korean not yet enabled yet so i put on default eng
            // will change when sexy announce
            $data = 'cert='.$cert.
                    '&agentId='.$agentId.
                    '&userId='.$memberId.
                    '&gameCode='.$gameCode.
                    '&gameType='.$gameType.
                    '&platform='.$platform.
                    '&isMobileLogin='.$mobile;

            $url = $hostName.$method;

            //log
            // Helper::storeRequestInDatabase($log['txnId'], 'auth_gameUrl', 'r', $log['uriSegment'], '', $data, $memberId, $prdId);

            // $maintenanceQuery = Helper::checkMaintenanceSchedule($prdId);

            // if (!empty($maintenanceQuery))
            // {
            //     Helper::storeRequestInDatabase($log['txnId'], 'auth_gameUrl', 'x', $log['uriSegment'], '', 'Schedule maintenance', $memberId, $prdId);

            //     $maintenanceUrl = Helper::getMaintenanceUrl($prdId, $language);
                
            //     return $maintenanceUrl;
            // }
            
            $response = Helper::postData($url, $data, $header);

            $response = json_decode($response,true);

            log::debug($response);

            //log
            // Helper::storeRequestInDatabase($log['txnId'], 'auth_gameUrl', 'x', $log['uriSegment'], '', $response, $memberId, $prdId);

            if ($response['status'] == '0000')
            {
                return $response['url'];
            } 
            else 
            {    
                return '';
            }
        } 
        catch (\Exception $e) 
        {
            log::debug($e);

            //log
            Helper::storeRequestInDatabase($log['txnId'], 'auth_gameUrl', 'x', $log['uriSegment'], '', $response, $memberId, $prdId);
            
            return '';
        }
    }

    public static function kick($memberId)
    {
        try 
        {   
            $hostName = env('SXG_HOSTNAME');
            $cert = env('SXG_CERT');
            $agentId = env('SXG_AGENTID');
            $hostName  = helper::appendSlashToURL($hostName);
            $method = 'wallet/logout';
            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $data = 'cert='.$cert.
                '&agentId='.$agentId.
                '&userIds='.$memberId;

            $url = $hostName.$method;

            $response = Helper::postData($url,$data, $header);

            return $response;
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            
            return '';
        }
    }

    public static function updateBetLimit($memberId)
    {
        try 
        {   
            $hostName = env('SXG_HOSTNAME');
            $cert = env('SXG_CERT');
            $agentId = env('SXG_AGENTID');
            $hostName  = helper::appendSlashToURL($hostName);
            $method = 'wallet/updateBetLimit';
            $header = ['Content-Type: application/x-www-form-urlencoded'];

            $arrBetLimit = ['A'=>'260805','B'=>'260808','C'=>'260809','D'=>'260810','E'=>'260811'];
            $envBetLimit = env('SXG_BET_LIMIT','B');
            $betLimit = '{"SEXYBCRT":{"LIVE":{"limitId":['.$arrBetLimit[$envBetLimit].']}}}';
            
            $data = 'cert='.$cert.
                '&agentId='.$agentId.
                '&userIds='.$memberId.
                '&betLimit='.$betLimit;

            $url = $hostName.$method;

            $response = Helper::postData($url,$data, $header);

            return $response;
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            
            return '';
        }
    }

    //***********************
    //   Call from Providers
    //***********************
    public function api(Request $request)
    {
        try 
        {
            $message = json_decode($request['message'], true);
            $action = $message['action'];

            switch ($action) 
            {
                case 'getBalance':
                    $response = self::getBalance($request);
                break;

                case 'bet':
                    $response = self::debit($request);
                break;

                case 'settle':
                    $response = self::credit($request);
                break;

                case 'cancelBet':
                    $response = self::cancel($request);
                break;

                case 'voidBet':
                case 'voidSettle':
                case 'unsettle':
                    $response = self::unsettle($request,$action);
                break;

                default:
                    //error, Invalid transaction type.
                    $response = ['status' => 404];
                break;
            }

            header('Content-Type: application/json');
            return $response;
        }
        catch (\Exception $e) 
        {
            Log::debug($e);
        }
    }

    // sexy getBalance will keep sending request when stay at lobby
    public function getBalance($request)
    {
        try
        {
            //map request to variable
            $data = json_decode($request['message'], true);
            $header = json_encode($request->header(), true);

            // $action = $data['action'];
            // $prdId = $this->prdId;
            $memberId = $data['userId'];

            //log
            // $this->refId = Helper::storeRequestLogOnly('auth_balance', $header, $data, $memberId, $prdId);
            // $this->log['txnId'] = $this->refId;

            //check token and get balance
            // $merc = AGController::getApiDetailsById($memberId);

            $db = DB::select("SELECT b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.id = ?"
                            ,[$memberId]);

            if (sizeOf($db) == 0) 
            {
                $response = [
                        "userId" => $memberId,
                        "status" => 'INVALID_PARAMETER'
                    ];
            }
            else
            {
                //balance must be /1000
                $balance = $db[0]->balance;

                // millisecond calculate
                $d = new dateTime();
                $datetime = $d->format('Y-m-d\TH:i:s.vP');

                $response = [
                    "status" => "0000",
                    "balanceTs" => $datetime,
                    "userId" => $memberId,
                    "balance" => $balance
                ];
            }

            //log
            // Helper::storeRequestInDatabase($this->log['txnId'], 'auth_balance', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId);

            return $response;
        } 
        catch (\Exception $e) 
        {
            log::debug($e);
            $response = [
                "userId" => $memberId,
                "status" => "UNKNOWN_ERROR"
            ];

            //log
            // Helper::storeRequestInDatabase($this->log['txnId'], 'auth_balance', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId);

            return $response;
        }
    }

    public function debit($request)
    {
        try
        {
            //start execution time
            $executionStartTime = microtime(true);

            //map request to variable
            $data = json_decode($request['message'], true);
            $header = json_encode($request->header(), true);
            $prdId = $this->prdId;
            $memberId = $data['txns'][0]['userId'];

            //check user exist and map merchant details
            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.id = ?"
                            ,[$memberId]);

            if(sizeof($db) == 0)
            {
                $response = [
                                "status" => "1012",
                                "desc" => 'Account is not exists'
                            ];

                //log
                // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

                return $response;
            }

            $balance = $db[0]->balance;

            //log
            // $this->refId = Helper::storeRequestLogOnly('debit', $header, $data, $memberId, $prdId);
            // $this->log['txnId'] = $this->refId;
            try
            {
                DB::beginTransaction();

                foreach ($data['txns'] as $key => $value) 
                {
                    $txnId = $value['platformTxId'];
                    $extTxnId = $txnId;
                    $amount = $value['betAmount'];
                    $roundId = $value['roundId'];
                    $gameId = self::getSXGGame($value['gameCode']);
                    $tableId = $value['gameInfo']['tableId'] ?? 0;

                    if($balance < $amount)
                    {
                        $response = [
                                        "status" => "1012",
                                        "desc" => 'Account is not exists'
                                    ];

                        //log
                        // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

                        return $response;
                    }

                    log::debug($value);
                    
                    $checkTxnExist = DB::SELECT("SELECT txn_id
                                                 FROM sxg_debit 
                                                 WHERE txn_id = ?
                                                 AND round_id = ?
                                                 AND table_id = ?
                                                ",[$txnId,$roundId,$tableId]);

                    // //check is it betslip already exist in thier own db
                    if (sizeOf($checkTxnExist) != 0) 
                    {
                        DB::rollBack();
                        $response = [
                                        "status" => "9999",
                                        "desc" => 'BET_ALREADY_EXIST.'
                                    ]; 

                        //end execution time
                        $executionEndTime = microtime(true);
                        $seconds = ($executionEndTime - $executionStartTime)*1000;
                        $seconds = round($seconds);

                        //log
                        // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
                    
                        return $response;
                    }

                    // $agCode = DB::SELECT("SELECT ag_code FROM member WHERE id = ?",[$memberId]);
                    // $agCode = $agCode[0]->ag_code;

                    // $pt = DB::SELECT("SELECT ca_pt,sma_pt,ma_pt,ag_pt
                    //                   FROM pt_effective
                    //                   WHERE prd_id = ?
                    //                   AND ag_code = ?
                    //                 ",[$prdId,$agCode]);

                    // $extraPt = DB::select("SELECT ex_pt FROM extra_pt WHERE ag_code = ? AND prd_id = ?"
                    //                             ,[$agCode,$prdId]);
                        
                    // $extraPt = $extraPt[0]->ex_pt ?? 0;

                    // $caPt = $pt[0]->ca_pt;
                    // $caPt = $caPt - $extraPt;
                    // $smaPt = $pt[0]->sma_pt;
                    // $maPt = $pt[0]->ma_pt;
                    // $agPt = $pt[0]->ag_pt;
                    // $agPt = $agPt + $extraPt;

                    DB::INSERT("INSERT INTO sxg_debit 
                                (member_id,game_id,table_id,round_id,txn_id,amount,created_at)
                                VALUES (?,?,?,?,?,?,NOW())
                               ",[$memberId,$gameId,$tableId,$roundId,$txnId,$amount]);

                    //update balance
                    $db = DB::update('
                        UPDATE users_balance
                        SET balance = balance - ?
                        WHERE user_id = ?'
                        ,[  $amount
                            ,$memberId]);

                    $balance = $balance - $amount;

                    // DB::INSERT("INSERT INTO sxg_pt
                    //             (member_id,txn_id,
                    //             ag_pt,ma_pt,sma_pt,ca_pt,updated_at)
                    //             VALUES(?,?,?,?,?,?,?)
                    //            ",[$memberId,$txnId,$agPt,$maPt,$smaPt,$caPt,NULL]);
                }
            } 
            catch(\Exception $e)
            {
                DB::rollBack();
                log::debug($e);

                $response = [
                                "status" => '0000',
                                'desc'=> 'fail'
                            ];  

                //end execution time
                $executionEndTime = microtime(true);
                $seconds = ($executionEndTime - $executionStartTime)*1000;
                $seconds = round($seconds);

                //log
                // Helper::storeRequestInDatabase($this->log['txnId'], 'credit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
            
                return $response;
            }


            // millisecond calculate
            $d = new dateTime();
            $datetime = $d->format('Y-m-d\TH:i:s.vP');

            $response = [
                            'status' => '0000',
                            'balance' => $balance,
                            'balanceTs' => $datetime
                        ];

            log::debug($response);

            // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds); 

            DB::commit(); 

            return $response;
        }
        catch(\Exception $e)
        {   
            DB::rollback();
            log::debug($e);
            $response = [
                            "status" => '9999',
                            "desc" => 'fail'
                        ];

            // //log
            // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

            return $response;
        }
    }

    public function storeCreditAsCancel($memberId,$requestType,$txnId,$amount)
    {
        try
        {
            DB::beginTransaction();               

            DB::INSERT("INSERT INTO sxg_credit(txn_id,type,ext_txn_id,amount)
                        VALUES(?,?,?,?)"
                        ,[$txnId,$requestType,$txnId,$amount]);

            $db = DB::SELECT('SELECT p.ag_pt,p.ma_pt,p.sma_pt,p.ca_pt,d.amount debit
                             FROM sxg_pt as p
                             INNER JOIN sxg_credit AS c
                                ON p.txn_id = c.txn_id
                             INNER JOIN sxg_debit AS d
                                ON p.txn_id = d.txn_id
                             WHERE p.txn_id = ?
                                AND p.member_id = ?'
                             ,[$txnId,$memberId]
                        );

            $credit = $amount;
            $debit = $db[0]->debit; 

            $caPt = $db[0]->ca_pt;
            $smaPt = $db[0]->sma_pt;
            $maPt = $db[0]->ma_pt;
            $agPt = $db[0]->ag_pt;

            $winlose = $credit - $debit;

            if ($winlose < 0 )
            {
                $smaPtAmt = -(ceil($winlose * $smaPt)) / 100;
                $maPtAmt  = -(ceil($winlose * $maPt)) / 100;
                $agPtAmt  = -(ceil($winlose * $agPt)) / 100;
                $caPtAmt  = -($winlose) - $smaPtAmt - $maPtAmt - $agPtAmt;
            }
            else
            {
                $smaPtAmt = -(floor($winlose * $smaPt)) / 100;
                $maPtAmt  = -(floor($winlose * $maPt)) / 100;
                $agPtAmt  = -(floor($winlose * $agPt)) / 100;
                $caPtAmt  = -($winlose) - $smaPtAmt - $maPtAmt - $agPtAmt;
            }


            DB::UPDATE('UPDATE sxg_pt 
                        SET ag_amt = ?,ma_amt = ?,sma_amt = ?,ca_amt = ?
                        WHERE txn_id = ?
                        AND member_id = ?'
                        ,[$agPtAmt,$maPtAmt,$smaPtAmt,$caPtAmt,$txnId,$memberId]
                    );
            DB::commit();
        } 
        catch(\Exception $e)
        {
            DB::rollBack();

            Log::debug($e);
        }
    }

    public function credit($request)
    {
        try
        {
            //start execution time
            $executionStartTime = microtime(true);

            //map request to variable
            $data = json_decode($request['message'], true);
            $header = json_encode($request->header(), true);
            $prdId = $this->prdId;
            $requestType = 'c';
            $memberId = $data['txns'][0]['userId'];

            //check user exist and map merchant details
            $member = DB::select("SELECT id
                                FROM users
                                WHERE id = ?"
                                ,[$memberId]);

            if(sizeof($member) == 0)
            {
                $response = [
                                "status" => "1012",
                                "desc" => 'Account is not exists'
                            ];

                //log
                // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

                return $response;
            }

            $balance = UserController::getBalance($memberId);

            //log
            // $this->refId = Helper::storeRequestLogOnly('credit', $header, $data, '', $prdId);
            // $this->log['txnId'] = $this->refId;

            try
            {
                DB::beginTransaction();
                foreach ($data['txns'] as $key => $value) 
                {
                    $txnId = $value['platformTxId'];
                    $extTxnId = $txnId;
                    $amount = $value['winAmount'];
                    $roundId = $value['roundId'];
                    $gameId = self::getSXGGame($value['gameCode']);
                    $tableId = $value['gameInfo']['tableId'] ?? 0;
                    $memberId = $value['userId'];
                    $response = json_encode($value);

                    $checkExist = DB::SELECT("SELECT txn_id FROM sxg_credit WHERE txn_id = ?",[$txnId]);

                    //check is it betslip already exist in thier own db
                    if (sizeOf($checkExist) != 0) 
                    {
                        DB::rollBack();
                        $response = [
                                        "status" => '0000',
                                        "desc" => 'BET_ALREADY_SETTLED'
                                    ];

                        //end execution time
                        $executionEndTime = microtime(true);
                        $seconds = round(($executionEndTime - $executionStartTime)*1000);

                        //log
                        // Helper::storeRequestInDatabase($this->log['txnId'], 'credit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
                            
                        return $response;
                    }

                    $checkDebitExist = DB::SELECT("SELECT txn_id , amount
                                                   FROM sxg_debit 
                                                   WHERE txn_id = ?
                                                   AND table_id = ?
                                                  ",[$txnId,$tableId]);

                    //check is it exist in debit where integrator_status = new (CREDIT)
                    if (sizeOf($checkDebitExist) == 0) 
                    {
                        DB::rollBack();
                        $response = [
                                        "status" => '0000',
                                        "desc" => 'BET_DOES_NOT_EXIST'
                                    ];

                        //end execution time
                        $executionEndTime = microtime(true);
                        $seconds = round(($executionEndTime - $executionStartTime)*1000);

                        //log
                        // Helper::storeRequestInDatabase($this->log['txnId'], 'credit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);

                        return $response;
                    }

                    $debitAmt = $checkDebitExist[0]->amount;

                    // DB::INSERT("INSERT INTO sxg_cache_bet_details(member_id,txn_id,response)
                    //             VALUES(?,?,?)
                    //            ",[$memberId,$txnId,$response]);

                    //calculate PT and COMM
                    $db = DB::select('
                        SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                        FROM users a
                        LEFT JOIN pt_eff b ON a.admin_id = b.admin_id
                        LEFT JOIN admin_comm c ON a.admin_id = c.admin_id
                        WHERE a.id = ?
                            AND b.prd_id = ?'
                        ,[$memberId,$this->prdId]);

                    $tier1PT = $db[0]->tier1_pt;
                    $tier2PT = $db[0]->tier2_pt;
                    $tier3PT = $db[0]->tier3_pt;
                    $tier4PT = $db[0]->tier4_pt;
                    $tier4Comm = $db[0]->comm;

                    $wlAmt = $debitAmt - $amount;

                    $tier4PTAmt = $wlAmt * ($tier4PT / 100);
                    Helper::removePrecision($tier4PTAmt);

                    $tier3PTAmt = $wlAmt * ($tier3PT / 100);
                    Helper::removePrecision($tier3PTAmt);

                    $tier2PTAmt = $wlAmt * ($tier2PT / 100);
                    Helper::removePrecision($tier2PTAmt);

                    $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

                    $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

                    //insert transaction
                    $db = DB::insert('
                            INSERT INTO sxg_credit
                            (txn_id,
                            type,
                            amount,
                            tier1_pt,tier2_pt,tier3_pt,tier4_pt,
                            tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt,
                            created_at)
                            VALUES
                            (?,?,?,?,?,?,?,?,?,?,?,NOW())'
                            ,[  $txnId,
                                $requestType,
                                $amount,
                                $tier1PT,$tier2PT,$tier3PT,$tier4PT,
                                $tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);

                    //update balance
                    $db = DB::update('
                        UPDATE users_balance
                        SET balance = balance + ?
                        WHERE user_id = ?'
                        ,[  $amount
                            ,$memberId]);

                    $balance = $balance + $amount;

                }
                DB::commit();
            } 
            catch(\Exception $e)
            {
                DB::rollBack();
                log::debug($e);

                $response = [
                                "status" => '0000',
                                'desc'=> 'fail'
                            ];  

                //end execution time
                $executionEndTime = microtime(true);
                $seconds = ($executionEndTime - $executionStartTime)*1000;
                $seconds = round($seconds);

                //log
                // Helper::storeRequestInDatabase($this->log['txnId'], 'credit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
            
                return $response;
            }

            $response = ['status' => '0000'];

            //end execution time
            $executionEndTime = microtime(true);
            $seconds = round(($executionEndTime - $executionStartTime)*1000);

            // Helper::storeRequestInDatabase($this->log['txnId'], 'credit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);

            return $response;
        }
        catch(\Exception $e)
        {
            log::debug($e);
            $response = [
                            "status" => '0000',
                            "desc" => 'fail'
                        ];
            //log
            // Helper::storeRequestInDatabase($this->log['txnId'], 'credit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

            return $response;
        }
    }

    public function cancel($request)
    {
        try
        {
            //start execution time
            $executionStartTime = microtime(true);

            // $d = new dateTime();
            // $datetime = $d->format('Y-m-d\TH:i:s.vP');


            // $response = [
            //                 'status' => '0000',
            //                 'balance' => 100000,
            //                 'balanceTs' => $datetime
            //             ];

            // return $response;

            //map request to variable
            $data = json_decode($request['message'], true);
            $header = json_encode($request->header(), true);
            $prdId = $this->prdId;
            $memberId = $data['txns'][0]['userId'];
            $requestType = 'x';

            //check user exist and map merchant details
            $member = DB::select("SELECT id
                                FROM users
                                WHERE id = ?"
                                ,[$memberId]);

            if(sizeof($member) == 0)
            {
                $response = [
                                "status" => "1012",
                                "desc" => 'Account is not exists'
                            ];

                //log
                // Helper::storeRequestInDatabase($this->log['txnId'], 'debit', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

                return $response;
            }

            $balance = UserController::getBalance($memberId);

            //log
            // $this->refId = Helper::storeRequestLogOnly('cancel', $header, $data, $memberId, $prdId);
            // $this->log['txnId'] = $this->refId;

            try
            {
                DB::beginTransaction();
                foreach ($data['txns'] as $key => $value) 
                {
                    $txnId = $value['platformTxId'];
                    $extTxnId = $txnId;
                    $roundId = $value['roundId'];
                    $tableId = $value['gameInfo']['tableId'] ?? 0;

                    $checkExist = DB::SELECT("SELECT txn_id FROM sxg_credit WHERE txn_id = ?",[$txnId]);

                    //check is it betslip already exist in thier own db
                    if (sizeOf($checkExist) != 0) 
                    {
                        DB::rollBack();
                        $response = [
                                        "status" => '0000',
                                        "desc" => 'BET_ALREADY_SETTLED'
                                    ];

                        //end execution time
                        $executionEndTime = microtime(true);
                        $seconds = round(($executionEndTime - $executionStartTime)*1000);

                        //log
                        // Helper::storeRequestInDatabase($this->log['txnId'],'cancel', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
                            
                        return $response;
                    }

                    $checkDebitExist = DB::SELECT("SELECT txn_id, amount
                                                   FROM sxg_debit 
                                                   WHERE txn_id = ?
                                                   AND table_id = ?
                                                  ",[$txnId,$tableId]);

                    //check is it exist in debit where integrator_status = new (CREDIT)
                    if (sizeOf($checkDebitExist) == 0) 
                    {
                        DB::rollBack();
                        $response = [
                                        "status" => '0000',
                                        "desc" => 'BET_DOES_NOT_EXIST'
                                    ];

                        //end execution time
                        $executionEndTime = microtime(true);
                        $seconds = round(($executionEndTime - $executionStartTime)*1000);

                        //log
                        // Helper::storeRequestInDatabase($this->log['txnId'],'cancel', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
                            
                        return $response;
                    }

                    $amount = $checkDebitExist[0]->amount;

                    //calculate PT and COMM
                    $db = DB::select('
                        SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                        FROM users a
                        LEFT JOIN pt_eff b ON a.admin_id = b.admin_id
                        LEFT JOIN admin_comm c ON a.admin_id = c.admin_id
                        WHERE a.id = ?
                            AND b.prd_id = ?'
                        ,[$memberId,$this->prdId]);

                    $tier1PT = $db[0]->tier1_pt;
                    $tier2PT = $db[0]->tier2_pt;
                    $tier3PT = $db[0]->tier3_pt;
                    $tier4PT = $db[0]->tier4_pt;
                    $tier4Comm = $db[0]->comm;

                    $wlAmt = 0;

                    $tier4PTAmt = $wlAmt * ($tier4PT / 100);
                    Helper::removePrecision($tier4PTAmt);

                    $tier3PTAmt = $wlAmt * ($tier3PT / 100);
                    Helper::removePrecision($tier3PTAmt);

                    $tier2PTAmt = $wlAmt * ($tier2PT / 100);
                    Helper::removePrecision($tier2PTAmt);

                    $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

                    $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

                    //insert transaction
                    $db = DB::insert('
                            INSERT INTO sxg_credit
                            (txn_id,
                            type,
                            amount,
                            tier1_pt,tier2_pt,tier3_pt,tier4_pt,
                            tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt,
                            created_at)
                            VALUES
                            (?,?,?,?,?,?,?,?,?,?,?,NOW())'
                            ,[  $txnId,
                                $requestType,
                                $amount,
                                $tier1PT,$tier2PT,$tier3PT,$tier4PT,
                                $tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);

                    //update balance
                    $db = DB::update('
                        UPDATE users_balance
                        SET balance = balance + ?
                        WHERE user_id = ?'
                        ,[  $amount
                            ,$memberId]);

                    $balance = $balance + $amount;
                }
                DB::commit();
            } 
            catch(\Exception $e)
            {
                DB::rollBack();
                log::debug($e);

                $response = [
                                "status" => '0000',
                                'desc'=> 'fail'
                            ];  

                //end execution time
                $executionEndTime = microtime(true);
                $seconds = ($executionEndTime - $executionStartTime)*1000;
                $seconds = round($seconds);

                //log
                // Helper::storeRequestInDatabase($this->log['txnId'],'cancel', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);
            
                return $response;
            }

            // millisecond calculate
            $d = new dateTime();
            $datetime = $d->format('Y-m-d\TH:i:s.vP');

            $response = [
                            'status' => '0000',
                            'balance' => $balance,
                            'balanceTs' => $datetime
                        ];

            //end execution time
            $executionEndTime = microtime(true);
            $seconds = round(($executionEndTime - $executionStartTime)*1000);

            //log
            // Helper::storeRequestInDatabase($this->log['txnId'],'cancel', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId, $seconds);

            return $response;
        }
        catch(\Exception $e)
        {
            log::debug($e);

            $response = [
                            "status" => '0000',
                            "desc" => 'fail'
                        ];

            //log
            // Helper::storeRequestInDatabase($this->log['txnId'],'cancel', 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId, $txnId);

            return $response;
        }
    }

    public function unsettle($request, $action)
    {
        try
        {
            $type = $action;
            //map request to variable
            $data = json_decode($request['message'], true);
            $header = json_encode($request->header(), true);
            $prdId = $this->prdId;
            $memberId = $data['txns'][0]['userId'];

            //log
            $this->refId = Helper::storeRequestLogOnly($type, $header, $data, $memberId, $prdId);
            $this->log['txnId'] = $this->refId;

            if ($type == 'voidBet') 
            {
                DB::beginTransaction();
                foreach ($data['txns'] as $key => $value) 
                {
                    $txnId = $value['platformTxId'];
                    $betAmount = $value['betAmount'];
                    $memberId = $value['userId'];
                    $checkExist = DB::SELECT("SELECT txn_id FROM sxg_credit WHERE txn_id = ?",[$txnId]);

                    if (empty($checkExist)) 
                    {
                        DB::INSERT("INSERT INTO sxg_credit(txn_id,type,amount,created_at)
                            VALUES (?,?,?,?)
                           ",[$txnId,'x',$betAmount,NOW()]);
                    }
                }
                DB::commit();
            }

           $response = ["status" => '0000'];

            //log
            // Helper::storeRequestInDatabase($this->log['txnId'],$type, 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId);

            return $response;
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            Log::debug($e);
            $response = ["status" => '0000',"desc" => 'fail'];
            
            //log
            // Helper::storeRequestInDatabase($this->log['txnId'],$type, 'x',$this->log['uriSegment'], $header, $response, $memberId, $prdId);

            return $response;
        }
    }

    public static function updateDB($txnId,$roundId,$tableId,$type)
    {
        try
        {
            //select for lock the table
            DB::select("SELECT amount 
                        FROM sxg_debit  
                        WHERE txn_id = ?
                        AND ext_round_id = ?
                        AND ext_table_id = ? 
                            for UPDATE"
                        ,[$txnId,$roundId,$tableId]
                    );


            DB::update("UPDATE sxg_debit 
                SET ag_response = ? 
                WHERE txn_id = ?
                        AND ext_round_id = ?
                        AND ext_table_id = ?", 
                [$type,$txnId,$roundId,$tableId]);

        }
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public static function getSXGGame($gameCode)
    {
        $gameId = DB::select("SELECT id 
                            FROM sxg_games
                            WHERE game_code = ?",
                            [$gameCode]);

        if (sizeOf($gameId) > 0) 
        {
            $gameId = $gameId[0]->id;
        }
        else
        {
            $id = DB::select("SELECT MAX(id) id
                            FROM sxg_games");

            $gameId = $id[0]->id + 1;

            DB::insert("INSERT INTO sxg_games
                        (game_code,id)
                            VALUES
                            (?,?)",
                        [$gameCode,$gameId]);
        }

        return $gameId;
    }
}
