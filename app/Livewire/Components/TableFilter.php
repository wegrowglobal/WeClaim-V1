<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class TableFilter extends Component
{
    use WithPagination;

    /**
     * The query builder instance.
     */
    public $query;

    /**
     * The model class name.
     */
    public string $model;

    /**
     * The search term.
     */
    public string $search = '';

    /**
     * The columns to search in.
     */
    public array $searchColumns = [];

    /**
     * The filters to apply.
     */
    public array $filters = [];

    /**
     * The available filter options.
     */
    public array $filterOptions = [];

    /**
     * The column to sort by.
     */
    public string $sortColumn = 'id';

    /**
     * The sort direction.
     */
    public string $sortDirection = 'desc';

    /**
     * The number of items per page.
     */
    public int $perPage = 10;

    /**
     * The debounce time for search input in milliseconds.
     */
    protected $debounce = ['search' => 300];

    /**
     * The query string parameters.
     */
    protected $queryString = [
        'search' => ['except' => ''],
        'sortColumn' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'filters' => ['except' => []],
        'perPage' => ['except' => 10],
    ];

    /**
     * Reset pagination when search or filters change.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset pagination when filters change.
     */
    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    /**
     * Sort the table by the given column.
     */
    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Apply a filter.
     */
    public function applyFilter(string $key, $value): void
    {
        if (empty($value)) {
            unset($this->filters[$key]);
        } else {
            $this->filters[$key] = $value;
        }
        
        $this->resetPage();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->reset('filters', 'search');
        $this->resetPage();
    }

    /**
     * Get the filtered and sorted data.
     */
    public function getData(): LengthAwarePaginator
    {
        $query = $this->baseQuery();
        
        // Apply search
        if (!empty($this->search) && !empty($this->searchColumns)) {
            $query->where(function (Builder $query) {
                foreach ($this->searchColumns as $column) {
                    $query->orWhere($column, 'like', '%' . $this->search . '%');
                }
            });
        }
        
        // Apply filters
        foreach ($this->filters as $key => $value) {
            if (!empty($value)) {
                $query->where($key, $value);
            }
        }
        
        // Apply sorting
        $query->orderBy($this->sortColumn, $this->sortDirection);
        
        return $query->paginate($this->perPage);
    }

    /**
     * Get the base query.
     */
    protected function baseQuery(): Builder
    {
        if (isset($this->query)) {
            return $this->query;
        }
        
        $modelClass = $this->model;
        return $modelClass::query();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.components.table-filter', [
            'data' => $this->getData(),
        ]);
    }
}
