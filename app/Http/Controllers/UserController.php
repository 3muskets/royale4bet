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
                SELECT id,bank,acc_no,name
                FROM admin_bank_info 
                WHERE status = "a"
                LIMIT 1'
                );

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
            $userId =  Auth::id();

            //check member is it firsttime user
            $db = DB::select("
                    SELECT id 
                    FROM member_dw
                    WHERE (status = 'a' 
                    OR status = 'p')
                    AND member_id = ?
                    ",[$userId]
                );

            if(sizeof($db) == 0)
            {
                //get promo list
                $db = DB::select("
                        SELECT promo_id,promo_name
                        FROM promo_setting");
            }
            else
            {
                $db = DB::select("
                        SELECT promo_id,promo_name
                        FROM promo_setting
                        WHERE promo_id != 1");                
            }


            return $db;

        }
        catch(\Exception $e)
        {
            Log::Debug($e);

            return [];
        }
    }

}



















