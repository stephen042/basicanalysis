<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\NewNotification;
use App\Models\Kyc;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class KycController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function processKyc(Request $request)
    {
        $application = Kyc::find($request->kyc_id);
        $user = User::where('id', $application->user_id)->first();

        // will use API key
        if ($request->action == 'Accept') {
            User::where('id', $user->id)
                ->update([
                    'account_verify' => 'Verified',
                ]);
            $application->status = "Verified";
            $application->save();

            try {
                // Send KYC approved notification
                $this->notificationService->sendKycApprovedNotification(
                    $user->id,
                    $application->id,
                    $application->document_type
                );
            } catch (\Exception $e) {
                Log::error('Failed to send KYC approved notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'kyc_id' => $application->id,
                    'document_type' => $application->document_type
                ]);
            }
        } else {
            if (Storage::disk('public')->exists($application->frontimg) and Storage::disk('public')->exists($application->backimg)) {
                Storage::disk('public')->delete($application->frontimg);
                Storage::disk('public')->delete($application->backimg);
            }

            // Update the user verification status
            $user->account_verify = 'Rejected';
            $user->save();

            try {
                // Send KYC rejected notification
                $this->notificationService->sendKycRejectedNotification(
                    $user->id,
                    $application->id,
                    $application->document_type,
                    $request->message ?? null
                );
            } catch (\Exception $e) {
                Log::error('Failed to send KYC rejected notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'kyc_id' => $application->id,
                    'document_type' => $application->document_type,
                    'reason' => $request->message ?? 'No reason provided'
                ]);
            }

            // delete the application form database so user can resubmit application
            $application->delete();
        }

        try {
            Mail::to($user->email)->send(new NewNotification($request->message, $request->subject, $user->name));
        } catch (\Exception $e) {
            Log::error('Failed to send KYC decision email to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'kyc_id' => $application->id,
                'user_email' => $user->email,
                'action' => $request->action
            ]);
        }

        return redirect()->route('kyc')->with('success', 'Action Sucessful!');
    }
}
