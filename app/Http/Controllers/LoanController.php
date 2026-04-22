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
            return back()->with('error', 'Você atingiu o limite máximo de 3 empréstimos ativos.');
        }

        if (!$book->isAvailableForLoan()) {
            return back()->with('error', 'Este livro não está disponível para empréstimo.');
        }

        $existingLoan = Loan::where('user_id', $user->id)
                           ->where('book_id', $book->id)
                           ->whereNull('returned_at')
                           ->first();

        if ($existingLoan) {
            return back()->with('error', 'Você já tem um empréstimo ativo para este livro.');
        }

        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
        ]);

        return redirect()->route('loans.index')
            ->with('success', "Livro '{$book->title}' emprestado com sucesso. Data de devolução: {$loan->due_date->format('d/m/Y')}");
    }

    /**
     * Return a book.
     */
    public function return(Loan $loan): RedirectResponse
    {
        $user = auth()->user();

        if ($loan->user_id !== $user->id && !$user->hasRole('librarian')) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($loan->returned_at) {
            return back()->with('error', 'Este livro já foi devolvido.');
        }

        $loan->update(['returned_at' => now()]);

        return redirect()->route('loans.index')
            ->with('success', "Livro '{$loan->book->title}' devolvido com sucesso.");
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
            return back()->with('error', 'Não é possível excluir um empréstimo ativo. Devolva o livro primeiro.');
        }

        $loan->delete();

        return redirect()->route('loans.index')
            ->with('success', 'Registro de empréstimo excluído com sucesso.');
    }

    /**
     * Extend loan due date.
     */
    public function extend(Loan $loan): RedirectResponse
    {
        $user = auth()->user();

        if ($loan->user_id !== $user->id && !$user->hasRole('librarian')) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($loan->returned_at) {
            return back()->with('error', 'Não é possível estender um empréstimo devolvido.');
        }

        if ($loan->isOverdue()) {
            return back()->with('error', 'Não é possível estender um empréstimo atrasado.');
        }

        $newDueDate = $loan->due_date->addDays(7);
        $loan->update(['due_date' => $newDueDate]);

        return redirect()->route('loans.index')
            ->with('success', "Empréstimo estendido até {$newDueDate->format('d/m/Y')}.");
    }
}
