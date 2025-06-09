<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Branch;

class BranchSwitcher extends Component
{
    public $currentBranch;
    public $branches;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->currentBranch = session('current_branch_id') 
            ? Branch::find(session('current_branch_id'))
            : Branch::where('is_main', true)->first();
            
        $this->branches = Branch::orderBy('name')->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.branch-switcher');
    }
}
