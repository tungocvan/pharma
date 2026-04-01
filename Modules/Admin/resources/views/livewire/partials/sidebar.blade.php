@php
    use Modules\Website\Models\Category;

    // Lấy menu từ DB
    $menus = Category::menu()
        ->active()
        ->whereNull('parent_id')
        ->with('children')
        ->sorted()
        ->get();
@endphp

<aside
    class="flex flex-col bg-gray-900 text-white transition-all duration-300 ease-in-out z-40"
    :class="sidebarOpen ? 'w-64' : 'w-20'"
>

    <div class="flex items-center justify-center h-16 bg-gray-900 border-b border-gray-800 shadow-sm transition-all duration-300">

        <div x-show="sidebarOpen" x-transition.opacity.duration.300ms class="flex items-center gap-2 whitespace-nowrap overflow-hidden">
            <span class="text-xl font-bold tracking-wider uppercase text-indigo-500">Master<span class="text-white">Admin</span></span>
        </div>

        <div x-show="!sidebarOpen" x-transition.opacity.duration.300ms class="text-xl font-bold text-indigo-500">
            MA
        </div>

    </div>

    <div class="flex-1 overflow-y-auto overflow-x-hidden py-4 custom-scrollbar">
        <nav class="space-y-1 px-3">

            @foreach($menus as $menu)
                {{-- Check Permission --}}
                @if($menu->can && !auth()->user()->can($menu->can)) @continue @endif

                @php
                    $hasChildren = $menu->children->isNotEmpty();
                    $isActive = ($menu->url && request()->is(ltrim($menu->url, '/') . '*')) ||
                                ($hasChildren && $menu->children->contains(fn($c) => $c->url && request()->is(ltrim($c->url, '/') . '*')));
                @endphp

                @if(empty($menu->url) && $menu->children->isEmpty())
                    <div x-show="sidebarOpen" class="mt-6 mb-2 px-3 text-[10px] font-bold text-gray-500 uppercase tracking-widest transition-opacity duration-300 whitespace-nowrap">
                        {{ $menu->name }}
                    </div>
                    <div x-show="!sidebarOpen" class="mt-4 mb-2 border-t border-gray-700 mx-2"></div>
                    @continue
                @endif


                @if($hasChildren)
                    <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }" class="relative">

                        <button
                            @click="sidebarOpen ? (open = !open) : (sidebarOpen = true, open = true)"
                            type="button"
                            class="group w-full flex items-center py-2.5 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none min-h-[44px]"
                            :class="(sidebarOpen ? 'px-3 ' : 'justify-center px-0 ') + ('{{ $isActive }}' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white')"
                        >
                            @if($menu->icon)
                                <div class="flex-shrink-0 transition-colors duration-200" :class="!sidebarOpen ? '' : 'mr-3'">
                                    <x-icon name="{{ $menu->icon }}" class="h-6 w-6 {{ $isActive ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" />
                                </div>
                            @endif

                            <span x-show="sidebarOpen" class="flex-1 text-left tracking-wide whitespace-nowrap overflow-hidden transition-all duration-200">
                                {{ $menu->name }}
                            </span>

                            <svg x-show="sidebarOpen" :class="open ? 'text-gray-400 rotate-90' : 'text-gray-600'" class="ml-2 flex-shrink-0 h-4 w-4 transform transition-all duration-200 ease-in-out group-hover:text-gray-400" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M6 6L14 10L6 14V6Z" fill="currentColor" />
                            </svg>
                        </button>

                        <div x-show="open && sidebarOpen" x-collapse class="space-y-1 mt-1 border-l border-gray-700 ml-4 pl-2" style="display: none;">
                            @foreach($menu->children as $child)
                                @if($child->can && !auth()->user()->can($child->can)) @continue @endif

                                <a href="{{ url($child->url) }}"
                                   class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors duration-150 truncate
                                   {{ request()->is(ltrim($child->url, '/') . '*') ? 'text-white bg-indigo-600/20' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                                    <span class="w-1.5 h-1.5 rounded-full mr-2 {{ request()->is(ltrim($child->url, '/') . '*') ? 'bg-indigo-400' : 'bg-gray-600 group-hover:bg-gray-400' }}"></span>
                                    {{ $child->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                @else
                    <a href="{{ url($menu->url) }}"
                       class="group flex items-center py-2.5 text-sm font-medium rounded-lg transition-all duration-200 min-h-[44px]"
                       :class="(sidebarOpen ? 'px-3 ' : 'justify-center px-0 ') + ('{{ $isActive }}' ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white')"
                       title="{{ $menu->name }}" {{-- Tooltip native khi thu nhỏ --}}
                    >

                        @if($menu->icon)
                            <div class="flex-shrink-0 transition-colors duration-200" :class="!sidebarOpen ? '' : 'mr-3'">
                                <x-icon name="{{ $menu->icon }}" class="h-6 w-6 {{ $isActive ? 'text-indigo-400' : 'text-gray-500 group-hover:text-gray-300' }}" />
                            </div>
                        @endif

                        <span x-show="sidebarOpen" class="tracking-wide whitespace-nowrap overflow-hidden transition-all duration-200">
                            {{ $menu->name }}
                        </span>
                    </a>
                @endif
            @endforeach

        </nav>
    </div>

    <div class="border-t border-gray-800 p-4 transition-all duration-300">
        <div class="flex items-center" :class="!sidebarOpen ? 'justify-center' : ''">
            <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xs">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
            </div>
            <div x-show="sidebarOpen" class="ml-3 transition-opacity duration-300 whitespace-nowrap overflow-hidden">
                <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500">View Profile</p>
            </div>
        </div>
    </div>
</aside>
