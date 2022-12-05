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

            $type = $request->input('type');

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

            log::debug($startDate);
            log::debug($endDate);

            $sql = "SELECT id, amount, status, payment_type, type, (created_at + INTERVAL 8 HOUR) as 'created_at'
                    FROM member_dw
                    WHERE member_id = :member_id
                    AND type = :type
                    AND (created_at >= :start_date OR '' = :start_date2)
                    AND (created_at <= :end_date OR '' = :end_date2)
                    ORDER BY created_at DESC";

            $params = [
                        'member_id' => $userId
                        ,'type' => $type
                        ,'start_date' => $startDate
                        ,'start_date2' => $startDate
                        ,'end_date' => $endDate
                        ,'end_date2' => $endDate
                    ];


            $orderByAllow = [];
            $orderByDefault = '';

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
