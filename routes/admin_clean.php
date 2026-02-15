<?php

use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\ManageUsersController;
use App\Http\Controllers\Admin\ManageDepositController;
use App\Http\Controllers\Admin\ManageWithdrawalController;
use App\Http\Controllers\Admin\InvPlanController;
use App\Http\Controllers\Admin\ManageAdminController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\FrontendController;
use App\Http\Controllers\Admin\Settings\AppSettingsController;
use App\Http\Controllers\Admin\Settings\ReferralSettings;
use App\Http\Controllers\Admin\Settings\PaymentController;
use App\Http\Controllers\Admin\Settings\SubscriptionSettings;
use App\Http\Controllers\Admin\IpaddressController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\ClearCacheController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\ManageAssetController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\SignalProvderController;
use App\Http\Controllers\Admin\TopupController;
use App\Http\Controllers\Admin\TradingAccountController;
use App\Http\Controllers\Admin\TradingBotController;
use App\Http\Controllers\Admin\TradingAssetController;
use App\Http\Controllers\Admin\TradingPaymentController;
use App\Http\Controllers\Admin\LogicController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TradingDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
	Route::get('remedylogin', [LoginController::class, 'showLoginForm'])->name('adminloginform')->middleware('adminguest');
	Route::post('login', [LoginController::class, 'adminlogin'])->name('adminlogin');
	Route::post('logout', [LoginController::class, 'adminlogout'])->name('adminlogout');
	Route::get('dashboard', [LoginController::class, 'validate_admin'])->name('validate_admin');
});

// Forgot password route
Route::post('admin/forgot-password', [ForgotPasswordController::class, 'email'])->name('admin.password.email');

Route::prefix('admin')->group(function () {
	Route::get('forgot-password', [ForgotPasswordController::class, 'showEmailForm'])->name('admin.forgotpassword');
	Route::post('forgot-password', [ForgotPasswordController::class, 'email'])->name('admin.password.email');
	Route::get('reset-password/{token}', [ForgotPasswordController::class, 'showresetpasswordform'])->name('admin.password.reset');
	Route::post('reset-password', [ForgotPasswordController::class, 'resetpassword'])->name('admin.password.update');
});

Route::middleware(['isadmin', '2fa'])->prefix('admin')->group(function () {
	Route::get('/', [HomeController::class, 'admin'])->name('admin');
	Route::get('demo', [HomeController::class, 'demo'])->name('demo');
	Route::get('adminDarkMode', [HomeController::class, 'adminDarkMode'])->name('adminDarkMode');
	Route::get('adminLightMode', [HomeController::class, 'adminLightMode'])->name('adminLightMode');
	
	// Basic admin routes will be added here as needed
});
// Everything About Admin Route ends here
