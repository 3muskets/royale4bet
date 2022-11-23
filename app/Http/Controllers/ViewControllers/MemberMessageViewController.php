<?php

namespace App\Http\Controllers\ViewControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\MemberMessageController;

use DB;
use Log;

class MemberMessageViewController extends Controller
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
    public function new()
    {
        return view('new_message');
    }
    public function inbox()
    {
        return view('inbox_message');
    }
    public function sent()
    {
        return view('sent_message');
    }

    public function createNewMsg(Request $request)
    {
        return MemberMessageController::createNewMsg($request);
    }

    public function inboxMsg(Request $request)
    {
        return MemberMessageController::inboxMsg($request);
    }

    public function sentMsg(Request $request)
    {
        return MemberMessageController::sentMsg($request);
    }

    public function updateUnreadMsg(Request $request)
    {
        return MemberMessageController::updateUnreadMsg($request);
    }

}
