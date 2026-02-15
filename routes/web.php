<?php

use App\Http\Controllers\Admin\ClearCacheController;
use Illuminate\Support\Facades\Route;
use App\Models\Settings;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Illuminate\Support\Facades\Artisan;

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

// HTTP-based Cron Job Route for Shared Hosting
Route::get('/cron/run-scheduler/{token}', function ($token) {
    // Check if cron secret token is configured
    $cronSecret =  env('CRON_SECRET_TOKEN');
    
    if (!$cronSecret) {
        return response()->json([
            'status' => 'error',
            'message' => 'Cron secret not configured'
        ], 500);
    }
    
    // Verify token
    if ($token !== $cronSecret) {
        \Log::warning('Unauthorized cron access attempt', [
            'ip' => request()->ip(),
            'token_provided' => $token,
            'timestamp' => now()
        ]);
        
        return response()->json([
            'status' => 'error', 
            'message' => 'Unauthorized'
        ], 403);
    }
    
    try {
        // Log the cron execution
        \Log::info('Cron scheduler triggered via HTTP', [
            'ip' => request()->ip(),
            'timestamp' => now()
        ]);
        
        // Run the Laravel scheduler
        $output = '';
        Artisan::call('schedule:run', [], $output);
        $schedulerOutput = Artisan::output();
        
        // Log successful execution
        \Log::info('Scheduler executed successfully', [
            'output' => $schedulerOutput,
            'timestamp' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Scheduler executed successfully',
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'output' => $schedulerOutput
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Cron scheduler execution failed', [
            'error' => $e->getMessage(),
            'timestamp' => now()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Scheduler execution failed: ' . $e->getMessage()
        ], 500);
    }
})->name('cron.run-scheduler');

require __DIR__ . '/home.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';
require __DIR__ . '/botman.php';

