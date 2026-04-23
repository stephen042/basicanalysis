<?php

namespace App\Http\Livewire\User;

use App\Models\Signal;
use App\Models\Settings; // Assuming you have a Settings model for the currency
use Livewire\Component;

class SubscribeToSignal extends Component
{
    public $hasSubscribe = false;
    public $inviteLink;

    public function render()
    {
        return view('livewire.user.subscribe-to-signal', [
            'signals' => Signal::where('status', 'active')->get(),
            'settings' => Settings::first(), // Fetch currency and other global settings
        ]);
    }
}