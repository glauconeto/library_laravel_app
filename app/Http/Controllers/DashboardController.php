<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $isLibrarian = method_exists($user, 'hasRole') ? $user->hasRole('librarian') : false;

        if ($isLibrarian) {
            return $this->librarianDashboard($request);
        }

        return $this->userDashboard($request);
    }

    /**
     * Librarian dashboard with system metrics.
     */
    private function librarianDashboard(Request $request): Response
    {
        $stats = [
            'total_books' => Book::count(),
            'total_users' => User::count(),
            'active_loans' => Loan::whereNull('returned_at')->count(),
            'overdue_loans' => Loan::whereNull('returned_at')
                                  ->where('due_date', '<', now())
                                  ->count(),
            'total_feedbacks' => Feedback::count(),
            'average_rating' => round(Feedback::avg('rating'), 2),
        ];

        $recentBooks = Book::latest()->take(5)->get();
        $recentLoans = Loan::with(['user', 'book'])
                          ->latest('loan_date')
                          ->take(10)
                          ->get();
        
        $overdueLoans = Loan::with(['user', 'book'])
                           ->whereNull('returned_at')
                           ->where('due_date', '<', now())
                           ->latest('due_date')
                           ->take(10)
                           ->get();

        $popularBooks = Book::withCount(['loans' => function ($query) {
                            $query->whereNull('returned_at');
                        }])
                        ->orderBy('loans_count', 'desc')
                        ->take(5)
                        ->get();

        return Inertia::render('Dashboard/Librarian', [
            'stats' => $stats,
            'recentBooks' => $recentBooks,
            'recentLoans' => $recentLoans,
            'overdueLoans' => $overdueLoans,
            'popularBooks' => $popularBooks,
        ]);
    }

    /**
     * Regular user dashboard.
     */
    private function userDashboard(Request $request): Response
    {
        $user = auth()->user();

        $userStats = [
            'active_loans' => $user->activeLoans()->count(),
            'total_loans' => $user->loans()->count(),
            'feedbacks_given' => $user->feedbacks()->count(),
            'can_borrow' => $user->canBorrow(),
            'max_loans' => 3,
        ];

        $activeLoans = $user->activeLoans()
                           ->with('book')
                           ->latest('loan_date')
                           ->get();

        $recentFeedbacks = $user->feedbacks()
                               ->with('book')
                               ->latest()
                               ->take(5)
                               ->get();

        $availableBooks = Book::whereRaw('(stock - (SELECT COUNT(*) FROM loans WHERE book_id = books.id AND returned_at IS NULL)) > 0')
                            ->inRandomOrder()
                            ->take(6)
                            ->get();

        return Inertia::render('Dashboard/User', [
            'stats' => $userStats,
            'activeLoans' => $activeLoans,
            'recentFeedbacks' => $recentFeedbacks,
            'availableBooks' => $availableBooks,
        ]);
    }

    /**
     * API endpoint for dashboard stats (for AJAX calls).
     */
    public function stats(Request $request): array
    {
        $user = auth()->user();
        $isLibrarian = $user->hasRole('librarian');

        if ($isLibrarian) {
            return [
                'total_books' => Book::count(),
                'active_loans' => Loan::whereNull('returned_at')->count(),
                'overdue_loans' => Loan::whereNull('returned_at')
                                      ->where('due_date', '<', now())
                                      ->count(),
                'total_users' => User::count(),
            ];
        }

        return [
            'active_loans' => $user->activeLoans()->count(),
            'can_borrow' => $user->canBorrow(),
            'feedbacks_given' => $user->feedbacks()->count(),
        ];
    }
}
