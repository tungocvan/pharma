<?php

namespace Modules\Admin\Livewire\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Header extends Component
{
    public function logout()
    {
        Auth::guard('admin')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function render()
    {
        return view('Admin::livewire.partials.header', [
            'user' => Auth::guard('admin')->user()
        ]);
    }
}
