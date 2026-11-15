<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['email' => 'book@example.com']);
        // Ensure librarian role exists and assign to user for book management
        if (!\Spatie\Permission\Models\Role::where('name', 'librarian')->exists()) {
            \Spatie\Permission\Models\Role::create(['name' => 'librarian']);
        }
        $this->user->assignRole('librarian');
    }

    /**
     * Test index books
     */
    public function test_index_books(): void
    {
        $this->actingAs($this->user);
        Book::factory()->count(2)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Books/Index', false)
            ->has('books.data', 2)
            ->has('genres')
        );
    }

    /**
     * Test show book
     */
    public function test_show_book(): void
    {
        $this->actingAs($this->user);
        $book = Book::factory()->create([
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'stock' => 10,
        ]);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Books/Show', false)
            ->has('book')
            ->has('averageRating')
            ->has('canBorrow')
        );
    }

    /**
     * Test create book (POST /books - returns redirect to Inertia)
     */
    public function test_create_book(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '123-456-789',
            'stock' => 10,
            'description' => 'Test description',
        ]);

        // BookController::store redirects to books.index (Inertia view)
        $response->assertStatus(302);
        $response->assertRedirect('/books');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'title' => 'Test Book',
            'isbn' => '123-456-789',
        ]);
    }

    /**
     * Test update book (PUT /books/{id} - returns redirect to Inertia)
     */
    public function test_update_book(): void
    {
        $this->actingAs($this->user);
        $book = Book::factory()->create(['author' => 'Test Author', 'genre' => 'Fiction', 'year' => 2024, 'stock' => 10]);

        $response = $this->put("/books/{$book->id}", [
            'title' => 'Updated Book',
            'author' => 'Updated Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '987-654-321',
            'stock' => 10,
            'description' => 'Updated description',
        ]);

        // BookController::update redirects to books.index (Inertia view)
        $response->assertStatus(302);
        $response->assertRedirect('/books');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('books', [
            'title' => 'Updated Book',
            'isbn' => '987-654-321',
        ]);
    }

    /**
     * Test delete book (DELETE /books/{id} - redirect to Inertia index)
     */
    public function test_delete_book(): void
    {
        $this->actingAs($this->user);
        $book = Book::factory()->create(['author' => 'Test Author', 'genre' => 'Fiction', 'year' => 2024, 'stock' => 10]);

        $response = $this->delete("/books/{$book->id}");

        $response->assertStatus(302);
        $response->assertRedirect(route('books.index'));
        $response->assertSessionHas('success', 'Livro excluído com sucesso.');

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /**
     * Test delete book with active loans (redirect with error flash)
     */
    public function test_delete_book_with_active_loans(): void
    {
        $this->actingAs($this->user);
        $book = Book::factory()->create(['author' => 'Test Author', 'genre' => 'Fiction', 'year' => 2024, 'stock' => 10]);

        // Create an active loan with required dates
        Loan::create([
            'book_id' => $book->id,
            'user_id' => $this->user->id,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'returned_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->delete("/books/{$book->id}");

        // Policy denies deletion while an active loan exists (403)
        $response->assertStatus(403);

        // Book should not be deleted
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    /**
     * Test create book with missing title
     */
    public function test_create_book_with_missing_title(): void
    {
        $this->actingAs($this->user);
        $response = $this->postJson('/books', [
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '123-456-789',
            'stock' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }

    /**
     * Test show book with not found - expect 404
     */
    public function test_show_book_with_not_found(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/books/99999');

        $response->assertStatus(404);
    }

    /**
     * Test delete book with not found - expect 404 (model binding)
     */
    public function test_delete_book_with_not_found(): void
    {
        $this->actingAs($this->user);
        $response = $this->delete('/books/99999');

        $response->assertStatus(404);
    }

    /**
     * Test create book with invalid isbn
     */
    public function test_create_book_with_invalid_isbn(): void
    {
        $this->actingAs($this->user);
        $response = $this->postJson('/books', [
            'title' => 'Test Book',
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => 'invalid-isbn',
            'stock' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['isbn']);
    }

    /**
     * Test create book with missing required fields
     */
    public function test_create_book_with_missing_required_fields(): void
    {
        $this->actingAs($this->user);
        $response = $this->postJson('/books', [
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '123-456-789',
            'stock' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
    }
}
