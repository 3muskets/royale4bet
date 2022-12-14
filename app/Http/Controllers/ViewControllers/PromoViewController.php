<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Helper;
use App\Http\Controllers\PromoController;
use Auth;
use DB;
use Log;

class PromoViewController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = PromoController::getPromo();

        return view('promo')->with(['promo' => $data]);
    }
}
