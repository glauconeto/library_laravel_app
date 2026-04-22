<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Feedback;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $feedbacks = Feedback::query()
            ->with(['user', 'book'])
            ->when($request->rating, function ($query, $rating) {
                $query->where('rating', $rating);
            })
            ->when($request->book_id, function ($query, $bookId) {
                $query->where('book_id', $bookId);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $books = Book::orderBy('title')->pluck('title', 'id');

        return Inertia::render('Feedbacks/Index', [
            'feedbacks' => $feedbacks,
            'filters' => $request->only(['rating', 'book_id']),
            'books' => $books,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Book $book): RedirectResponse
    {
        $user = auth()->user();

        $existingFeedback = Feedback::where('user_id', $user->id)
                                  ->where('book_id', $book->id)
                                  ->first();

        if ($existingFeedback) {
            return back()->with('error', 'You have already reviewed this book.');
        }

        $hasActiveLoan = Loan::where('user_id', $user->id)
                           ->where('book_id', $book->id)
                           ->whereNotNull('returned_at')
                           ->exists();

        if (!$hasActiveLoan && !$user->hasRole('librarian')) {
            return back()->with('error', 'You can only review books you have borrowed and returned.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return redirect()->route('books.show', $book)
            ->with('success', 'Your review has been submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Feedback $feedback): Response
    {
        $feedback->load(['user', 'book']);

        return Inertia::render('Feedbacks/Show', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Feedback $feedback): Response
    {
        $this->authorize('update', $feedback);

        return Inertia::render('Feedbacks/Edit', [
            'feedback' => $feedback,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feedback $feedback): RedirectResponse
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $feedback->update($validated);

        return redirect()->route('books.show', $feedback->book)
            ->with('success', 'Your review has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feedback $feedback): RedirectResponse
    {
        $this->authorize('delete', $feedback);

        $book = $feedback->book;
        $feedback->delete();

        return redirect()->route('books.show', $book)
            ->with('success', 'Your review has been deleted successfully.');
    }
}
