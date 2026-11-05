<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test book index page access.
     */
    public function test_any_user_can_view_books(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/books');

        $response->assertStatus(200);
    }

    /**
     * Test guest cannot view create book page.
     */
    public function test_guest_cannot_view_create_book_page(): void
    {
        $response = $this->get('/books/create');

        $response->assertRedirect('/login');
    }

    /**
     * Test regular user cannot view create book page.
     */
    public function test_regular_user_cannot_view_create_book_page(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/books/create');

        $response->assertForbidden();
    }

    /**
     * Test librarian can view create book page.
     */
    public function test_librarian_can_view_create_book_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $response = $this->get('/books/create');

        $response->assertStatus(200);
    }

    /**
     * Test librarian can create book.
     */
    public function test_librarian_can_create_book(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $data = [
            'title' => 'Test Book Title',
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '978-1234567897',
            'stock' => 5,
            'description' => 'Test description',
        ];

        $response = $this->post('/books', $data);

        $response->assertRedirect('/books');
        $this->assertDatabaseHas('books', [
            'title' => 'Test Book Title',
            'author' => 'Test Author',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test book creation validation.
     */
    public function test_book_creation_validation(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $response = $this->post('/books', [
            'title' => '',
            'author' => '',
            'genre' => '',
            'year' => '',
            'isbn' => '',
            'stock' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'author', 'genre', 'year', 'isbn', 'stock']);
    }

    /**
     * Test book show page.
     */
    public function test_can_view_book_details(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
    }

    /**
     * Test regular user cannot edit book.
     */
    public function test_regular_user_cannot_edit_book(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get("/books/{$book->id}/edit");

        $response->assertForbidden();
    }

    /**
     * Test librarian can edit book.
     */
    public function test_librarian_can_edit_book(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $response = $this->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
    }

    /**
     * Test librarian can update book.
     */
    public function test_librarian_can_update_book(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $data = [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'genre' => 'Non-Fiction',
            'year' => 2025,
            'isbn' => '978-7654321098',
            'stock' => 10,
            'description' => 'Updated description',
        ];

        $response = $this->put("/books/{$book->id}", $data);

        $response->assertRedirect('/books');
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'author' => 'Updated Author',
        ]);
    }

    /**
     * Test regular user cannot delete book.
     */
    public function test_regular_user_cannot_delete_book(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete("/books/{$book->id}");

        $response->assertForbidden();
    }

    /**
     * Test librarian can delete book without active loans.
     */
    public function test_librarian_can_delete_book_without_loans(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $response = $this->delete("/books/{$book->id}");

        $response->assertRedirect('/books');
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /**
     * Test librarian cannot delete book with active loans.
     */
    public function test_librarian_cannot_delete_book_with_active_loans(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $this->actingAs($user);

        $loanUser = User::factory()->create();
        $book->loans()->create([
            'user_id' => $loanUser->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $response = $this->delete("/books/{$book->id}");

        // Policy should deny deletion, so it will be forbidden
        $response->assertForbidden();
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    /**
     * Test book search functionality.
     */
    public function test_can_search_books(): void
    {
        Book::factory()->create(['title' => 'Laravel Testing']);
        Book::factory()->create(['title' => 'PHP Programming']);
        Book::factory()->create(['title' => 'JavaScript Guide']);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/books?search=Laravel');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->where('books.data.0.title', 'Laravel Testing'));
    }

    /**
     * Test book filtering by genre.
     */
    public function test_can_filter_books_by_genre(): void
    {
        Book::factory()->create(['genre' => 'Fiction']);
        Book::factory()->create(['genre' => 'Fiction']);
        Book::factory()->create(['genre' => 'Science']);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/books?genre=Fiction');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('books'));
    }

    /**
     * Test book filtering by availability.
     */
    public function test_can_filter_available_books(): void
    {
        $availableBook = Book::factory()->create(['stock' => 2]);
        $unavailableBook = Book::factory()->create(['stock' => 1]);

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create active loan for unavailable book
        $user2 = User::factory()->create();
        $unavailableBook->loans()->create([
            'user_id' => $user2->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $response = $this->get('/books?available=true');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('books'));
    }

    /**
     * Test isAvailableForLoan method.
     */
    public function test_is_available_for_loan_method(): void
    {
        $book = Book::factory()->create(['stock' => 2]);

        $this->assertTrue($book->isAvailableForLoan());

        // Add active loans
        $user = User::factory()->create();
        $book->loans()->create([
            'user_id' => $user->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->assertTrue($book->isAvailableForLoan());

        // Add another active loan
        $user2 = User::factory()->create();
        $book->loans()->create([
            'user_id' => $user2->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->assertFalse($book->isAvailableForLoan());
    }
}
