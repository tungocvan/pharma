@props(['menu'])

<li data-id="{{ $menu->id }}" class="relative">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-3 flex items-center justify-between group hover:border-indigo-300 transition-colors">

        <div class="flex items-center gap-3 flex-1">
            <div class="drag-handle cursor-move text-gray-400 hover:text-indigo-600 p-1" title="Kéo để sắp xếp">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
            </div>

            <div class="flex items-center gap-3">
                <div class="h-8 w-8 rounded bg-gray-50 flex items-center justify-center text-gray-500 border border-gray-100">
                    @if($menu->icon)
                        <x-icon name="{{ $menu->icon }}" class="h-5 w-5" />
                    @else
                        <span class="text-xs font-bold text-gray-300">#</span>
                    @endif
                </div>

                <div>
                    <div class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        {{ $menu->name }}
                        @if(empty($menu->url))
                            <span class="bg-gray-100 text-gray-600 text-[10px] px-1.5 py-0.5 rounded border border-gray-200 uppercase">Section</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 font-mono">{{ $menu->url ?? '---' }}</div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">

            <div class="flex items-center gap-1 text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                @if($menu->can)
                    <svg class="w-3 h-3 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span class="ml-1">{{ $menu->can }}</span>
                @else
                    <span class="text-green-600">Public</span>
                @endif
            </div>

            <button wire:click="toggleStatus({{ $menu->id }})" title="Ẩn/Hiện" class="{{ $menu->is_active ? 'text-green-500' : 'text-gray-300' }} hover:scale-110 transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            </button>

            <button wire:click="duplicate({{ $menu->id }})" wire:loading.attr="disabled" title="Nhân bản (Copy)" class="text-teal-500 hover:text-teal-700 p-1 hover:bg-teal-50 rounded">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 01-2-2V5a2 2 0 012-2h4.586" />
                </svg>
            </button>

            <a href="{{ route('admin.menus.edit', $menu->id) }}" title="Chỉnh sửa" class="text-indigo-600 hover:text-indigo-800 p-1 hover:bg-indigo-50 rounded">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </a>

            <button wire:confirm="Xóa menu này?" wire:click="delete({{ $menu->id }})" title="Xóa" class="text-red-400 hover:text-red-600 p-1 hover:bg-red-50 rounded">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </div>

    <ul class="menu-list pl-8 mt-2 space-y-2 border-l-2 border-gray-100 ml-4">
        @foreach($menu->children as $child)
            <x-menu-item :menu="$child" />
        @endforeach
    </ul>
</li>
