<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class LogicController extends Controller
{
    /**
     * Add an agent to the system
     */
    public function addagent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ]);

        try {
            $agent = new User();
            $agent->name = $request->name;
            $agent->email = $request->email;
            $agent->phone = $request->phone;
            $agent->password = bcrypt('password123'); // Default password
            $agent->account_type = 'agent';
            $agent->status = 'active';
            $agent->email_verified_at = now();
            $agent->save();

            return back()->with('success', 'Agent added successfully. Default password is: password123');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to add agent: ' . $e->getMessage());
        }
    }

    /**
     * View agent details
     */
    public function viewagent($agent)
    {
        try {
            $agentUser = User::where('account_type', 'agent')->findOrFail($agent);
            $title = "Agent Details - " . $agentUser->name;
            
            // Get agent statistics
            $stats = [
                'total_referrals' => User::where('ref_by', $agentUser->id)->count(),
                'active_referrals' => User::where('ref_by', $agentUser->id)->where('status', 'active')->count(),
                'total_deposits' => \App\Models\Deposit::whereHas('user', function($query) use ($agentUser) {
                    $query->where('ref_by', $agentUser->id);
                })->where('status', 'processed')->sum('amount'),
                'commission_earned' => \App\Models\User::where('ref_by', $agentUser->id)->sum('ref_bonus'),
            ];

            return view('admin.agents.view', compact('title', 'agentUser', 'stats'));
        } catch (\Exception $e) {
            return back()->with('error', 'Agent not found.');
        }
    }

    /**
     * Delete an agent
     */
    public function delagent($id)
    {
        try {
            $agent = User::where('account_type', 'agent')->findOrFail($id);
            
            // Check if agent has referrals
            $hasReferrals = User::where('ref_by', $id)->exists();
            
            if ($hasReferrals) {
                return back()->with('error', 'Cannot delete agent with existing referrals. Please reassign referrals first.');
            }

            $agent->delete();
            return back()->with('success', 'Agent deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete agent: ' . $e->getMessage());
        }
    }
}
