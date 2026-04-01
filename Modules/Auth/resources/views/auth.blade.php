@extends('layouts.page')

@section('title', 'New Module')

@section('content_header')
        {{--
            khai báo ngôn ngữ:
            tiếng anh: resources/lang/en
            tiếng việt: resources/lang/vi
        --}}
        <h3>{{ __('messages.language') }}</h3>
@stop

@section('content')
<div class="container">
   {{-- gọi component livewire/User/UserList.php --}}
   {{-- @livewire('user.user-list') --}}
</div>

@endsection

@section('css')
@stack('styles')
    {{-- Sử dụng ở component @push(css)<style>...</style>@endpush ở cuối file --}}
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}

@stop

@section('js')
@stack('scripts')
     {{-- Sử dụng ở component @push(js)<script>...</script>@endpush ở cuối file --}}
     {{-- https://www.daterangepicker.com/#examples  --}}

      {{-- <script type="text/javascript">
        $(document).ready(function () {
            console.log("Sử dụng Jquery");
            console.log("Các JS script của component sẻ được ưu tiên tải trước");

        })
        <script> --}}

     <script>
        document.addEventListener("DOMContentLoaded", () => {
            console.log("Lắng nghe sự kiện DOMContentLoaded được gọi trước jquery");
        })
     </script>
@stop
