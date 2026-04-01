<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Admin Login</h2>
        <p class="text-gray-500 text-sm">Đăng nhập để quản trị hệ thống</p>
    </div>

    {{-- Form đăng nhập truyền thống --}}
    <form wire:submit="login">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input wire:model="email" type="email" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-800 @error('email') border-red-500 @enderror">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">Mật khẩu</label>
            <input wire:model="password" type="password" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-800">
            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center justify-between mb-6">
            <label class="flex items-center">
                <input wire:model="remember" type="checkbox" class="form-checkbox text-slate-800">
                <span class="ml-2 text-sm text-gray-600">Ghi nhớ</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-2 px-4 rounded-lg hover:bg-slate-700 transition duration-300 disabled:opacity-50">
            <span wire:loading.remove>Đăng nhập</span>
            <span wire:loading>Đang xử lý...</span>
        </button>
    </form>

    {{-- Section: Social Login (Section 9.3 & 9.4) --}}
    <div class="mt-6">
        <div class="relative flex items-center py-5">
            <div class="flex-grow border-t border-gray-200"></div>
            <span class="flex-shrink mx-4 text-gray-400 text-sm">Hoặc đăng nhập với</span>
            <div class="flex-grow border-t border-gray-200"></div>
        </div>

        <a href="{{ route('google') }}"
           class="flex items-center justify-center w-full px-4 py-2 border border-gray-200 rounded-lg shadow-sm bg-white text-gray-700 hover:bg-gray-50 transition duration-300">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 48 48">
                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"></path>
                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"></path>
                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"></path>
                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"></path>
            </svg>
            <span class="text-sm font-medium">Google Workspace</span>
        </a>
    </div>
</div>
