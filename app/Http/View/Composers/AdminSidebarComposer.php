<?php

declare(strict_types=1);

namespace App\Http\View\Composers;

use App\Domain\Kost\Models\Kost;
use App\Domain\Rental\Models\Rental;
use Illuminate\View\View;

class AdminSidebarComposer
{
    /**
     * Bind data to the admin sidebar view.
     */
    public function compose(View $view): void
    {
        if (! auth()->check()) {
            return;
        }

        $data = [];

        // For Admin: Count rentals pending document verification
        if (auth()->user()->isAdmin()) {
            $data['pendingVerifications'] = Rental::whereHas('documents', function ($query) {
                $query->where('status', 'pending');
            })
                ->where('user_id', auth()->id())
                ->count();
        }

        // For Super Admin: Count kost submissions pending review
        if (auth()->user()->isSuperAdmin()) {
            $data['pendingSubmissions'] = Kost::where('status', 'pending_review')->count();
        }

        $view->with($data);
    }
}
