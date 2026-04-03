<?php

namespace Modules\Ntd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NtdController extends Controller
{
    public function __construct()
    {
       // $this->middleware('permission:ntd-list|ntd-create|ntd-edit|ntd-delete', ['only' => ['index','show']]);
       // $this->middleware('permission:ntd-create', ['only' => ['create','store']]);
       // $this->middleware('permission:ntd-edit', ['only' => ['edit','update']]);
       // $this->middleware('permission:ntd-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('Ntd::ntd');
    }
}