# Table Filter Components

This document explains how to use the table filter components to add search and filter functionality to tables in the WeClaim application.

## Overview

The table filter system consists of several components:

1. `TableFilter` - A Livewire component that provides search, filter, and pagination functionality
2. `TableHeader` - A Blade component for sortable table headers
3. `TableCell` - A Blade component for table cells with responsive and truncation features

## Using the Components

### Option 1: Using the TableFilter Livewire Component

The `TableFilter` component provides a complete solution for filtering, searching, and paginating data. To use it:

```php
// In your Livewire component
public function render()
{
    return view('your-view', [
        'model' => User::class,
        'searchColumns' => ['first_name', 'second_name', 'email'],
        'filterOptions' => [
            'role_id' => Role::pluck('name', 'id')->toArray(),
            'department_id' => Department::pluck('name', 'id')->toArray(),
        ],
    ]);
}
```

```blade
{{-- In your Blade view --}}
<livewire:components.table-filter
    :model="$model"
    :search-columns="$searchColumns"
    :filter-options="$filterOptions">
    <x-slot:header>
        <x-table-header column="id" label="ID" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('id')" />
        <x-table-header column="first_name" label="Name" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('first_name')" />
        <x-table-header column="email" label="Email" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('email')" responsive breakpoint="sm" />
    </x-slot:header>
    
    <x-slot:body>
        @foreach($data as $item)
            <tr>
                <x-table-cell>{{ $item->id }}</x-table-cell>
                <x-table-cell truncate>{{ $item->first_name }} {{ $item->second_name }}</x-table-cell>
                <x-table-cell responsive breakpoint="sm">{{ $item->email }}</x-table-cell>
            </tr>
        @endforeach
    </x-slot:body>
</livewire:components.table-filter>
```

### Option 2: Creating a Custom Table Component

For more control, you can create a dedicated Livewire component for each table:

1. Create a new Livewire component:
```bash
php artisan make:livewire tables/YourTableName
```

2. Implement the component with search and filter functionality:
```php
<?php

declare(strict_types=1);

namespace App\Livewire\Tables;

use App\Models\YourModel;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class YourTableName extends Component
{
    use WithPagination;

    public string $search = '';
    public array $filters = [];
    public string $sortColumn = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortColumn' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'filters' => ['except' => []],
        'perPage' => ['except' => 10],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset('filters', 'search');
        $this->resetPage();
    }

    public function getDataQuery(): Builder
    {
        $query = YourModel::query();
        
        // Apply search
        if (!empty($this->search)) {
            $query->where(function (Builder $query) {
                $query->where('column1', 'like', '%' . $this->search . '%')
                    ->orWhere('column2', 'like', '%' . $this->search . '%');
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
        
        return $query;
    }

    public function render(): View
    {
        return view('livewire.tables.your-table-name', [
            'items' => $this->getDataQuery()->paginate($this->perPage),
            // Add any other data needed for filters
        ]);
    }
}
```

3. Create the Blade view with search, filter, and table components:
```blade
<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Search and Filters -->
    <div class="border-b border-gray-200 p-4">
        <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:space-x-4 sm:space-y-0">
            <!-- Search Input -->
            <div class="relative flex-grow focus-within:shadow-sm">
                <input
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-4 text-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    type="search"
                    placeholder="Search...">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Add your filters here -->
            
            <!-- Per Page Selector -->
            <div class="w-full sm:w-auto">
                <select
                    wire:model.live="perPage"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>

            <!-- Clear Filters Button -->
            @if(!empty($search) || !empty($filters))
                <button
                    wire:click="clearFilters"
                    class="inline-flex items-center justify-center rounded-lg border border-red-600 bg-white px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <x-table-header column="id" label="ID" :sort-column="$sortColumn" :sort-direction="$sortDirection" wire:click="sortBy('id')" />
                    <!-- Add more headers as needed -->
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <x-table-cell>{{ $item->id }}</x-table-cell>
                        <!-- Add more cells as needed -->
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-sm text-gray-600">
                            No items found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($items->hasPages())
        <div class="border-t border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 sm:px-6">
                <div class="mb-4 sm:mb-0 text-sm text-gray-600">
                    Showing <span class="font-medium text-gray-900">{{ $items->firstItem() }}</span>
                    to <span class="font-medium text-gray-900">{{ $items->lastItem() }}</span>
                    of <span class="font-medium text-gray-900">{{ $items->total() }}</span> results
                </div>
                <div class="pagination-links">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    @endif
</div>
```

## Component Reference

### TableHeader Component

```blade
<x-table-header 
    column="column_name"           {{-- Required: The column identifier --}}
    label="Column Label"           {{-- Required: The display label --}}
    :sort-column="$sortColumn"     {{-- Optional: Current sort column --}}
    :sort-direction="$sortDirection" {{-- Optional: Current sort direction --}}
    :sortable="true"               {{-- Optional: Whether column is sortable (default: true) --}}
    width="15%"                    {{-- Optional: Column width --}}
    :responsive="true"             {{-- Optional: Whether column is responsive (default: false) --}}
    breakpoint="md"                {{-- Optional: Responsive breakpoint (sm, md, lg, xl, 2xl) --}}
    wire:click="sortBy('column_name')" {{-- Optional: Wire click handler for sorting --}}
/>
```

### TableCell Component

```blade
<x-table-cell 
    :truncate="true"               {{-- Optional: Whether to truncate text (default: false) --}}
    maxWidth="250px"               {{-- Optional: Max width for truncated text --}}
    :responsive="true"             {{-- Optional: Whether cell is responsive (default: false) --}}
    breakpoint="md"                {{-- Optional: Responsive breakpoint (sm, md, lg, xl, 2xl) --}}
    :nowrap="true"                 {{-- Optional: Whether to prevent text wrapping (default: true) --}}
    align="center"                 {{-- Optional: Text alignment (left, center, right) (default: left) --}}
>
    Content goes here
</x-table-cell>
```

## Examples

See the following components for examples of implementation:

1. `app/Livewire/Tables/UsersTable.php` - Example of a users table with role and department filters
2. `app/Livewire/Tables/ClaimsTable.php` - Example of a claims table with status, user, and date range filters 