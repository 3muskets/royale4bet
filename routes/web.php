<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
 */

Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('register', 'Auth\RegisterController@showRegisterForm')->name('register');

Route::post('login', 'Auth\LoginController@login')->name('login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::post('register', 'Auth\RegisterController@register')->name('register');

/*
|--------------------------------------------------------------------------
| Home Routes
|--------------------------------------------------------------------------
 */

Route::get('/', 'ViewControllers\HomeViewController@index')->name('/');

Route::get('/game', 'ViewControllers\GameViewController@index')->name('game');
Route::post('/ajax/slots/open_game', 'ViewControllers\GameViewController@openSlotGame');

Route::get('/lobby', 'ViewControllers\LobbyViewController@index')->name('lobby');

Route::get('/bet_history', 'ViewControllers\BetHistoryViewController@index')->name('bet_history');

// Route::get('/bet_history/details', 'ViewControllers\BetHistoryViewController@details')->name('bet_details');

Route::get('/about_us', 'ViewControllers\HomeViewController@aboutUs')->name('about_us');

Route::get('/responsible_gaming', 'ViewControllers\HomeViewController@responsibleGaming')->name('rg');

Route::get('/tnc', 'ViewControllers\HomeViewController@tNC')->name('tnc');

Route::get('/privacy_policy', 'ViewControllers\HomeViewController@privacyPolicy')->name('privacy_policy');

Route::get('/ajax/user_balance', 'UserController@getBalance');

/*
|--------------------------------------------------------------------------
| Member Profile
|--------------------------------------------------------------------------
 */

Route::get('/account', 'ViewControllers\ProfileViewController@index')->name('account');
// Route::get('/my_profile/personal_info', 'ViewControllers\ProfileViewController@personalInfo')->name('personal_info');
// Route::get('/my_profile/change_pw', 'ViewControllers\ProfileViewController@changePW');
// Route::get('/my_profile/bank_info', 'ViewControllers\ProfileViewController@bankInfo');


//ajax
Route::post('ajax/profile/edit_profile', 'ProfileController@editProfile');
Route::post('ajax/profile/change_pw', 'ProfileController@changePassword');
Route::post('/ajax/profile/bank_info', 'ProfileController@editBankInfo');
Route::get('/ajax/profile/user-details', 'ProfileController@getDetails');

/*
|--------------------------------------------------------------------------
| Deposit Withdraw
|--------------------------------------------------------------------------
 */
Route::get('/transfer', 'ViewControllers\DWViewController@transfer');
Route::get('/my_profile/dw/return', 'ViewControllers\DWViewController@return');
Route::get('/my_profile/dw', 'ViewControllers\DWViewController@index');

Route::get('/my_profile/deposit/new', 'ViewControllers\DWViewController@deposit');
Route::get('/my_profile/withdraw/new', 'ViewControllers\DWViewController@withdraw');


//ajax
Route::get('/ajax/getwalletaddress', 'ViewControllers\DWViewController@getWalletAddress');
Route::post('/ajax/dw/create', 'ViewControllers\DWViewController@create');
Route::post('/ajax/dw/cancel', 'ViewControllers\DWViewController@cancel');
Route::post('/ajax/wallet/transfer', 'DWController@transfer');

//ajax
Route::post('/ajax/dw/crypto-create', 'ViewControllers\DWViewController@createCrypto');
Route::get('/ajax/dw/crypto-rate', 'ViewControllers\DWViewController@getCryptoRate');

Route::get('/ajax/bet/products', 'ViewControllers\BetHistoryViewController@getProducts');
Route::get('/ajax/bet/details', 'ViewControllers\BetHistoryViewController@getDetails');

Route::get('/ajax/bet/transaction/details', 'BetHistoryController@getBetResults');

/*
|--------------------------------------------------------------------------
| Promo
|--------------------------------------------------------------------------
 */

Route::get('/promo', 'ViewControllers\PromoViewController@index');

/*
|--------------------------------------------------------------------------
| Referral
|--------------------------------------------------------------------------
 */

Route::get('/my_profile/referral', 'ViewControllers\ProfileViewController@referral');

/*
|--------------------------------------------------------------------------
| Member Message
|--------------------------------------------------------------------------
 */
Route::get('/message/new', 'ViewControllers\MemberMessageViewController@new');
Route::get('/message/inbox', 'ViewControllers\MemberMessageViewController@inbox');
Route::get('/message/sent', 'ViewControllers\MemberMessageViewController@sent');

//ajax
Route::post('/ajax/message/new', 'ViewControllers\MemberMessageViewController@createNewMsg');
Route::post('/ajax/message/inbox/read', 'ViewControllers\MemberMessageViewController@updateUnreadMsg');
Route::get('/ajax/message/inbox', 'ViewControllers\MemberMessageViewController@inboxMsg');
Route::get('/ajax/message/sent', 'ViewControllers\MemberMessageViewController@sentMsg');
/*
|--------------------------------------------------------------------------
| Locale Routes
|--------------------------------------------------------------------------
 */
Route::get('/locale', function () {
	return abort(404);
});
Route::post('/locale', 'Locale@setLocale')->name('locale');


Route::get('/paymentgateway','ViewControllers\PaymentGatewayViewController@index');

Route::get('/orderPaymentf2f','ViewControllers\PaymentGatewayViewController@orderPaymentf2f');
Route::get('/doitnowPayment','ViewControllers\PaymentGatewayViewController@doitnowPayment');

//gs demo
Route::get('/demo', 'Providers\GSController@getBetHistory');
Route::get('/transfer/{gameid}/{type}/{amount}', 'Providers\GSController@makeTransfer');
Route::get('/admin-balance', 'Providers\GSController@checkAgentCredit');
Route::get('/gamelist', 'Providers\GSController@getGameList');

//mega demo
Route::get('/mega-demo', 'Providers\MEGAController@createMember');
Route::get('/get-balance', 'Providers\MEGAController@getBalance');
Route::get('/balance-transfer', 'Providers\MEGAController@balanceTransfer');
Route::get('/balance-transfer-query', 'Providers\MEGAController@balanceTransferQuery');
Route::get('/mega-game', 'Providers\MEGAController@launchGames');
Route::get('/total-report', 'Providers\MEGAController@getTotalReport');

//crowplay demo
Route::get('/cp-login', 'Providers\CPController@login');

//crowplay demo
Route::get('/doitnow/return', 'Providers\DNController@return');
Route::get('/doitnow/callback', 'Providers\DNController@callback');
Route::get('/doitnow/payment', 'Providers\DNController@payment');
Route::get('/doitnow/demo', 'ViewControllers\PaymentGatewayViewController@doitnow');
Route::get('/ajax/dw/doitnow/create', 'Providers\DNController@payment');
Route::get('/ajax/dw/doitnow/check_payment', 'Providers\DNController@checkPaymentAPI');

//918
Route::get('/testme', 'Providers\NOEController@createUser');
Route::get('/noe/create-account', 'Providers\NOEController@createAccount');
Route::get('/noe-game', 'Providers\NOEController@launchGames');
Route::get('/noe-score/{memberId}', 'Providers\NOEController@setMemberScore');
Route::get('/noe-agentscore/{memberId}', 'Providers\NOEController@setAgentScore');
Route::get('/noe-userinfo/{memberId}', 'Providers\NOEController@getUserInfo');
Route::get('/noe-getGameLog/{memberId}', 'Providers\NOEController@getGameLog');

//sbo
Route::get('/sbo-create-agent', 'Providers\SBOController@createAgent');

//ibc
Route::get('/ibc', 'Providers\IBCController@getGame');

//ab
Route::get('/ab', 'Providers\ABController@getGame');
Route::get('/sc', 'Providers\SCController@getGame');

Route::get('/joker', 'Providers\JokerController@openGame');

Route::get('/sa', 'Providers\JokerController@openGame');

Route::get('/xe88', 'Providers\XE88Controller@getGame');

//error cant login
Route::get('/pt', 'Providers\PTController@getGame');

Route::get('/noe', 'Providers\NOEController@getGameLog');

Route::get('/mega', 'Providers\MEGAController@getOrderPage');

Route::get('/pussy', 'Providers\PUSSYController@launchGames');



Route::get('/joker/gameList', 'Providers\JokerController@getGameList');

Route::get('/scr', 'Providers\SCRController@createUser');


