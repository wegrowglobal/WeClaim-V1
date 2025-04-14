<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\User\Department;
use App\Models\Auth\Role;
use App\Models\User\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    /**
     * The search term.
     */
    public string $search = '';

    /**
     * The filters to apply.
     */
    public array $filters = [];

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
     * Get the users query with filters applied.
     */
    public function getUsersQuery(): Builder
    {
        $query = User::query()
            ->with(['role', 'department']);
        
        // Apply search
        if (!empty($this->search)) {
            $query->where(function (Builder $query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('second_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        
        // Apply filters
        if (!empty($this->filters['role_id'])) {
            $query->where('role_id', $this->filters['role_id']);
        }
        
        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }
        
        // Apply sorting
        $query->orderBy($this->sortColumn, $this->sortDirection);
        
        return $query;
    }

    /**
     * Get the roles for filtering.
     */
    public function getRoles(): array
    {
        return Role::pluck('name', 'id')->toArray();
    }

    /**
     * Get the departments for filtering.
     */
    public function getDepartments(): array
    {
        return Department::pluck('name', 'id')->toArray();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.tables.users-table', [
            'users' => $this->getUsersQuery()->paginate($this->perPage),
            'roles' => $this->getRoles(),
            'departments' => $this->getDepartments(),
        ]);
    }
}
