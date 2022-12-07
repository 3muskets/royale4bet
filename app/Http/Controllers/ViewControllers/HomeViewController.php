<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Helper;
use App\Http\Controllers\UserController;
use Auth;
use DB;
use Log;

class HomeViewController extends Controller
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
        $mainBanner = UserController::getMainBanner();
        $popupBanner = UserController::getPopupBanner();

        return view('home')->with(['mainBanner' => $mainBanner, 'popupBanner' => json_encode($popupBanner)]);
    }

    public function aboutUs()
    {
        return view('aboutus');
    }

    public function responsibleGaming()
    {
        return view('responsible_gaming');
    }

    public function tNC()
    {
        return view('tnc');
    }

    public function privacyPolicy()
    {
        return view('privacy_policy');
    }
}
