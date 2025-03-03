<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TableHeader extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $column,
        public string $label,
        public ?string $sortColumn = null,
        public ?string $sortDirection = null,
        public bool $sortable = true,
        public ?string $width = null,
        public bool $responsive = false,
        public ?string $breakpoint = null
    ) {
    }

    /**
     * Get the sort icon based on the current sort state.
     */
    public function sortIcon(): string
    {
        if ($this->sortColumn !== $this->column) {
            return <<<'SVG'
            <svg class="h-4 w-4 text-gray-400 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
            </svg>
            SVG;
        }

        return $this->sortDirection === 'asc'
            ? <<<'SVG'
            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 15l4-4 4 4" />
            </svg>
            SVG
            : <<<'SVG'
            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 9l4 4 4-4" />
            </svg>
            SVG;
    }

    /**
     * Get the responsive class based on the breakpoint.
     */
    public function responsiveClass(): string
    {
        if (!$this->responsive || !$this->breakpoint) {
            return '';
        }

        return match ($this->breakpoint) {
            'sm' => 'hidden sm:table-cell',
            'md' => 'hidden md:table-cell',
            'lg' => 'hidden lg:table-cell',
            'xl' => 'hidden xl:table-cell',
            '2xl' => 'hidden 2xl:table-cell',
            default => '',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.table-header');
    }
}
