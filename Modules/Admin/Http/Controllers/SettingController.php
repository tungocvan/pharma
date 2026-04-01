<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index()
    {
        return view('Admin::pages.settings.index');
    }
    public function profile()
    {
        return view('Admin::pages.settings.profile');
    }
}
