<?php

namespace Modules\Admin\Livewire\Partials;

use Livewire\Component;
use Modules\Website\Models\Category;

class Sidebar extends Component
{
    public function render()
    {
        // Lấy danh mục có type='menu', lấy cấp cha (parent_id = null)
        // và load kèm con (children) để làm menu đa cấp
        $menuItems = Category::query()
            ->where('type', 'menu')
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('sort_order', 'asc');
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        return view('Admin::livewire.partials.sidebar', [
            'menuItems' => $menuItems
        ]);
    }
}
