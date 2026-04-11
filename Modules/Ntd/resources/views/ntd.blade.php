@extends('Admin::layouts.master')

@section('title', 'Ntd')

@section('content')
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-slate-900">Ntd module</h1>
        <p class="text-sm text-slate-500">Loai module: shell.</p>

        @include('Ntd::components.placeholder')
    </div>
@endsection