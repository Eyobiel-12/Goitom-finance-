<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\User;

class ImpersonationBanner extends Component
{
    public ?User $impersonator = null;
    public bool $isImpersonating = false;

    public function __construct()
    {
        $this->isImpersonating = session()->has('impersonator_id');
        if ($this->isImpersonating) {
            $impersonatorId = session()->get('impersonator_id');
            $this->impersonator = User::find($impersonatorId);
        }
    }

    public function render()
    {
        return view('components.impersonation-banner');
    }
}
