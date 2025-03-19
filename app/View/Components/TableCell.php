<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TableCell extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public bool $truncate = false,
        public ?string $maxWidth = null,
        public bool $responsive = false,
        public ?string $breakpoint = null,
        public bool $nowrap = true,
        public ?string $align = 'left'
    ) {
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
     * Get the alignment class.
     */
    public function alignClass(): string
    {
        return match ($this->align) {
            'left' => 'text-left',
            'center' => 'text-center',
            'right' => 'text-right',
            default => 'text-left',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.table-cell');
    }
}
