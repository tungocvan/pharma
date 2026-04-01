@extends('layouts.page')

@section('title', 'TEMPLATE HTML BOOSTRAP 5')

@section('content')
    @livewire('users.user-list')
@endsection

@push('css')
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"> --}}
@endpush

@push('js')
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
     <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log("Lắng nghe sự kiện DOMContentLoaded được gọi trước jquery");
        })
     </script>
@endpush
