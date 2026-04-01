<?php

namespace Modules\Auth\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
         $this->middleware('permission:auth-list|auth-create|auth-edit|auth-delete', ['only' => ['index','show']]);
         $this->middleware('permission:auth-create', ['only' => ['create','store']]);
         $this->middleware('permission:auth-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:auth-delete', ['only' => ['destroy']]);
    }
    public function index()
    {
        return view('Auth::auth');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
