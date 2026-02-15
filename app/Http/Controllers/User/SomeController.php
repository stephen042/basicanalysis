<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class SomeController extends Controller
{
    /**
     * Change user theme preference
     */
    public function changetheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        try {
            $user = User::find(Auth::id());
            
            if (!$user) {
                return back()->with('error', 'User not found.');
            }

            // Update user theme preference
            $user->theme = $request->theme;
            $user->save();

            return back()->with('success', 'Theme changed successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to change theme: ' . $e->getMessage());
        }
    }
}
