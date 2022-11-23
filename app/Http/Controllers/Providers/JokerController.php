<?php

namespace App\Http\Controllers\Providers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Helper;
use App\Http\Controllers\Provider;
use App\Http\Controllers\UserController;
use Log;
use DateTime;
use Auth;
use App;
use DES;

class JokerController extends Controller
{
    public static function mapLocale()
    {
        $locale = array(
                    'en'  => 'en_US'
                    ,'zh-cn'  => 'zh-Hans'
                    ,'ar'  => 'en'
                );

        return $locale[App::getLocale()];
    }

    public static function getGame()
    {
        try
        {
            $db = DB::select('
                SELECT 1006 as prd_id, id, game_code as game_id, game_name,game_type,specials,image as img_url
                FROM joker_games
                WHERE status = 1
                    AND game_type != "Fishing"');

            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function getGameFishing()
    {
        try
        {
            $db = DB::select('
                SELECT id,game_code,game_name,game_type,specials,image 
                FROM joker_games
                WHERE status = 1
                    AND game_type = "Fishing"');

            return $db;
        }
        catch(\Exception $e)
        {
            return [];
        }
    }

    public static function getGameList()
    {
        try 
        {
            $url = env('JOKER_API_URL');
            $url = $url.'list-games';
            $appId = env('JOKER_APPID');
            $timestamp = time();

            $data = [
                        'AppID' => $appId,
                        'Timestamp' => $timestamp
                    ];

            $convData = self::convertRawData($data);
            $hashData = md5($convData);
            $data['Hash'] = $hashData;
            ksort($data);

            $response = Helper::postData($url,$data);
            $response = json_decode($response,true);

            if ($response['Error'] != 0) 
            {
                $response = ['status' => 0,
                            'iframe' => $response['Description']];
            }
            else
            {
                $games = $response['ListGames'];

                foreach ($games as $g) 
                {
                    $gameType = $g['GameType'];
                    $gameCode = $g['GameCode'];
                    $gameName = $g['GameName'];
                    $specials = $g['Specials'];
                    $image = $g['Image1'];

                    $db = DB::select("SELECT game_code
                                    FROM joker_games
                                    WHERE game_code = ?"
                                    ,[$gameCode]);

                    if (sizeOf($db) == 0) 
                    {
                        DB::insert("INSERT INTO joker_games(game_code,game_type,game_name,specials,image)
                        VALUES (?,?,?,?,?)"
                        ,[$gameCode,$gameType,$gameName,$specials,$image]);
                    }
                    else
                    {
                        DB::update("UPDATE joker_games
                                    SET game_type = ?
                                    ,game_name = ?
                                    ,specials = ?
                                    ,image = ?
                                    WHERE game_code = ?",
                                    [$gameType,$gameName,$specials,$image,$gameCode]);
                    }
                }

                $response = ['status' => 1,
                            'iframe' => 'Success'];
            }

            return $response;

        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return $response = ['status' => 0,
                                'error' => 'INTERNAL_ERROR'];
        }
    }

    public static function openGame(Request $request)
    {
        try 
        {
            $brandgamecode = $request->input('type');
            $isMobile = 0;
            $gameUrl = env('JOKER_GAMING_URL');
            $userId = Auth::id();
            //check status
            $status = Auth::user()->status;
            
            if ($status != 'a') 
            {
                return 'Inactive Member!';
            }
            $token = Helper::generateUniqueId();
            $appId = env('JOKER_APPID');
            $language = self::mapLocale();
            // $redirectUrl = '';

            DB::insert('
                INSERT INTO joker_users_token(member_id,token)
                VALUES (?,?)
                ON duplicate key UPDATE
                    token = ?'
                ,[  $userId
                    ,$token
                    ,$token]);

            // $brandgameid = DB::select("SELECT game_code FROM joker_games WHERE id = ? AND status = 1",[$gameId]);
            // $brandgamecode = $brandgameid[0]->game_code ?? '';

            $gamingUrl = $gameUrl.'playGame?token='.$token.'&appID='.$appId.'&gameCode='.$brandgamecode.'&language='.$language.'&mobile='.$isMobile;

            log::debug($gamingUrl);

            $response = ['status' => 1,
                        'iframe' => $gamingUrl];

            return $response;

        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return $response = ['status' => 0,
                                'error' => 'INVALID_OPEN_GAME'];
        }
    }

    public static function convertRawData($data)
    {
        try 
        {
            $rawData = '';
            $secret = env('JOKER_SECRET');
            ksort($data);

            foreach ($data as $key => $value) 
            {
                $rawKey = strtolower($key);
                $rawValue = $value;

                if ($rawData != '') 
                {
                    $rawData = $rawData.'&';
                }

                $rawData = $rawData.$rawKey.'='.$rawValue;
            }

            return $rawData.$secret;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return '';
        }   
    }

    //***********************************
    //  Call from provider
    //***********************************

    public function authToken(Request $request)
    {
        try 
        {
            $appId = $request['appid'];
            $token = $request['token'];

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT b.username, c.balance
                            FROM joker_users_token a
                            LEFT JOIN users b ON a.user_id = b.id
                            LEFT JOIN users_balance c ON c.user_id = b.id
                            WHERE a.token = ?",
                             [$token]);

            if (sizeOf($db) == 0) 
            {
                return ['Status' => 7,
                    'Message'=>'Invalid User'];
            }

            $response = ['Status' => 0,
                    'Message'=>'Success',
                    'Username'=>$db[0]->username,
                    'Balance'=>$db[0]->balance];

            return $response;
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    public function balance(Request $request)
    {
        try
        {
            log::debug('balance');
            log::debug($request);
            $appId = $request['appid'];
            $username = $request['username'];

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;

                $response = [
                    "Status" => 0,
                    "Message" => 'Success',
                    "Balance" => $balance
                ];
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];
            }

            log::debug($response);
            

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    //This API is used for to return money back.
    public function deposit(Request $request)
    {
        try
        {
            $appId = $request['appid'];
            $username = $request['username'];
            $amount = $request['amount'];
            $id = $request['id'];

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            //Insufficient balance
            if($balance < $amount)
            {
                $response = [
                        "Status" => 100,
                        "Message" => "Insufficient fund"
                    ];

                return $response;
            }

            $db = DB::select("SELECT id, amount
                            FROM joker_withdraw
                            WHERE id = ?"
                            ,[$id]);

            if (sizeOf($db) == 0) 
            {
                DB::insert("INSERT INTO joker_withdraw (id,member_id,amount,created_at)
                            VALUES(?,?,?,NOW())"
                            ,[$id,$userId,$amount]);

                //update balance
                $db = DB::update('
                    UPDATE users_balance
                    SET balance = balance + ?
                    WHERE user_id = ?'
                    ,[  $amount
                        ,$userId]);

                $balance = $balance + $amount;

                $response = [
                    "Status" => 0,
                    "Message" => 'Success',
                    "Balance" => $balance
                ];
            }
            else
            {
                $response = [
                        "Status" => 201,
                        "Message" => "Transaction is being processed"
                    ];
            }

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    //This API is used for to player transfer money to play game Fish.
    public function withdraw(Request $request)
    {
        try
        {
            $appId = $request['appid'];
            $username = $request['username'];
            $amount = $request['amount'];
            $id = $request['id'];

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            $db = DB::select("SELECT id, amount
                            FROM joker_deposit
                            WHERE id = ?"
                            ,[$id]);

            if (sizeOf($db) == 0) 
            {
                DB::insert("INSERT INTO joker_deposit (id,member_id,amount,created_at)
                            VALUES(?,?,?,NOW())"
                            ,[$id,$userId,$amount]);

                //update balance
                $db = DB::update('
                    UPDATE users_balance
                    SET balance = balance - ?
                    WHERE user_id = ?'
                    ,[  $amount
                        ,$userId]);

                $balance = $balance - $amount;

                $response = [
                    "Status" => 0,
                    "Message" => 'Success',
                    "Balance" => $balance
                ];
            }
            else
            {
                $response = [
                        "Status" => 201,
                        "Message" => "Transaction is being processed"
                    ];
            }

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    public function debit(Request $request)
    {
        try
        {
            $appId = $request['appid'];
            $username = $request['username'];
            $amount = $request['amount'];
            $extTxnId = $request['id'];
            $txnId = $request['roundid'];
            $gameCode = $request['gamecode'];
            $timestamp = $request['timestamp'];

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            //Insufficient balance
            if($balance < $amount)
            {
                $response = [
                        "Status" => 100,
                        "Message" => "Insufficient fund"
                    ];

                return $response;
            }

            $gameId = self::checkGame($gameCode);

            if ($gameId == '') 
            {
                $response = [
                        "Status" => 4,
                        "Message" => "Invalid parameters"
                    ];

                return $response;
            }

            $db = DB::select("SELECT txn_id, amount
                            FROM joker_debit
                            WHERE txn_id = ?"
                            ,[$txnId]);

            if (sizeOf($db) == 0) 
            {
                DB::insert("INSERT INTO joker_debit (txn_id,member_id,amount,ext_txn_id,game_id,created_at,updated_at)
                            VALUES(?,?,?,?,?,NOW(),NOW())"
                            ,[$txnId,$userId,$amount,$extTxnId,$gameId]);

                //update balance
                $db = DB::update('
                    UPDATE users_balance
                    SET balance = balance - ?
                    WHERE user_id = ?'
                    ,[  $amount
                        ,$userId]);

                $balance = $balance - $amount;

                $response = [
                    "Status" => 0,
                    "Message" => 'Success',
                    "Balance" => $balance
                ];
            }
            else
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Duplicate Debit"
                    ];
            }

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    public function credit(Request $request)
    {
        try
        {
            log::debug($request);
            $appId = $request['appid'];
            $username = $request['username'];
            $gameCode = $request['gamecode'];
            $extTxnId = $request['id'];
            $txnId = $request['roundid'];
            $type = $request['type'];
            $amount = $request['amount'];
            $timestamp = $request['timestamp'];
            $prdId = Provider::Joker;

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            $gameId = self::checkGame($gameCode);

            if ($gameId == '') 
            {
                $response = [
                        "Status" => 4,
                        "Message" => "Invalid parameters"
                    ];

                return $response;
            }
            

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM joker_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);


            $checkDebitExist = DB::select("
                            SELECT txn_id , amount
                            FROM joker_debit
                            WHERE txn_id = ?
                            ",
                            [$txnId]);

            if (sizeOf($checkDebitExist) == 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Debit Does Not Exist"
                    ];

                return $response;
            }

            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Credit Already Exist"
                    ];

                return $response;
            }

            //pair the credit with unpaired debit by roundId
            $debitTxnId = $checkDebitExist[0]->txn_id;
            $debitAmt = $checkDebitExist[0]->amount;

            //calculate PT and COMM
            $db = DB::select('
                SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                FROM users a
                INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                WHERE a.id = ?
                    AND b.prd_id = ?'
                ,[$userId,$prdId]);

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
                    INSERT INTO joker_credit
                    (txn_id,ext_txn_id,type,amount
                    ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                    ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                    ,created_at)
                    VALUES
                    (?,?,?,?
                    ,?,?,?,?
                    ,?,?,?,?
                    ,NOW())'
                    ,[  $txnId,$extTxnId,'c',$amount
                        ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                        ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);

            //update balance
            $db = DB::update('
                UPDATE users_balance
                SET balance = balance + ?
                WHERE user_id = ?'
                ,[  $amount
                    ,$userId]);

            $balance = $balance + $amount;

            $response = [
                "Status" => 0,
                "Message" => 'Success',
                "Balance" => $balance
            ];

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    public function cancel(Request $request)
    {
        try
        {
            log::debug($request);
            $appId = $request['appid'];
            $username = $request['username'];
            $gameCode = $request['gamecode'];
            $extTxnId = $request['id'];
            $txnId = $request['roundid'];
            $type = $request['type'];
            $timestamp = $request['timestamp'];
            $prdId = Provider::Joker;

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            $gameId = self::checkGame($gameCode);

            if ($gameId == '') 
            {
                $response = [
                        "Status" => 4,
                        "Message" => "Invalid parameters"
                    ];

                return $response;
            }
            

            $checkCancelExist = DB::select("
                                SELECT txn_id 
                                FROM joker_credit 
                                WHERE txn_id = ?
                                    AND type = 'x'
                                ",
                                [$txnId]);

            $checkCreditExist = DB::select("
                                SELECT txn_id 
                                FROM joker_credit 
                                WHERE txn_id = ?
                                    AND type = 'c'
                                ",
                                [$txnId]);


            $checkDebitExist = DB::select("
                            SELECT amount 
                            FROM joker_debit
                            WHERE txn_id = ?
                            ",
                            [$txnId]);

            if (sizeOf($checkDebitExist) == 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Debit Does Not Exist"
                    ];

                return $response;
            }

            if (sizeOf($checkCancelExist) != 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Cancel Already Exist"
                    ];

                return $response;
            }

            $amount = $checkDebitExist[0]->amount;

            //pair the credit with unpaired debit by roundId
            $debitAmt = $checkDebitExist[0]->amount;

            //calculate PT and COMM
            $db = DB::select('
                SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                FROM users a
                INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                WHERE a.id = ?
                    AND b.prd_id = ?'
                ,[$userId,$prdId]);

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

            if (sizeOf($checkCreditExist) != 0) 
            {
                DB::update("UPDATE joker_credit
                            SET ext_txn_id = ?,
                            type = ?,
                            amount = ?,
                            tier1_pt_amt = ?,
                            tier2_pt_amt = ?,
                            tier3_pt_amt = ?,
                            tier4_pt_amt = ?,
                            WHERE txn_id = ?",
                            [$extTxnId,
                                'x',
                                $amount,
                                $tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt,$txnId]);
            }
            else
            {
                //insert transaction
                $db = DB::insert('
                        INSERT INTO joker_credit
                        (txn_id,ext_txn_id,type,amount
                        ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                        ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,?,?,?,?
                        ,?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'x',$amount
                            ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                            ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);
            }

            //update balance
            $db = DB::update('
                UPDATE users_balance
                SET balance = balance + ?
                WHERE user_id = ?'
                ,[  $amount
                    ,$userId]);

            $balance = $balance + $amount;

            $response = [
                "Status" => 0,
                "Message" => 'Success',
                "Balance" => $balance
            ];

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    public function bonusWin(Request $request)
    {
        try
        {
            log::debug('bonusWin');
            log::debug($request);
            $appId = $request['appid'];
            $username = $request['username'];
            $gameCode = $request['gamecode'];
            $extTxnId = $request['id'];
            $txnId = $request['roundid'];
            $type = $request['type'];
            $amount = $request['amount'];
            $timestamp = $request['timestamp'];
            $prdId = Provider::Joker;

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            $gameId = self::checkGame($gameCode);

            if ($gameId == '') 
            {
                $response = [
                        "Status" => 4,
                        "Message" => "Invalid parameters"
                    ];

                return $response;
            }
            

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM joker_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);

            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Bonus Already Exist"
                    ];

                return $response;
            }

            DB::insert("INSERT INTO joker_debit (txn_id,member_id,amount,ext_txn_id,game_id,created_at,updated_at)
                            VALUES(?,?,?,?,?,NOW(),NOW())"
                            ,[$txnId,$userId,0,$extTxnId,$gameId]);

            //pair the credit with unpaired debit by roundId
            $debitAmt = 0;

            //calculate PT and COMM
            $db = DB::select('
                SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                FROM users a
                INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                WHERE a.id = ?
                    AND b.prd_id = ?'
                ,[$userId,$prdId]);

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

            $db = DB::insert('
                        INSERT INTO joker_credit
                        (txn_id,ext_txn_id,type,amount
                        ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                        ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,?,?,?,?
                        ,?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'b',$amount
                            ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                            ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);

            //update balance
            $db = DB::update('
                UPDATE users_balance
                SET balance = balance + ?
                WHERE user_id = ?'
                ,[  $amount
                    ,$userId]);

            $balance = $balance + $amount;

            $response = [
                "Status" => 0,
                "Message" => 'Success',
                "Balance" => $balance
            ];

            return $response;
        } 
        catch (\Exception $e) 
        {
            Log::debug($e);
            return ['Status' => 1000,
                    'Message'=>'Internal Error'];
        }
    }

    public function jackpotWin(Request $request)
    {
        try 
        {
            log::debug('jackpotWin');
            log::debug($request);
            $appId = $request['appid'];
            $username = $request['username'];
            $gameCode = $request['gamecode'];
            $extTxnId = $request['id'];
            $txnId = $request['roundid'];
            $type = $request['type'];
            $amount = $request['amount'];
            $timestamp = $request['timestamp'];
            $prdId = Provider::Joker;

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }
            

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM joker_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);

            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Jackpot Already Exist"
                    ];

                return $response;
            }

            $gameId = self::checkGame($gameCode);

            if ($gameId == '') 
            {
                $response = [
                        "Status" => 4,
                        "Message" => "Invalid parameters"
                    ];

                return $response;
            }

            DB::insert("INSERT INTO joker_debit (txn_id,member_id,amount,ext_txn_id,game_id,created_at,updated_at)
                            VALUES(?,?,?,?,?,NOW(),NOW())"
                            ,[$txnId,$userId,0,$extTxnId,$gameId]);

            //pair the credit with unpaired debit by roundId
            $debitAmt = 0;

            //calculate PT and COMM
            $db = DB::select('
                SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                FROM users a
                INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                WHERE a.id = ?
                    AND b.prd_id = ?'
                ,[$userId,$prdId]);

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

            $db = DB::insert('
                        INSERT INTO joker_credit
                        (txn_id,ext_txn_id,type,amount
                        ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                        ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,?,?,?,?
                        ,?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'j',$amount
                            ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                            ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);

            //update balance
            $db = DB::update('
                UPDATE users_balance
                SET balance = balance + ?
                WHERE user_id = ?'
                ,[  $amount
                    ,$userId]);

            $balance = $balance + $amount;

            $response = [
                "Status" => 0,
                "Message" => 'Success',
                "Balance" => $balance
            ];

            return $response;
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public function transaction(Request $request)
    {
        try 
        {
            log::debug('transaction');
            log::debug($request);

            $appId = $request['appid'];
            $username = $request['username'];
            $gameCode = $request['gamecode'];
            $extTxnId = $request['id'];
            $txnId = $request['roundid'];
            $type = $request['type'];
            $debit = $request['amount'];
            $credit = $request['result'];
            $startbalance = $request['startbalance'];
            $endbalance = $request['endbalance'];
            $timestamp = $request['timestamp'];
            $prdId = Provider::Joker;

            if ($request['appid'] != env('JOKER_APPID')) 
            {
                return ['Status'=>1,'Message'=>'Invalid App ID'];
            }

            $db = DB::select("SELECT a.id, b.balance
                            FROM users a
                            LEFT JOIN users_balance b ON a.id = b.user_id
                            WHERE a.username = ?"
                            ,[$username]);

            if (sizeOf($db) != 0) 
            {
                $balance = $db[0]->balance;
                $userId = $db[0]->id;
            }
            else
            {
                $response = [
                        "Status" => 7,
                        "Message" => "Invalid Username or Password"
                    ];

                return $response;
            }

            $gameId = self::checkGame($gameCode);

            if ($gameId == '') 
            {
                $response = [
                        "Status" => 4,
                        "Message" => "Invalid parameters"
                    ];

                return $response;
            }
            

            $checkExist = DB::select("
                                SELECT txn_id 
                                FROM joker_credit 
                                WHERE txn_id = ?
                                ",
                                [$txnId]);

            if (sizeOf($checkExist) != 0) 
            {
                $response = [
                        "Status" => 1000,
                        "Message" => "Jackpot Already Exist"
                    ];

                return $response;
            }

            DB::insert("INSERT INTO joker_debit (txn_id,member_id,amount,ext_txn_id,game_id,created_at,updated_at)
                            VALUES(?,?,?,?,?,NOW(),NOW())"
                            ,[$txnId,$userId,$debit,$extTxnId,$gameId]);

            //pair the credit with unpaired debit by roundId
            $debitAmt = $debit;

            //calculate PT and COMM
            $db = DB::select('
                SELECT b.tier1_pt,b.tier2_pt,b.tier3_pt,b.tier4_pt,c.comm
                FROM users a
                INNER JOIN pt_eff b ON a.admin_id = b.admin_id
                INNER JOIN admin_comm c ON a.admin_id = c.admin_id
                WHERE a.id = ?
                    AND b.prd_id = ?'
                ,[$userId,$prdId]);

            $tier1PT = $db[0]->tier1_pt;
            $tier2PT = $db[0]->tier2_pt;
            $tier3PT = $db[0]->tier3_pt;
            $tier4PT = $db[0]->tier4_pt;
            $tier4Comm = $db[0]->comm;

            $wlAmt = $debitAmt - $credit;

            $tier4PTAmt = $wlAmt * ($tier4PT / 100);
            Helper::removePrecision($tier4PTAmt);

            $tier3PTAmt = $wlAmt * ($tier3PT / 100);
            Helper::removePrecision($tier3PTAmt);

            $tier2PTAmt = $wlAmt * ($tier2PT / 100);
            Helper::removePrecision($tier2PTAmt);

            $tier1PTAmt = $wlAmt - $tier4PTAmt - $tier3PTAmt - $tier2PTAmt;

            $tier4CommAmt = $wlAmt * ($tier4Comm / 100);

            $db = DB::insert('
                        INSERT INTO joker_credit
                        (txn_id,ext_txn_id,type,amount
                        ,tier1_pt,tier2_pt,tier3_pt,tier4_pt
                        ,tier1_pt_amt,tier2_pt_amt,tier3_pt_amt,tier4_pt_amt
                        ,created_at)
                        VALUES
                        (?,?,?,?
                        ,?,?,?,?
                        ,?,?,?,?
                        ,NOW())'
                        ,[  $txnId,$extTxnId,'c',$credit
                            ,$tier1PT,$tier2PT,$tier3PT,$tier4PT
                            ,$tier1PTAmt,$tier2PTAmt,$tier3PTAmt,$tier4PTAmt]);

            $response = [
                "Status" => 0,
                "Message" => 'Success',
                "Balance" => $balance
            ];

            return $response;
            
        } 
        catch (Exception $e) 
        {
            log::debug($e);
        }
    }

    public static function checkGame($gameCode)
    {
        try 
        {
            $game = DB::select("SELECT id
                                FROM joker_games
                                WHERE game_code = ?"
                                ,[$gameCode]);

            if (sizeOf($game) == 0) 
            {
                return '';
            }
            else
            {
                return $game[0]->id;
            }
        } 
        catch (Exception $e) 
        {
            Log::debug($e);
            return '';
        }
    }
}