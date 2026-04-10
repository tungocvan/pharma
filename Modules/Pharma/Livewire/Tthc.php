<?php

namespace Modules\Pharma\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActiveIngredient;

class Tthc extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    public $sortColumn = 'id';
    public $sortDirection = 'desc';

    public $selected = [];
    public $selectAll = false;

    // FILTERS
    public $hospitalLevel = null;
    public $drugGroup = null;

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($column)
    {
        $this->sortDirection = $this->sortColumn === $column
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortColumn = $column;
    }

    public function updatedSelectAll($value)
    {
        $this->selected = $value
            ? ActiveIngredient::pluck('id')->toArray()
            : [];
    }

    public function deleteSelected()
    {
        ActiveIngredient::whereIn('id', $this->selected)->delete();

        $this->reset(['selected', 'selectAll']);
    }

    public function delete($id)
    {
        ActiveIngredient::find($id)?->delete();
    }

    public function editInline($id, $field, $value)
    {
        ActiveIngredient::where('id', $id)->update([
            $field => $value
        ]);
    }

    public function render()
    {
        $query = ActiveIngredient::query()
            ->search($this->search)
            ->when($this->hospitalLevel, fn($q) =>
                $q->where('hospital_level', $this->hospitalLevel)
            )
            ->when($this->drugGroup, fn($q) =>
                $q->where('drug_group', $this->drugGroup)
            )
            ->orderBy($this->sortColumn, $this->sortDirection);

        return view('Pharma::livewire.tthc', [
            'data' => $query->paginate($this->perPage),
            'groups' => ActiveIngredient::select('drug_group')->distinct()->pluck('drug_group'),
        ]);
    }
}
