<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.index', [
            'totalTitles' => Book::query()->count(),
            'totalCopies' => (int) Book::query()->sum('total_stock'),
            'availableCopies' => (int) Book::query()->sum('available_stock'),
            'totalCategories' => Category::query()->count(),
            'totalMembers' => Member::query()->count(),
            'activeLoans' => Loan::query()->whereNull('returned_at')->count(),
            'overdueLoans' => Loan::query()->whereNull('returned_at')->whereDate('due_at', '<', today()->toDateString())->count(),
            'recentBooks' => Book::query()->with('category')->latest()->limit(5)->get(),
        ]);
    }
}
