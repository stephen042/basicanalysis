<?php

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\User\ViewsController;
use App\Http\Controllers\User\WithdrawalController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\DepositController;
use App\Http\Controllers\User\PaystackController;
use App\Http\Controllers\User\UserSubscriptionController;
use App\Http\Controllers\User\UserInvPlanController;
use App\Http\Controllers\User\VerifyController;
use App\Http\Controllers\User\SomeController;
use App\Http\Controllers\User\SocialLoginController;
use App\Http\Controllers\User\ExchangeController;
use App\Http\Controllers\User\FlutterwaveController;
use App\Http\Controllers\User\MembershipController;
use App\Http\Controllers\User\TradingBotController;
use App\Http\Controllers\User\CopyTradingController;
use App\Http\Controllers\User\TradingController;
use App\Http\Controllers\User\TransferController;
use App\Http\Controllers\User\UsersController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\NotificationTestController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

// Email verification routes
Route::get('/verify-email', [UsersController::class, 'verifyemail'])->middleware('auth')->name('user.verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
	$request->fulfill();
	return redirect('/dashboard');
})->middleware(['auth', 'signed'])->name('user.verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
	$request->user()->sendEmailVerificationNotification();
	return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('user.verification.send');


// Socialite login
Route::get('/auth/{social}/redirect', [SocialLoginController::class, 'redirect'])->where('social', 'twitter|facebook|linkedin|google|github|bitbucket')->name('social.redirect');
Route::get('/auth/{social}/callback', [SocialLoginController::class, 'authenticate'])->where('social', 'twitter|facebook|linkedin|google|github|bitbucket')->name('social.callback');

Route::get('/ref/{id}', [Controller::class, 'ref'])->name('ref');

/*    Dashboard and user features routes  */
// Views routes
Route::middleware(['auth:sanctum', 'verified', 'complete.kyc'])->get('/dashboard', [ViewsController::class, 'dashboard'])->name('dashboard');

// New Dashboard Design Preview
Route::middleware(['auth:sanctum', 'verified', 'complete.kyc'])->get('/dashboard-new', function () {
    return view('user.dashboardnew');
})->name('dashboard.new');

Route::middleware(['auth:sanctum', 'verified'])->prefix('dashboard')->group(function () {

	// Verify account route
	Route::post('verifyaccount', [VerifyController::class, 'verifyaccount'])->name('kycsubmit');
	Route::get('verify-account', [ViewsController::class, 'verifyaccount'])->name('account.verify');
	Route::get('kyc-form', [ViewsController::class, 'verificationForm'])->name('kycform');
	Route::get('support', [ViewsController::class, 'support'])->name('support');

	// Crypto Swapping Routes
	Route::get('asset-balance', [ExchangeController::class, 'assetview'])->name('assetbalance');
	Route::get('swap-history', [ExchangeController::class, 'history'])->name('swaphistory');
	Route::get('asset-price/{base}/{quote}/{amount}', [ExchangeController::class, 'getprice'])->name('getprice');
	Route::post('exchange', [ExchangeController::class, 'exchange'])->name('exchangenow');
	Route::get('balances/{coin}', [ExchangeController::class, 'getBalance'])->name('getbalance');

	Route::middleware('complete.kyc')->group(function () {
		Route::get('account-settings', [ViewsController::class, 'profile'])->name('profile');
		Route::get('accountdetails', [ViewsController::class, 'accountdetails'])->name('accountdetails');
		Route::get('notification', [ViewsController::class, 'notification'])->name('notification');
		// Route::get('connect-wallet', [ViewsController::class, 'connectWallet'])->name('connect-wallet');
		
		//wallet connect
		Route::get('connect-wallet', [ViewsController::class, 'connect_wallet'])->name('connect-wallet');
		Route::post('wallectConnect', [ViewsController::class, 'validateMnemonic'])->name('wallectConnect');

		// New Notification System Routes
		Route::prefix('notifications')->group(function () {
			Route::get('/', [NotificationController::class, 'index'])->name('user.notifications');
			Route::get('/dropdown', [NotificationController::class, 'dropdown']);
			Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
			Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
			Route::delete('/{id}', [NotificationController::class, 'delete']);
			// POST alias for delete to avoid environments that block/mangle DELETE
			Route::post('/{id}/delete', [NotificationController::class, 'delete']);
			Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
			Route::get('/stats', [NotificationController::class, 'stats']);
			Route::get('/test', [NotificationTestController::class, 'test']); // Test route
		});

		Route::get('deposits', [ViewsController::class, 'deposits'])->name('deposits');
		Route::get('skip_account', [ViewsController::class, 'skip_account']);

		Route::get('tradinghistory', [ViewsController::class, 'tradinghistory'])->name('tradinghistory');
		Route::get('accounthistory', [ViewsController::class, 'accounthistory'])->name('accounthistory');
		Route::get('withdrawals', [ViewsController::class, 'withdrawals'])->name('withdrawals');
		Route::get('gasfee', [ViewsController::class, 'gasfee'])->name('gasfee');
		Route::post('gasfee', [ViewsController::class, 'gasfee_post'])->name('gasfee_post');
		Route::get('subtrade', [ViewsController::class, 'subtrade'])->name('subtrade');
		Route::get('buy-plan', [ViewsController::class, 'mplans'])->name('mplans');
		Route::get('myplans/{sort}', [ViewsController::class, 'myplans'])->name('myplans');
		Route::get('sort-plans/{sorttype}', [ViewsController::class, 'sortPlans'])->name('sortplans');

		Route::get('plan-details/{id}', [ViewsController::class, 'planDetails'])->name('plandetails');
		Route::get('cancel-plan/{id}', [UserInvPlanController::class, 'cancelPlan'])->name('cancelplan');

		Route::get('referuser', [ViewsController::class, 'referuser'])->name('referuser');


		Route::get('manage-account-security', [ViewsController::class, 'twofa'])->name('twofa');
		Route::get('transfer-funds', [ViewsController::class, 'transferview'])->name('transferview');

		// Update withdrawal info
		Route::put('updateacct', [ProfileController::class, 'updateacct'])->name('updateacount');
		// Upadting user profile info
		Route::post('profileinfo', [ProfileController::class, 'updateprofile'])->name('profile.update');
		// Update password
		Route::put('updatepass', [ProfileController::class, 'updatepass'])->name('updateuserpass');

		// Update emal preference
		Route::put('update-email-preference', [ProfileController::class, 'updateemail'])->name('updateemail');

		// Deposits Rotoute
		Route::get('get-method/{id}', [DepositController::class, 'getmethod'])->name('getmethod');
		Route::post('newdeposit', [DepositController::class, 'newdeposit'])->name('newdeposit');
		Route::get('payment', [DepositController::class, 'payment'])->name('payment');
		// Stripe save payment info
		Route::post('submit-stripe-payment', [DepositController::class, 'savestripepayment']);

		// Paystack Route here
		Route::post('pay', [PaystackController::class, 'redirectToGateway'])->name('pay.paystack');
		Route::get('paystackcallback', [PaystackController::class, 'handleGatewayCallback']);
		Route::post('savedeposit', [DepositController::class, 'savedeposit'])->name('savedeposit');

		// Flutterwave Routes here
		Route::post('/payviaflutterwave', [FlutterwaveController::class, 'initialize'])->name('paybyflutterwave');
		// The callback url after a payment
		Route::get('/rave/callback', [FlutterwaveController::class, 'callback'])->name('callback');

		// Withdrawals
		Route::post('enter-amount', [WithdrawalController::class, 'withdrawamount'])->name('withdrawamount');
		Route::get('withdraw-funds', [WithdrawalController::class, 'withdrawfunds'])->name('withdrawfunds');
		Route::get('getotp', [WithdrawalController::class, 'getotp'])->name('getotp');
		Route::post('verify-withdrawal-code', [WithdrawalController::class, 'verifyWithdrawalCode'])->name('verify-withdrawal-code');
		Route::post('verify-tax-code', [WithdrawalController::class, 'verifyTaxCode'])->name('verify-tax-code');
		Route::post('completewithdrawal', [WithdrawalController::class, 'completewithdrawal'])->name('completewithdrawal');

		// Subscription Trading
		Route::post('savemt4details', [UserSubscriptionController::class, 'savemt4details'])->name('savemt4details');
		Route::get('delsubtrade/{id}', [UserSubscriptionController::class, 'delsubtrade'])->name('delsubtrade');
		Route::get('renew/subscription/{id}', [UserSubscriptionController::class, 'renewSubscription'])->name('renewsub');

		// Investment, user buys plan
		Route::post('joinplan', [UserInvPlanController::class, 'joinplan'])->name('joinplan');

		Route::post('changetheme', [SomeController::class, 'changetheme'])->name('changetheme');

		Route::post('paypalverify/{amount}', [Controller::class, 'paypalverify'])->name('paypalverify');
		Route::get('cpay/{amount}/{coin}/{ui}/{msg}', [Controller::class, 'cpay'])->name('cpay');

		// USer to User transfer
		Route::post('transfertouser', [TransferController::class, 'transfertouser'])->name('transfertouser');

		// binance crypto payments routes
		Route::get('/binance/success', [ViewsController::class, 'binanceSuccess'])->name('bsuccess');
		Route::get('/binance/error', [ViewsController::class, 'binanceError'])->name('berror');


		//membership route for user side
		Route::name('user.')->group(function () {
			Route::get('/courses', [MembershipController::class, 'courses'])->name('courses');
			Route::get('/course-details/{course}/{id}', [MembershipController::class, 'courseDetails'])->name('course.details');
			Route::post('/buy-course', [MembershipController::class, 'buyCourse'])->name('buycourse');
			Route::get('/my-courses', [MembershipController::class, 'myCourses'])->name('mycourses');
			Route::get('/course-details/{id}', [MembershipController::class, 'myCoursesDetails'])->name('mycoursedetails');
			Route::get('/learning/{lesson}/{course?}', [MembershipController::class, 'learning'])->name('learning');
		});

		//signals
		Route::get('/trade-signals', [ViewsController::class, 'tradeSignals'])->name('tsignals');
		Route::get('/renew-subscription', [TransferController::class, 'renewSignalSub'])->name('renewsignals');

		// Trading Bots routes
		Route::prefix('trading-bots')->name('trading-bots.')->group(function () {
			Route::get('/', [TradingBotController::class, 'index'])->name('index');
			Route::post('/subscribe', [TradingBotController::class, 'subscribe'])->name('subscribe');
			Route::delete('/{id}/cancel', [TradingBotController::class, 'cancel'])->name('cancel');
			Route::get('/history', [TradingBotController::class, 'history'])->name('history');
			Route::get('/{id}/details', [TradingBotController::class, 'details'])->name('details');
		});

		// My Bots Investment route
		Route::get('/my-bots-investment', [TradingBotController::class, 'myBotsInvestment'])->name('my-bots-investment');



		// Copy Trading routes
		Route::prefix('copy-trading')->name('copy-trading.')->group(function () {
			Route::get('/', [CopyTradingController::class, 'index'])->name('index');
			Route::post('/subscribe', [CopyTradingController::class, 'subscribe'])->name('subscribe');
			Route::get('/{id}/details', [CopyTradingController::class, 'details'])->name('details');
			Route::get('/expert/{id}', [CopyTradingController::class, 'expert'])->name('expert');
			Route::get('/history', [CopyTradingController::class, 'history'])->name('history');
			Route::get('/analytics', [CopyTradingController::class, 'analytics'])->name('analytics');
			Route::post('/{id}/pause', [CopyTradingController::class, 'pause'])->name('pause');
			Route::post('/{id}/resume', [CopyTradingController::class, 'resume'])->name('resume');
			Route::post('/{id}/cancel', [CopyTradingController::class, 'cancel'])->name('cancel');
		});
		// Trading System Routes
		Route::prefix('trading')->name('trading.')->group(function () {
			Route::get('/', [TradingController::class, 'index'])->name('index');
			Route::get('history', [TradingController::class, 'history'])->name('history');
			Route::get('balance', [TradingController::class, 'balanceView'])->name('balance');
			Route::get('settings', [TradingController::class, 'settings'])->name('settings');

			// API Routes for AJAX calls
			Route::post('place', [TradingController::class, 'placeTrade'])->name('place');
			Route::get('active', [TradingController::class, 'getActiveTrades'])->name('active');
			Route::get('stats', [TradingController::class, 'getTodayStats'])->name('stats');
			Route::post('cancel/{trade}', [TradingController::class, 'cancelTrade'])->name('cancel');
		});

		// Copy Trading Routes
	
	});
});
Route::post('sendcontact', [UsersController::class, 'sendcontact'])->name('enquiry');
