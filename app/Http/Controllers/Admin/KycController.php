<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\NewNotification;
use App\Models\Kyc;
use App\Services\NotificationService;
use App\Support\KycDocumentPath;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            $this->deleteDocument($application->frontimg);
            $this->deleteDocument($application->backimg);

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

    public function image($id, $side): BinaryFileResponse
    {
        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $application = Kyc::findOrFail($id);
        $storedPath = $application->storedDocumentPath($side);
        abort_unless($storedPath, 404);

        // Resolve current private storage and every legacy public path format.
        // Run `php artisan kyc:secure-files` to migrate old public files.
        $resolved = KycDocumentPath::resolve($storedPath);
        abort_unless($resolved, 404);

        $absolutePath = $resolved['absolute'];
        $mime = mime_content_type($absolutePath) ?: '';
        abort_unless(in_array($mime, ['image/jpeg', 'image/pjpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'], true), 404);

        $response = response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="kyc-' . $id . '-' . $side . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    private function deleteDocument($storedPath)
    {
        $resolved = KycDocumentPath::resolve($storedPath);
        if (!$resolved) {
            // The database record is still removed by the caller; no file is
            // deleted when it is already missing.
            return;
        }

        if ($resolved['disk'] === 'absolute') {
            @unlink($resolved['absolute']);
            return;
        }

        Storage::disk($resolved['disk'])->delete($resolved['path']);
    }
}
