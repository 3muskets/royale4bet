<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use App\Http\Controllers\CryptoController;
use App\Events\DWRequest;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\Providers\DNController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Providers;
use App\Http\Controllers\Providers\MEGAController;
use App\Http\Controllers\Providers\NOEController;
use App\Http\Controllers\Providers\PUSSYController;
use App\Http\Controllers\Providers\GSController;

use Auth;
use Log;

class DWController extends Controller
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
    public static function getList($type)
    {
        try
        {
            $userId =  Auth::id();

            $db = DB::select('
                SELECT a.id,a.payment_type,a.amount,a.status,(a.created_at + INTERVAL 4 HOUR) "created_at", (a.updated_at + INTERVAL 4 HOUR) "updated_at",a.promo_id
                ,b.promo_name
                FROM member_dw a
                LEFT JOIN promo_setting b
                ON a.promo_id = b.promo_id
                WHERE a.member_id = ?
                    AND a.type = ?
                ORDER BY a.created_at DESC'
                ,[$userId, $type]);

            foreach($db as $d)
            {
                $d->pType_text = Helper::getOptionsValue(self::getOptionsPaymentType(), $d->payment_type);
                $d->status_text = Helper::getOptionsValue(self::getOptionsStatus(), $d->status);
            }

            return $db;
        }
        catch(\Exception $e)
        {
            log::Debug($e);
            return [];
        }
    }

    public static function getBankList()
    {
        try
        {
            $userId =  Auth::id();

            $db = DB::select('
                            SELECT b.id, b.bank, b.name, b.acc_no
                            FROM member a 
                            LEFT JOIN admin_bank_info b
                                ON a.admin_id = b.admin_id
                            WHERE a.id = ? AND b.status = ?'
                ,[$userId, 'a']);

            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function create(Request $request)
    {
        DB::beginTransaction();

        try
        {
            

            $secretKey = env('PAYMENTGATEWAY_SECRETKEY');
            $type = $request->type;
            $amount = $request->amount;
            $amount = str_replace( ',', '', $amount);

            $paymentType = $request->payment_type;
        
            $bank = $request->bank;
            $memberName = $request->acc_name;
            $memberAccNo = $request->acc_no;
            $refId = $request->ref_id;
            $dwDate = $request->txn_date;
            $adminBankId = $request->admin_bank_id;

            $promoId = $request->promo_id;

            $img = $request->img;
            $base64 = NULL;

            $userId =  Auth::id();

            //validation
            $errMsg = [];

            //payment type cash or fiat2fiat
            if($paymentType == 'c' || $paymentType == 'f' || $paymentType == 'd')
            {
                $bank = null;
                $dwDate = null;
                $adminBankId = null;
            }
            else
            {
                if($type == 'd')
                {
                    if($img != null)
                    {
                        $type2 = pathinfo($img, PATHINFO_EXTENSION);
                        $data = file_get_contents($img);
                        $base64 = 'data:image/' . $type2 . ';base64,' . base64_encode($data);
                        unlink($img);
                    }
                    else
                    {
                        array_push($errMsg,'Please Upload Your Receipt');
                    }

                    //promotion checking 
                    //welcome promotion checking

    
                }

                //checking only for type withdraw
                if($type == 'w')
                {
                    if($bank == '')
                    {
                        array_push($errMsg, __('error.dw.emptybankname'));
                    }

                    if($memberName == '')
                    {
                        array_push($errMsg, __('error.dw.emptymemberaccname'));
                    }

                    if(!Helper::checkInputFormat('alphabetWithSpace',$bank))
                    {
                        array_push($errMsg, __('error.dw.invalidbankname'));
                    }

                    if(!Helper::checkInputFormat('alphabetWithSpace',$memberName))
                    {
                        array_push($errMsg, __('error.dw.invalidmemberaccname'));
                    }

                    if(!ctype_digit($memberAccNo))
                    {
                        array_push($errMsg, __('error.dw.invalidbankacc'));
                    }

                    //check member have pending promotion or not 
                    $db = DB::select("
                            SELECT status
                            FROM promo_turnover_txn
                            WHERE status = 'p'
                            AND member_id = ?
                            ",[$userId]
                        );

                    if(sizeof($db) != 0)
                    {
                        array_push($errMsg,'Withdraw Failed,Please Complete Your Promotion');
                    }

                }

                if(!Helper::checkInputFormat('alphanumeric',$refId))
                {
                    array_push($errMsg, __('error.dw.invalidtxn'));
                }

            }


            if($promoId == 1)
            {
                //check user is it deposit before
                $db = DB::select("
                    SELECT amount
                    FROM member_dw
                    WHERE status = 'a'
                    AND type = 'd'
                    AND member_id = ?
                    ",[$userId]);

                if(sizeof($db) != 0)
                {
                    array_push($errMsg,'Invalid Promotion,This Promotion Only For First Time User');
                }

            }  
            //Daily Bonus
            else if($promoId == 2)
            {

                $todayDate = NOW();
                $todayStartDate = date('Y-m-d 00:00:00',strtotime($todayDate. '+8 hours'));
                $todayEndDate = date('Y-m-d 23:59:59',strtotime($todayDate. '+8 hours'));

                $db = DB::select("
                        SELECT id
                        FROM member_dw
                        WHERE status = 'a'
                        AND member_id = ? AND promo_id = 2
                        AND (created_at + INTERVAL 8 HOUR) >= ?
                        AND (created_at + INTERVAL 8 HOUR) <= ?
                        ",[$userId,$todayStartDate,$todayEndDate]
                    );

                if(sizeof($db) != 0)
                {
                    array_push($errMsg,'Invalid Promotion,This Promotion Only Can Apply Once By Day');
                }
            }     

            //if request promo,checking still have pending promotion or not
            if($promoId != '')
            {
                $db = DB::select("
                    SELECT id
                    FROM member_promo_turnover
                    WHERE member_id = ? AND
                    status = 'p'
                    ",[$userId]
                );

                if(sizeof($db) != 0)
                {
                    array_push($errMsg,'Invalid Promotion,Please Complete Your Promotion');
                }
            }


            if(!Helper::checkValidOptions(self::getOptionsType(),$type))
            {
                array_push($errMsg,'error.dw.invalid_type');
            }

            if(is_numeric($amount)) 
            {
                if($amount <= 0) 
                {
                    array_push($errMsg, __('error.dw.amount_zero'));

                }
                else if(!Helper::validAmount($amount))
                {
                    array_push($errMsg, __('error.dw.invalid_credit_length'));
                }
            }
            else
            {
                array_push($errMsg, __('error.dw.invalid_credit'));
            }

            if($errMsg)
            {
                DB::rollback();

                $response = ['status' => 0
                    ,'error' => $errMsg
                    ];

                return json_encode($response);
            }   

            

            if($type == 'w')
            {
                //withdraw no need admin bank id
                $adminBankId = null;

                $dwDate = null;
                $subject = "Withdraw Request";
                $message = "Dear Player, Your funds withdraw request has been accepted! Your request will be processed within 24 hours. You can click WITHDRAW STATUS in your wallet to follow up.";

                // get and lock balance
                $db = DB::select('
                        SELECT available
                        FROM member_credit
                        WHERE member_id = ? 
                        FOR UPDATE'
                        ,[$userId]);

                $balance = $db[0]->available;

                //check enough credit to withdraw
                if($balance >= $amount)
                {
                    //update balance
                    $db = DB::update('
                        UPDATE member_credit
                        SET available = available - ?, member_withdraw_request = NOW()
                        WHERE member_id = ?'
                        ,[  $amount
                            ,$userId]);
                }
                else
                {
                    DB::rollback();

                    $response = ['status' => 0
                        ,'error' => __('error.dw.insufficient_funds')];

                    return json_encode($response);
                }
            }
            else
            {
                //deposit bank info for set to null
                $bank = null;
                $memberName = null;
                $memberAccNo = null;

                $subject = "Deposit Request";
                $message = "Dear Player, Your account Top-up request has been accepted! Your request will be processed within 24 hours. You can click DEPOSIT STATUS in your wallet to follow up.";

                 $db = DB::update('
                        UPDATE member_credit
                        SET member_deposit_request = NOW()
                        WHERE member_id = ?'
                        ,[$userId]);
            }

            DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                                VALUES(?,0,"a",?,?,NOW())',[$userId,$message,$subject]);

            if($paymentType == 'f' || $paymentType == 'd')
                $status = 'p';
            else
                $status = 'n';

            DB::insert('INSERT INTO member_dw(member_id,type,amount,status,payment_type,ref_id,dw_date,bank,member_name,member_bank_acc,image,admin_bank_id,promo_id,created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())'
                    ,[  $userId
                        ,$type
                        ,$amount
                        ,$status
                        ,$paymentType
                        ,$refId
                        ,$dwDate
                        ,$bank
                        ,$memberName
                        ,$memberAccNo
                        ,$base64
                        ,$adminBankId
                        ,$promoId
                    ]);

            $db = DB::select("SELECT id
                            FROM member_dw
                            WHERE id = LAST_INSERT_ID()
                            AND member_id = ?
                            ORDER BY created_at desc"
                            ,[$userId]);
            $txnId = '';

            if(sizeof($db) != 0 )
            {
                $txnId = $db[0]->id;
            }

            //no error
            DB::commit();

            if($paymentType != 'f' && $paymentType != 'd')
                //WS message
                self::sendWS($userId);

            $txnDet = [];

            if ($paymentType == 'f') 
            {
                $txnDet = PaymentGatewayController::orderPaymentf2f($txnId);
            }
            else if ($paymentType == 'd') 
            {
                $txnDet = DNController::payment($txnId);
            }

            // $txnDet = self::encrypt($txnId,$secretKey);
            


            $response = ['status' => 1,'txn_details' => $txnDet,'payment_type' => $paymentType];
            return $response;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);
            DB::rollback();

            $response = ['status' => 0
                    ,'error' => __('error.dw.internal_error')
                    ];

            return json_encode($response);
        }
    }


    public static function encrypt($data, $secret) 
    { 
         //Take first 8 bytes of $key and append them to the end of $key. 
         $subkey = substr($secret, 0, 8); 
         // Encrypt data 
         $encData = openssl_encrypt($data, "DES-EDE3-CBC", $secret, OPENSSL_RAW_DATA,$subkey); 

         return bin2hex($encData); 
    }

    public static function cancel(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $txnId = $request->id;

            $userId =  Auth::id();

            //check is user's txn and get info
            $db = DB::select("
                SELECT type,amount
                FROM member_dw
                WHERE id = ?
                    AND member_id = ?
                "
                ,[$txnId,$userId]);

            if(sizeOf($db) == 0)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' => __('error.dw.invalid_process')
                            ];

                return json_encode($response);
            }

            $type = $db[0]->type;
            $amount = $db[0]->amount;

            if($type == 'w')
            {
                //topup credit been held
                $db = DB::update('
                UPDATE member_credit
                SET available = available + ?
                WHERE member_id = ?'
                ,[  $amount
                    ,$userId]);
            }

            //update txn status
            $db = DB::update("
                    UPDATE member_dw
                    SET status = 'c'
                        ,updated_at = NOW()
                    WHERE id = ?
                        AND status = 'n'"
                    ,[$txnId]);

            $txnUpdated = $db;

            if(!$txnUpdated)
            {
                DB::rollback();

                $response = ['status' => 0
                            ,'error' => __('error.dw.txn_processed')
                            ];

                return json_encode($response);
            }

            //no error
            DB::commit();

            //WS message
            self::sendWS($userId);
            
            $response = ['status' => 1];
            return json_encode($response);

        }
        catch(\Exception $e)
        {
            DB::rollback();

            $response = ['status' => 0
                    ,'error' => __('error.dw.internal_error')
                    ];

            return json_encode($response);
        }
    }

    public static function createCrypto(Request $request)
    {
        DB::beginTransaction();

        try
        {
            $type = $request->type;
            $amount = $request->amount;
            $amount = str_replace( ',', '', $amount);

            //validation
            $errMsg = [];

            if(is_numeric($amount)) 
            {
                if($amount <= 0) 
                {
                    array_push($errMsg, __('error.dw.amount_zero'));

                }
                else if(!Helper::validAmount($amount))
                {
                    array_push($errMsg, __('error.dw.invalid_credit_length'));
                }
            }
            else
            {
                array_push($errMsg, __('error.dw.invalid_credit'));
            }

            if (CryptoController::getRate() == "") 
            {
                array_push($errMsg, __('error.dw.invalid_credit'));
            }
            
            $rate = CryptoController::getRate(); //crypto rate

            if($errMsg)
            {
                DB::rollback();

                $response = ['status' => 0
                    ,'error' => $errMsg
                    ];

                return json_encode($response);
            }
  
            $userId =  Auth::id();

            if($type == 'w')
            {
                $address = $request->address;
                $validAmount = 1*$rate; //crypto amount
                $tokenAmount = $amount*$rate; //crypto amount

                if ($amount < $validAmount) 
                {
                    DB::rollback();

                    $response = ['status' => 0
                        ,'error' =>  __('error.dw.invalid_crypto_amount').$validAmount];

                    return json_encode($response);
                }

                if(!Helper::checkInputLength($address,42,42))
                {
                    DB::rollback();

                    $response = ['status' => 0
                        ,'error' =>  __('error.dw.invalid_address')];

                    return json_encode($response);
                }

                $dwDate = null;
                $subject = "Withdraw Request";
                $message = "Dear Player, Your funds withdraw request has been accepted! Your request will be processed within 24 hours. You can click WITHDRAW STATUS in your wallet to follow up.";

                // get and lock balance
                $db = DB::select('
                        SELECT available
                        FROM member_credit
                        WHERE member_id = ? 
                        FOR UPDATE'
                        ,[$userId]);

                $balance = $db[0]->available;

                //check enough credit to withdraw
                if($balance >= $amount)
                {
                    //update balance
                    $db = DB::update('
                        UPDATE member_credit
                        SET available = available - ?, member_withdraw_request = NOW()
                        WHERE member_id = ?'
                        ,[  $amount
                            ,$userId]);
                }
                else
                {
                    DB::rollback();

                    $response = ['status' => 0
                        ,'error' => __('error.dw.insufficient_funds')];

                    return json_encode($response);
                } 

                DB::insert('INSERT INTO member_msg(member_id,is_read,send_by,message,subject,created_at)
                                VALUES(?,0,"a",?,?,NOW())',[$userId,$message,$subject]);

                DB::insert("INSERT INTO member_dw 
                            (member_id, type, amount, payment_type, ref_id, status, created_at)
                            VALUES (?,?,?,?,?,?,?)"
                            ,[$userId,'w', $amount, 'x', $address, 'n', NOW()]);

                $orderId = DB::getPdo()->lastInsertId();

                DB::insert("INSERT INTO member_crypto_dw 
                                (id, crypto_num, rate, created_at)
                                VALUES (?,?,?,?)"
                                ,[$orderId, $tokenAmount, $rate, NOW()]);

                //no error
                DB::commit();

                //WS message
                self::sendWS($userId);

                $response = ['status' => 1];
                return json_encode($response);
            }
            else
            {
                $db = DB::update('
                        UPDATE member_credit
                        SET member_deposit_request = NOW()
                        WHERE member_id = ?'
                        ,[$userId]);

                $crypto = CryptoController::getUserAddress();

                $tokenAmount = $amount; //crypto amount
                $currencyAmount = $amount*$rate; //crypto amount

                if ($crypto == "") 
                {
                    DB::rollback();

                    $response = ['status' => 0
                        ,'error' => 'error.dw.invalid_crypto'
                        ];

                    return json_encode($response);
                }
                else
                {
                    DB::commit();

                    $response = ['status' => 1
                        ,'address' => $crypto
                        ,'amount' => number_format($currencyAmount, 2)
                        ,'token_amount' => number_format($tokenAmount,2)
                        ];

                    return json_encode($response);
                }
            }   
        }
        catch(\Exception $e)
        {
            DB::rollback();

            log::debug($e);

            $response = ['status' => 0
                    ,'error' => __('error.dw.internal_error')
                    ];

            return json_encode($response);
        }
    }

    public static function transfer(Request $request)
    {
        try
        {
            $transferFrom = $request->input('from');
            $transferTo = $request->input('to');
            $amount = $request->input('amount');

            log::debug($request);

            $response = '';

            $errMsg = [];

            if ($amount == NULL || $amount == '') 
            {
                array_push($errMsg,'Invalid Amount');
            }

            if($errMsg)
            {
                $response = [
                                'success' => 0
                                ,'error' => $errMsg
                            ];

                return json_encode($response);
            }

            if (array_key_exists($prdId, GSController::mapProduct())) 
            {
                $response = GSController::makeTransfer($prdId,$amount);
            }
            else if ($prdId == Providers::MEGA) 
            {
                $response = MEGAController::balanceTransfer($amount);
            }
            else if ($prdId == Providers::NOE) 
            {
                $response = NOEController::setMemberScore($amount);
            }
            else if ($prdId == Providers::PUSSY) 
            {
                $response = PUSSYController::setMemberScore($amount);
            }

            log::debug($response);

            return $response;
        }   
        catch(\Exception $e)
        {
            log::debug($e);
        }
    }

    public static function getWalletAddress(Request $request)
    {
        $memberId = Auth::id();

        $walletAddr = '';


        $db = DB::select("
                    SELECT wallet_address
                    FROM member
                    WHERE id = ?
                    ",[$memberId]
                );

        if($db[0]->wallet_address == '')
        {
            $walletAddr = PaymentGatewayController::orderPaymentCyrto();

            DB::update("
                UPDATE member
                SET wallet_address = ?
                WHERE id = ?",[$walletAddr,$memberId]
            );
        }
        else
        {
            $walletAddr = $db[0]->wallet_address;
        }

        return $walletAddr;
    }

    public static function getPendingCount()
    {
        try
        {
            $userId = Auth::user()->id;
            
            $db = DB::select("
                SELECT COUNT(*) 'count'
                FROM member_dw
                WHERE member_id = ?
                    AND status = 'n'
                GROUP BY member_id"
                ,[$userId]);

            if(sizeOf($db) == 0)
                return 0;

            return $db[0]->count;
        } 
        catch (\Exception $e) 
        {
            return 0;
        }
    }

    public static function getOptionsType()
    {
        //todo localization
        return  [
            ['d', __('option.dw.deposit')]
            ,['w', __('option.dw.withdraw')]
        ];
    }

    public static function getOptionsPaymentType()
    {
        //todo localization
        return  [
            ['c', __('option.dw.cash')]
            ,['x', __('option.dw.crypto')]
            ,['b', __('option.dw.bank')]
            ,['f', __('option.dw.f2f')]
            ,['d', __('option.dw.doitnow')]
        ];
    }

    public static function getOptionsStatus()
    {
        //todo localization
        return  [
            ['n', __('option.dw.new')]
            ,['a', __('option.dw.approved')]
            ,['r', __('option.dw.rejected')]
            ,['p', __('option.dw.processing')]
            ,['c', __('option.dw.canceled')]
        ];
    }

    public static function sendWS($userId)
    {
        try
        {
            //get recipient for tier1,2,3
            $db = DB::select("
                SELECT c.id,c.ws_channel
                FROM member a
                LEFT JOIN tiers b ON a.admin_id = b.admin_id
                LEFT JOIN admin c ON c.id = b.admin_id OR c.id = b.up1_tier OR c.id = b.up2_tier
                WHERE a.id = ?"
                ,[$userId]);

            $recipients = $db;

            $aryAdmin = array_column($recipients,'id');

            //get pending request count for tier1,2,3
            $sql = "SELECT a.id,COUNT(d.id) 'count'
                    FROM admin a
                    LEFT JOIN tiers b ON (a.id = b.admin_id OR a.id = b.up1_tier OR a.id = b.up2_tier) 
                        AND b.up1_tier IS NOT NULL AND b.up2_tier IS NOT NULL
                    LEFT JOIN member c ON c.admin_id = b.admin_id
                    LEFT JOIN member_dw d ON c.id = d.member_id
                    WHERE a.id IN (?)
                        and d.status = 'n'
                    GROUP BY a.id";

            $params = [$aryAdmin];

            $pdo = Helper::prepareWhereIn($sql,$params);

            $db = DB::select($pdo['sql'],$pdo['params']);

            //send WS for tier1,2,3
            foreach($recipients as $r)
            {
                $wsChannel = $r->ws_channel;
                $count = 0;

                foreach($db as $d)
                {
                    if($r->id == $d->id)
                    {
                        $count = $d->count;
                        break;
                    }
                }

                event(new DWRequest($wsChannel,$count));
            }

            //get recipient for tier0
            $db = DB::select("
                SELECT ws_channel
                FROM admin
                WHERE level = 0
                LIMIT 1"
                ,[$userId]);

            $wsChannel = $db[0]->ws_channel;

            //get pending count for tier0
            $db = DB::select("
                SELECT COUNT(*) 'count'
                FROM member_dw
                WHERE status = 'n'");

            //send WS for tier0
            event(new DWRequest($wsChannel,$db[0]->count));
        }
        catch(\Exception $e)
        {
            // log::info($e);
        }
    }
}



















