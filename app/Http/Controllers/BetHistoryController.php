<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Providers\AASController;
use App\Http\Controllers\Providers\HABAController;
use App\Http\Controllers\Providers\PPController;
use App\Http\Controllers\Providers\WMController;

use DB;
use Auth;
use Log;

class BetHistoryController extends Controller
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

    public static function getProducts(Request $request)
    {
        try
        {
            $userId = Auth::id();

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            if($startDate == NULL)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == '')
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));

            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            $sql = "
                    SELECT '1' AS 'prd_id'
                        ,SUM(a.amount) 'turnover'
                        ,SUM(b.amount - a.amount) 'win_loss'
                    FROM aas_debit a
                    INNER JOIN aas_credit b 
                        ON a.txn_id = b.txn_id
                    WHERE a.member_id= :id1
                        AND a.prd_id = 9
                        AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date OR '' = :start_date1)
                        AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date OR '' = :end_date1)

                    UNION ALL

                    SELECT '2' AS 'prd_id'
                        ,SUM(a.amount) 'turnover'
                        ,SUM(b.amount - a.amount) 'win_loss'
                    FROM haba_debit a
                    INNER JOIN haba_credit b 
                        ON a.txn_id = b.txn_id
                    WHERE a.member_id= :id2
                        AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date2 OR '' = :start_date3)
                        AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date2 OR '' = :end_date3)

                    UNION ALL

                    SELECT '3' AS 'prd_id'
                        ,SUM(a.amount) 'turnover'
                        ,SUM(b.amount - a.amount) 'win_loss'
                    FROM pp_debit a
                    INNER JOIN pp_credit b 
                        ON a.txn_id = b.txn_id
                    WHERE a.member_id= :id3
                        AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date4 OR '' = :start_date5)
                        AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date4 OR '' = :end_date5)

                    UNION ALL

                    SELECT '4' AS 'prd_id'
                        ,SUM(a.amount) 'turnover'
                        ,SUM(b.amount - a.amount) 'win_loss'
                    FROM aas_debit a
                    INNER JOIN aas_credit b 
                        ON a.txn_id = b.txn_id
                    WHERE a.member_id= :id4
                        AND a.prd_id = 208
                        AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date6 OR '' = :start_date7)
                        AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date6 OR '' = :end_date7)
                    ";

            $params = [
                        'id1' => $userId
                        ,'start_date' => $startDate
                        ,'start_date1' => $startDate
                        ,'end_date' => $endDate
                        ,'end_date1' => $endDate
                        ,'id2' => $userId
                        ,'start_date2' => $startDate
                        ,'start_date3' => $startDate
                        ,'end_date2' => $endDate
                        ,'end_date3' => $endDate
                        ,'id3' => $userId
                        ,'start_date4' => $startDate
                        ,'start_date5' => $startDate
                        ,'end_date4' => $endDate
                        ,'end_date5' => $endDate
                        ,'id4' => $userId
                        ,'start_date6' => $startDate
                        ,'start_date7' => $startDate
                        ,'end_date6' => $endDate
                        ,'end_date7' => $endDate
                    ];

            $orderByAllow = [];
            $orderByDefault = '';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            $aryProduct = self::getOptionsProduct();

            foreach($data['results'] as $d)
            {
                $d->prd_name = Helper::getOptionsValue($aryProduct, $d->prd_id);
            }

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::debug($e);

            return [];
        }
    }

    public static function getBetHistory(Request $request)
    {
        try
        {
            $userId = Auth::id();

            $prdId = $request->input('prd_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            
            if($startDate == NULL)
                $startDate = '';
            else
                $startDate = date('Y-m-d 00:00:00',strtotime($startDate));

            if($endDate == '')
                $endDate = '';
            else
                $endDate = date('Y-m-d 23:59:59',strtotime($endDate));

            $page = $request->input('page');
            $orderBy = $request->input('order_by');
            $orderType = $request->input('order_type');

            if($prdId == 1)
            {
                $sql = "
                        SELECT a.txn_id, a.amount 'debit', (a.created_at + INTERVAL 4 HOUR) 'timestamp', b.type, b.amount 'credit', c.game_name
                        FROM aas_debit a
                        LEFT JOIN aas_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN aas_games c
                            ON a.game_id = c.id
                        WHERE a.member_id = :member_id
                            AND a.prd_id = 9
                            AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date OR '' = :start_date1)
                            AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date OR '' = :end_date1)
                        ";
            }
            else if($prdId == 2)
            {
                $sql = "
                        SELECT a.member_id, a.txn_id, a.round_id, a.amount 'debit', (a.created_at + INTERVAL 4 HOUR) 'timestamp', b.type, b.amount 'credit', c.game_name
                        FROM haba_debit a
                        LEFT JOIN haba_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN haba_games c
                            ON a.game_id = c.game_id
                        WHERE a.member_id = :member_id
                            AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date OR '' = :start_date1)
                            AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date OR '' = :end_date1)
                        ";
            }
            else if($prdId == 3)
            {
                $sql = "
                        SELECT a.member_id, a.txn_id, a.round_id, a.amount 'debit', (a.created_at + INTERVAL 4 HOUR) 'timestamp', b.type, b.amount 'credit', c.game_name
                        FROM pp_debit a
                        LEFT JOIN pp_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN pp_games c
                            ON a.game_id = c.id
                        WHERE a.member_id = :member_id
                            AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date OR '' = :start_date1)
                            AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date OR '' = :end_date1)
                        ";
            }
            else
            {
                $sql = "
                        SELECT a.txn_id, a.amount 'debit', (a.created_at + INTERVAL 4 HOUR) 'timestamp', b.type, b.amount 'credit', c.game_name
                        FROM aas_debit a
                        LEFT JOIN aas_credit b
                            ON a.txn_id = b.txn_id
                        LEFT JOIN wm_games c
                            ON a.game_id = c.game_id
                        WHERE a.member_id = :member_id
                            AND a.prd_id = 208
                            AND ((b.created_at + INTERVAL 4 HOUR) >= :start_date OR '' = :start_date1)
                            AND ((b.created_at + INTERVAL 4 HOUR) <= :end_date OR '' = :end_date1)
                        ";
            }

            $params = [
                        'member_id' => $userId
                        ,'start_date' => $startDate
                        ,'start_date1' => $startDate
                        ,'end_date' => $endDate
                        ,'end_date1'=> $endDate
                    ];

            $orderByAllow = ['txn_id', 'timestamp','game_name'];
            $orderByDefault = 'timestamp desc';

            $sql = Helper::appendOrderBy($sql,$orderBy,$orderType,$orderByAllow,$orderByDefault);
             
            $data = Helper::paginateData($sql,$params,$page);

            return Response::make(json_encode($data), 200);
        }
        catch(\Exception $e)
        {
            log::debug($e);

            return [];
        }
    }

    public static function getBetResults(Request $request)
    {
        $prdId = $request->input('prd_id');
        $txnId = $request->input('txn_id');
        $memberId = $request->input('member_id');
        $roundId = $request->input('round_id');

        if($prdId == '1')
        {
            $data = AASController::getEvoBetDetail($request);
        }
        else if($prdId == '2')
        {
            $data = HABAController::getHabaBetDetail($request);
        } 
        else if($prdId == '3' )
        {
            $data = PPController::getPPBetDetail($request);
        }
        else if($prdId == '4')
        {
            $data = WMController::getWmBetDetail($request);
        }

        return $data;
    }

    public static function getOptionsProduct()
    {
        return  [
                ['1', __('Sexy Gaming')]
                ,['2', __('Habanero')]
                ,['3', __('Pragmatic Play')]
                ,['4', __('World Match')]
            ];
    }
}
