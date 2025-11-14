<?php

namespace App\Exports;

use App\Models\AceGfn;
use App\Models\AceTotalGfn;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AceGfnExport implements FromView
{
    public function __construct(
        public ?string $date = null,
        public ?string $shift = null,
    ) {}

    public function view(): View
    {
        $rows = AceGfn::query()
            ->when($this->date, fn($q) => $q->whereDate('gfn_date', $this->date))
            ->when($this->shift, fn($q) => $q->where('shift', $this->shift))
            ->orderBy('gfn_date')
            ->orderBy('shift')
            ->get();

        $recaps = AceTotalGfn::query()
            ->when($this->date, fn($q) => $q->whereDate('gfn_date', $this->date))
            ->when($this->shift, fn($q) => $q->where('shift', $this->shift))
            ->orderBy('gfn_date')
            ->orderBy('shift')
            ->get();

        return view('aceline-gfn.export', compact('rows', 'recaps'));
    }
}
