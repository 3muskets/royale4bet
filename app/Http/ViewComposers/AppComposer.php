<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

use Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DWController;
use App\Http\Controllers\MemberMessageController;

class AppComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if(Auth::check()) 
        {
            $userBalance = UserController::getBalance();
            $userCurrency = UserController::getCurrency();

            $pendingDWReq = DWController::getPendingCount();

            $unreadMsg = MemberMessageController::getUnreadMsg();

            $view->with(['userBalance' => $userBalance
                ,'userCurrency' => $userCurrency
                ,'pendingDWReq' => $pendingDWReq
                ,'unreadMsg' => $unreadMsg]);
        }
        
    }
}