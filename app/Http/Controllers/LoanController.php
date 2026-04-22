<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $loans = Loan::query()
            ->with(['book', 'user'])
            ->when($user->hasRole('librarian'), function ($query) {
                return $query;
            }, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->when($request->status, function ($query, $status) {
                match ($status) {
                    'active' => $query->whereNull('returned_at'),
                    'overdue' => $query->whereNull('returned_at')
                                  ->where('due_date', '<', now()),
                    'returned' => $query->whereNotNull('returned_at'),
                    default => $query,
                };
            })
            ->latest('loan_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Loans/Index', [
            'loans' => $loans,
            'filters' => $request->only(['status']),
            'isLibrarian' => $user->hasRole('librarian'),
        ]);
    }

    /**
     * Store a newly created loan in storage.
     */
    public function borrow(Request $request, Book $book): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->canBorrow()) {
            return back()->with('error', 'You have reached the maximum limit of 3 active loans.');
        }

        if (!$book->isAvailableForLoan()) {
            return back()->with('error', 'This book is not available for loan.');
        }

        $existingLoan = Loan::where('user_id', $user->id)
                           ->where('book_id', $book->id)
                           ->whereNull('returned_at')
                           ->first();

        if ($existingLoan) {
            return back()->with('error', 'You already have an active loan for this book.');
        }

        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
        ]);

        return redirect()->route('loans.index')
            ->with('success', "Book '{$book->title}' borrowed successfully. Due date: {$loan->due_date->format('Y-m-d')}");
    }

    /**
     * Return a book.
     */
    public function return(Request $request, Loan $loan): RedirectResponse
    {
        $user = auth()->user();

        if ($loan->user_id !== $user->id && !$user->hasRole('librarian')) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($loan->returned_at) {
            return back()->with('error', 'This book has already been returned.');
        }

        $loan->update(['returned_at' => now()]);

        return redirect()->route('loans.index')
            ->with('success', "Book '{$loan->book->title}' returned successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan): Response
    {
        $user = auth()->user();

        if ($loan->user_id !== $user->id && !$user->hasRole('librarian')) {
            abort(403);
        }

        $loan->load(['book', 'user']);

        return Inertia::render('Loans/Show', [
            'loan' => $loan,
            'isOverdue' => $loan->isOverdue(),
            'daysOverdue' => $loan->isOverdue() ? now()->diffInDays($loan->due_date) : 0,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan): RedirectResponse
    {
        $this->authorize('delete', $loan);

        if (!$loan->returned_at) {
            return back()->with('error', 'Cannot delete an active loan. Return the book first.');
        }

        $loan->delete();

        return redirect()->route('loans.index')
            ->with('success', 'Loan record deleted successfully.');
    }

    /**
     * Extend loan due date.
     */
    public function extend(Request $request, Loan $loan): RedirectResponse
    {
        $user = auth()->user();

        if ($loan->user_id !== $user->id && !$user->hasRole('librarian')) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($loan->returned_at) {
            return back()->with('error', 'Cannot extend a returned loan.');
        }

        if ($loan->isOverdue()) {
            return back()->with('error', 'Cannot extend an overdue loan.');
        }

        $newDueDate = $loan->due_date->addDays(7);
        $loan->update(['due_date' => $newDueDate]);

        return redirect()->route('loans.index')
            ->with('success', "Loan extended until {$newDueDate->format('Y-m-d')}.");
    }
}
