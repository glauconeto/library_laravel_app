<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $books = Book::query()
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
            })
            ->when($request->genre, function ($query, $genre) {
                $query->where('genre', $genre);
            })
            ->when($request->available, function ($query, $available) {
                if ($available === 'true') {
                    $query->whereRaw('(stock - (SELECT COUNT(*) FROM loans WHERE book_id = books.id AND returned_at IS NULL)) > 0');
                }
            })
            ->withCount(['loans' => function ($query) {
                $query->whereNull('returned_at');
            }])
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $genres = Book::distinct()->pluck('genre')->filter()->sort();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'filters' => $request->only(['search', 'genre', 'available']),
            'genres' => $genres,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $this->authorize('create', Book::class);

        return Inertia::render('Books/Create', [
            'genres' => ['Fiction', 'Non-Fiction', 'Science', 'Technology', 'History', 'Biography', 'Mystery', 'Romance', 'Fantasy', 'Other'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Book::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'year' => 'required|integer|min:1000|max:' . date('Y'),
            'isbn' => 'required|string|unique:books,isbn|regex:/^[0-9\-X]{10,17}$/',
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string|max:2000',
        ]);

        $validated['user_id'] = Auth::id();

        Book::create($validated);

        return redirect()->route('books.index')
            ->with('success', 'Book created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): Response
    {
        $book->load([
            'user',
            'feedbacks' => function ($query) {
                $query->with('user')->latest()->limit(10);
            },
            'loans' => function ($query) {
                $query->with('user')->whereNull('returned_at')->latest();
            }
        ]);

        $book->loadCount(['feedbacks', 'loans' => function ($query) {
            $query->whereNull('returned_at');
        }]);

        $averageRating = $book->feedbacks()->avg('rating');

        $user = Auth::user();
        
        return Inertia::render('Books/Show', [
            'book' => $book,
            'averageRating' => round($averageRating, 1),
            'canBorrow' => $user && $user->canBorrow() && $book->isAvailableForLoan(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book): Response
    {
        $this->authorize('update', $book);

        return Inertia::render('Books/Edit', [
            'book' => $book,
            'genres' => ['Fiction', 'Non-Fiction', 'Science', 'Technology', 'History', 'Biography', 'Mystery', 'Romance', 'Fantasy', 'Other'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'genre' => 'required|string|max:100',
            'year' => 'required|integer|min:1000|max:' . date('Y'),
            'isbn' => 'required|string|regex:/^[0-9\-X]{10,17}$/|unique:books,isbn,' . $book->id,
            'stock' => 'required|integer|min:1',
            'description' => 'nullable|string|max:2000',
        ]);

        $book->update($validated);

        return redirect()->route('books.index')
            ->with('success', 'Book updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        if ($book->loans()->whereNull('returned_at')->exists()) {
            return redirect()->route('books.index')
                ->with('error', 'Cannot delete book with active loans.');
        }

        $book->delete();

        return redirect()->route('books.index')
            ->with('success', 'Book deleted successfully.');
    }
}
