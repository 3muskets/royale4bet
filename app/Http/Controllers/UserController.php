<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Auth;
use Log;

class UserController extends Controller
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
    public static function getBalance()
    {
        try
        {
            $userId =  Auth::id();

            $db = DB::select('
                SELECT available 
                FROM member_credit 
                WHERE member_id = ? 
                LIMIT 1'
                ,[$userId]);

            return $db[0]->available;
        }
        catch(\Exception $e)
        {
            return 0;
        }
    }

    public static function getCurrency()
    {
        try
        {
            $userId =  Auth::id();

            $db = DB::select('
                SELECT b.currency_cd
                FROM member a
                INNER JOIN admin_currency b ON a.admin_id = b.admin_id
                WHERE a.id = ?
                LIMIT 1'
                ,[$userId]);

            return $db[0]->currency_cd;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function getRegCd()
    {
        try
        {
            $userId = Auth::id();

            $db = DB::select("
                    SELECT a.reg_cd
                    FROM admin a
                    LEFT JOIN member b
                    ON a.admin_id = b.admin_id
                    WHERE b.id = ?"
                    ,[$userId]
                );

            return $db[0]->reg_cd;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function getMemberDetails()
    {
        try
        {
            $userId =  Auth::id();

            $db = DB::select('
                SELECT username, first_name, last_name, birthdate, mobile, email
                FROM member
                WHERE id = ?
                LIMIT 1'
                ,[$userId]);

            foreach($db as $d)
            {
                if ($d->first_name == null)
                {
                    $d->first_name = '-';
                }

                if ($d->last_name == null)
                {
                    $d->last_name = '';
                }
            }

            return $db;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }

    public static function getBankDetails()
    {
        try
        {
            $userId =  Auth::id();

            $db = DB::select('
                SELECT bank, acc_no, name
                FROM member_bank_info
                WHERE member_id = ?
                LIMIT 1'
                ,[$userId]);

            return $db;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }


    public static function getAdminBankInfo()
    {
        try
        {
            $userId =  Auth::id();

              //temporary limit 1 
            $db = DB::select('
                SELECT id,bank,acc_no,name,min_deposit_amt, max_deposit_amt
                FROM admin_bank_info 
                WHERE status = "a"
                '
                );

            foreach($db as $d)
            {
                $d->bank_img = Helper::getOptionsValue(self::getBankArray(), $d->id);
            }

            return $db;
        }
        catch(\Exception $e)
        {
            return '';
        }
    }


    public static function getPromoList()
    {
        try
        {
            $userId = Auth::id();

            $todayDate = NOW();
            $todayStartDate = date('Y-m-d 00:00:00',strtotime($todayDate. '+8 hours'));
            $todayEndDate = date('Y-m-d 23:59:59',strtotime($todayDate. '+8 hours'));
            $prevWeekDate = date('Y-m-d 00:00:00',strtotime($todayDate. '-7days +8 hours'));
            $prevMonthDate = date('Y-m-d 00:00:00',strtotime($todayDate. '-1months +8 hours'));

            //check have pending promo or not
            $db = DB::select("
                SELECT id
                FROM member_promo_turnover
                WHERE member_id = ? AND
                status = 'p'
                ",[$userId]
            );


            if(sizeof($db) != 0)
            {
                return [];
            }


            $db = DB::select("
                SELECT *
                FROM 
                (
                    SELECT 
                    a.*
                    FROM promo_setting a
                    LEFT JOIN                     
                    (SELECT promo_id,MAX(created_at) 'created_at',member_id,status
                     FROM member_dw 
                     WHERE member_id = ?
                     GROUP BY promo_id,member_id,status
                    ) AS b 
                    ON a.promo_id = b.promo_id
                    WHERE a.type = 'd' AND a.status = 'a'
                    AND a.start_date <= ? AND a.end_date >= ?
                    AND (b.status = 'a' OR b.status IS NULL)
                    AND ((b.created_at+INTERVAL 8 HOUR) < ? OR b.created_at IS NULL)
                    GROUP BY a.promo_id
                    UNION ALL 
                    SELECT 
                    a.*
                    FROM promo_setting a
                    LEFT JOIN                     
                    (SELECT promo_id,MAX(created_at) 'created_at',member_id,status
                     FROM member_dw 
                     WHERE member_id = ?
                     GROUP BY promo_id,member_id,status
                    ) AS b 
                    ON a.promo_id = b.promo_id
                    WHERE a.type = 'w' AND a.status = 'a'
                    AND a.start_date <= ? AND a.end_date >= ?
                    AND (b.status = 'a' OR b.status IS NULL)
                    AND ((b.created_at+INTERVAL 8 HOUR) < ? OR b.created_at IS NULL)
                    GROUP BY a.promo_id
                    UNION ALL 
                    SELECT 
                    a.*
                    FROM promo_setting a
                    LEFT JOIN                     
                    (SELECT promo_id,MAX(created_at) 'created_at',member_id,status
                     FROM member_dw 
                     WHERE member_id = ?
                     GROUP BY promo_id,member_id,status
                    ) AS b 
                    ON a.promo_id = b.promo_id
                    WHERE a.type = 'm' AND a.status = 'a'
                    AND a.start_date <= ? AND a.end_date >= ?
                    AND (b.status = 'a' OR b.status IS NULL)
                    AND ((b.created_at+INTERVAL 8 HOUR) < ? OR b.created_at IS NULL)
                    GROUP BY a.promo_id
                    UNION ALL 
                    SELECT 
                    a.*
                    FROM promo_setting a
                    LEFT JOIN                     
                    (SELECT promo_id,MAX(created_at) 'created_at',member_id,status
                     FROM member_dw 
                     WHERE member_id = ?
                     GROUP BY promo_id,member_id,status
                    ) AS b 
                    ON a.promo_id = b.promo_id
                    WHERE a.type = 'f' AND a.status = 'a'
                    AND a.start_date <= ? AND a.end_date >= ?
                    GROUP BY a.promo_id
                ) as a 
                ORDER BY a.promo_id
                ",[
                    $userId
                   ,$todayStartDate
                   ,$todayStartDate
                   ,$todayStartDate
                   ,$userId
                   ,$todayStartDate
                   ,$todayStartDate
                   ,$prevWeekDate
                   ,$userId
                   ,$todayStartDate
                   ,$todayStartDate
                   ,$prevMonthDate
                   ,$userId
                   ,$todayStartDate
                   ,$todayStartDate
                ]);



            return $db;

        }
        catch(\Exception $e)
        {
            Log::debug($e);

            return [];
        }
    }

    public static function getBankArray()
    {
        return  [
            ['1', 'cimb']
            ,['2', 'pbb']
            ,['3', 'maybank']
            ,['4', 'rhb']
            ,['5', 'hlb']
            ,['6', 'ambank']
            ,['7', 'affin']
            ,['8', 'alliance']
            ,['9', 'ocbc']

        ];
    }

}



















