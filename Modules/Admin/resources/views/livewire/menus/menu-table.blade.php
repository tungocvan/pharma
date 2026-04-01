<div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Quản lý Menu</h1>
            <p class="mt-1 text-sm text-gray-500">Kéo thả để sắp xếp vị trí và phân cấp menu.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button wire:click="export" wire:loading.attr="disabled" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-bold uppercase rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export JSON
            </button>

            <button wire:click="$set('showImportModal', true)" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-bold uppercase rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Import
            </button>

            <a href="{{ route('admin.menus.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150">
                <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Thêm Mới
            </a>
        </div>
    </div>

    <div
        x-data="menuSortable()"
        x-init="initSortable()"
        class="bg-gray-50 rounded-xl border border-gray-200 p-6 min-h-[400px]"
    >
        <ul id="root-menu-list" class="space-y-3 menu-list">
            @foreach($menus as $menu)
                <x-menu-item :menu="$menu" />
            @endforeach
        </ul>

        @if($menus->isEmpty())
            <div class="text-center py-10 text-gray-400 border-2 border-dashed border-gray-300 rounded-lg">
                Chưa có menu nào. Hãy Import hoặc Thêm mới!
            </div>
        @endif
    </div>
    <div x-data="{ show: @entangle('showImportModal') }" x-show="show" style="display: none;" class="relative z-50">
        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="show = false"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-xl bg-white shadow-xl transition-all w-full max-w-md">
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="h-6 w-6 text-indigo-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Import Menu (JSON)
                        </h3>
                        <div class="space-y-4">
                            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-800">
                                Menu mới sẽ được <strong>thêm vào cuối</strong> danh sách hiện tại. Cấu trúc cha con sẽ được giữ nguyên.
                            </div>
                            <label class="block w-full rounded-xl border-2 border-dashed border-gray-300 p-8 text-center hover:bg-gray-50 hover:border-indigo-400 cursor-pointer transition">
                                <span class="text-sm text-gray-600 font-medium" x-text="$wire.importFile ? 'Đã chọn: ' + $wire.importFile.name : 'Click để chọn file .json'"></span>
                                <input type="file" wire:model="importFile" class="hidden" accept=".json">
                            </label>
                            @error('importFile') <p class="text-red-500 text-xs font-semibold">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                        <button type="button" @click="show = false" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                        <button type="button" wire:click="import" wire:loading.attr="disabled" class="px-4 py-2 bg-indigo-600 rounded-lg text-sm font-bold text-white hover:bg-indigo-700 disabled:opacity-70">
                            <span wire:loading.remove wire:target="import">Tiến hành Import</span>
                            <span wire:loading wire:target="import">Đang tải...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function menuSortable() {
            return {
                initSortable() {
                    // Tìm tất cả các list (cả cha và con)
                    const nestedSortables = [].slice.call(document.querySelectorAll('.menu-list'));

                    // Khởi tạo Sortable cho từng list
                    nestedSortables.forEach((el) => {
                        new Sortable(el, {
                            group: 'nested', // Cho phép kéo qua lại giữa các cấp
                            animation: 150,
                            fallbackOnBody: true,
                            swapThreshold: 0.65,
                            handle: '.drag-handle', // Chỉ kéo được khi nắm vào icon này
                            ghostClass: 'bg-indigo-50', // Class khi đang kéo
                            onEnd: (evt) => {
                                this.saveOrder();
                            }
                        });
                    });
                },
                saveOrder() {
                    // Hàm đệ quy để lấy cấu trúc ID
                    const getIds = (root) => {
                        const items = [];
                        // Lấy các thẻ li trực tiếp của ul hiện tại
                        const lis = root.children;

                        for (let i = 0; i < lis.length; i++) {
                            const li = lis[i];
                            // Bỏ qua nếu không phải element node (hoặc template)
                            if (li.tagName !== 'LI') continue;

                            const id = li.getAttribute('data-id');
                            // Tìm ul con bên trong li này (nếu có)
                            const childUl = li.querySelector('ul');

                            const item = { id: id };
                            if (childUl && childUl.children.length > 0) {
                                item.children = getIds(childUl);
                            }
                            items.push(item);
                        }
                        return items;
                    };

                    const rootList = document.getElementById('root-menu-list');
                    const payload = getIds(rootList);

                    // Gửi về Livewire
                    @this.updateMenuOrder(payload);
                }
            }
        }
    </script>
    <style>
        /* Style cho placeholder khi kéo */
        .bg-indigo-50 { background-color: #eef2ff; border: 1px dashed #6366f1; opacity: 0.8; }
    </style>
</div>

