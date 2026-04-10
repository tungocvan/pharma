<div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">

    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Hoạt chất</h1>
            <p class="mt-1 text-sm text-gray-500">Quản lý danh sách hoạt chất và phân loại dược phẩm.</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button class="btn-secondary">Export</button>

            <button wire:click="$set('showImportModal', true)" class="btn-secondary">
                Import
            </button>

            <button class="btn-primary">
                + Thêm mới
            </button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden relative">

            <!-- LOADING -->
            <div wire:loading.flex
                 class="absolute inset-0 bg-white/60 z-20 items-center justify-center backdrop-blur-[1px]">
                <svg class="animate-spin h-5 w-5 text-indigo-600"></svg>
            </div>

            @if(count($selected) > 0)

                <!-- BULK BAR -->
                <div class="p-3 bg-indigo-50 flex justify-between items-center">

                    <div class="flex items-center gap-3">
                        <button wire:click="$set('selected', [])"
                                class="p-2 rounded-lg text-indigo-600 hover:bg-indigo-100">
                            ✕
                        </button>

                        <span class="text-sm font-semibold text-indigo-900">
                            Đã chọn {{ count($selected) }} dòng
                        </span>
                    </div>

                    <button wire:click="deleteSelected"
                            class="text-red-600 text-sm font-medium">
                        Xóa
                    </button>

                </div>

            @else

                <!-- FILTER -->
                <div class="p-2 flex flex-col md:flex-row gap-2">

                    <!-- SEARCH -->
                    <div class="relative flex-1">
                        <input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="Tìm hoạt chất..."
                            class="w-full pl-10 pr-3 py-2 bg-transparent focus:ring-0 border-0"
                        >

                        <span class="absolute left-3 top-2 text-gray-400">🔍</span>

                        @if($search)
                            <button wire:click="$set('search','')"
                                class="absolute right-2 top-2 text-gray-400 hover:text-red-500">
                                ✕
                            </button>
                        @endif
                    </div>

                    <div class="w-px bg-gray-200 hidden md:block"></div>

                    <!-- FILTER -->
                    <div class="flex gap-2">

                        <select wire:model.live="hospitalLevel"
                            class="bg-gray-50 px-3 py-2 rounded-lg text-sm">
                            <option value="">Hạng BV</option>
                            <option value="1">Level 1</option>
                            <option value="2">Level 2</option>
                        </select>

                        <select wire:model.live="perPage"
                            class="bg-gray-50 px-3 py-2 rounded-lg text-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>

                    </div>

                </div>

            @endif
        </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        <table class="min-w-full divide-y divide-gray-200">

            <!-- HEADER -->
            <thead class="bg-gray-50/50">
                <tr>

                    <th class="px-4 py-3 text-center">
                        <input type="checkbox" wire:model.live="selectAll">
                    </th>

                    @foreach([
                        'name' => 'Hoạt chất',
                        'dosage_form' => 'Dạng dùng',
                        'hospital_level' => 'Hạng BV',
                        'drug_group' => 'Nhóm thuốc'
                    ] as $field => $label)

                    <th wire:click="sortBy('{{ $field }}')"
                        class="px-6 py-3 text-left text-xs font-medium uppercase cursor-pointer
                        {{ $sortColumn === $field ? 'text-indigo-600 bg-indigo-50' : 'text-gray-500' }}">

                        <div class="flex items-center gap-1">
                            {{ $label }}

                            @if($sortColumn === $field)
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </div>
                    </th>

                    @endforeach

                    <th class="px-6 py-3">Ghi chú</th>
                    <th></th>
                </tr>
            </thead>

            <!-- BODY -->
            <tbody class="divide-y divide-gray-200">

                @forelse($data as $item)
                <tr class="hover:bg-gray-50 {{ in_array($item->id, $selected) ? 'bg-indigo-50/40' : '' }}">

                    <td class="px-4 py-4 text-center">
                        <input type="checkbox"
                               wire:model.live="selected"
                               value="{{ $item->id }}">
                    </td>

                    <!-- NAME INLINE EDIT -->
                    <td class="px-6 py-4">
                        <input value="{{ $item->name }}"
                               wire:change="editInline({{ $item->id }}, 'name', $event.target.value)"
                               class="font-semibold bg-transparent w-full">
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-600">
                        {{ $item->dosage_form }}
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded">
                            {{ $item->hospital_level }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">
                            {{ $item->drug_group }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $item->note }}
                    </td>

                    <!-- ACTION -->
                    <td class="px-6 py-4 text-right">
                        <button wire:click="delete({{ $item->id }})"
                                class="text-gray-400 hover:text-red-600">
                            🗑
                        </button>
                    </td>

                </tr>
                @empty

                <!-- EMPTY -->
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                📦
                            </div>
                            <h3 class="text-sm font-medium text-gray-900">Không có dữ liệu</h3>
                            <p class="text-sm text-gray-500">Thử tìm kiếm khác</p>
                        </div>
                    </td>
                </tr>

                @endforelse

            </tbody>
        </table>

        <!-- PAGINATION -->
        <div class="bg-gray-50 border-t px-4 py-3">
            {{ $data->links() }}
        </div>
    </div>

</div>
