<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

use Auth;
use Log;

class PromoController extends Controller
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

    public static function getPromo()
    {

        $db = DB::select("SELECT promo_id, promo_name, detail, start_date, end_date, image
                            FROM promo_setting WHERE status = 'a'
                            ");

        return $db;

    }
}