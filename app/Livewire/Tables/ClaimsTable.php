<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\Claim\Claim;
use App\Models\User\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ClaimsTable extends Component
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
    public string $sortColumn = 'submitted_at';

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
        'sortColumn' => ['except' => 'submitted_at'],
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
     * Get the claims query with filters applied.
     */
    public function getClaimsQuery(): Builder
    {
        $query = Claim::query()
            ->with(['user', 'user.department']);
        
        // Apply search
        if (!empty($this->search)) {
            $query->where(function (Builder $query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function (Builder $query) {
                        $query->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('second_name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }
        
        // Apply filters
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        
        if (!empty($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }
        
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $this->filters['date_from']);
        }
        
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $this->filters['date_to']);
        }
        
        // Apply sorting
        $query->orderBy($this->sortColumn, $this->sortDirection);
        
        return $query;
    }

    /**
     * Get the statuses for filtering.
     */
    public function getStatuses(): array
    {
        return [
            Claim::STATUS_SUBMITTED => 'Submitted',
            Claim::STATUS_APPROVED_ADMIN => 'Approved by Admin',
            Claim::STATUS_APPROVED_DATUK => 'Approved by Datuk',
            Claim::STATUS_APPROVED_HR => 'Approved by HR',
            Claim::STATUS_APPROVED_FINANCE => 'Approved by Finance',
            Claim::STATUS_REJECTED => 'Rejected',
            Claim::STATUS_DONE => 'Done',
        ];
    }

    /**
     * Get the users for filtering.
     */
    public function getUsers(): array
    {
        return User::orderBy('first_name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->second_name,
                ];
            })
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.tables.claims-table', [
            'claims' => $this->getClaimsQuery()->paginate($this->perPage),
            'statuses' => $this->getStatuses(),
            'users' => $this->getUsers(),
        ]);
    }
}
