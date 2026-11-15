<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;

class PermissionAndFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $librarian;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Role::where('name', 'librarian')->exists()) {
            Role::create(['name' => 'librarian']);
        }
        if (!Role::where('name', 'reader')->exists()) {
            Role::create(['name' => 'reader']);
        }

        $this->user = User::factory()->create();
        $this->librarian = User::factory()->create();
        $this->librarian->assignRole('librarian');
    }

    // --- Roles ---

    public function test_user_without_role_cannot_create_book(): void
    {
        $this->actingAs($this->user);
        $response = $this->postJson('/books', [
            'title' => 'Test',
            'author' => 'Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '123-456-789',
            'stock' => 5,
        ]);

        $response->assertStatus(403);
    }

    public function test_librarian_can_create_book(): void
    {
        $this->actingAs($this->librarian);
        $response = $this->post('/books', [
            'title' => 'Lib Book',
            'author' => 'Lib Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => '111-222-333',
            'stock' => 3,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('books', ['title' => 'Lib Book']);
    }

    public function test_reader_role_assignment(): void
    {
        $this->user->assignRole('reader');
        $this->assertTrue($this->user->hasRole('reader'));
        $this->assertFalse($this->user->hasRole('librarian'));
        $this->assertTrue($this->librarian->hasRole('librarian'));
    }

    public function test_user_cannot_update_or_delete_book(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->user);
        $this->put("/books/{$book->id}", [
            'title' => 'Hacked',
            'author' => 'Hacker',
            'genre' => 'Fiction',
            'year' => 2024,
            'isbn' => $book->isbn,
            'stock' => 5,
        ])->assertStatus(403);

        $this->delete("/books/{$book->id}")->assertStatus(403);
    }

    public function test_librarian_can_update_and_delete_book(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->librarian);
        $this->put("/books/{$book->id}", [
            'title' => 'Updated by Librarian',
            'author' => $book->author,
            'genre' => $book->genre,
            'year' => $book->year,
            'isbn' => $book->isbn,
            'stock' => 10,
        ])->assertStatus(302);

        $this->assertDatabaseHas('books', ['title' => 'Updated by Librarian']);

        $this->delete("/books/{$book->id}")->assertStatus(302);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    // --- Book Filters ---

    public function test_book_index_search_filter(): void
    {
        Book::factory()->create(['title' => 'Laravel Guide', 'author' => 'Taylor', 'isbn' => '111-111-111']);
        Book::factory()->create(['title' => 'PHP Basics', 'author' => 'Rasmus', 'isbn' => '222-222-222']);
        Book::factory()->create(['title' => 'Python Tricks', 'author' => 'Guido', 'isbn' => '333-333-333']);

        $this->actingAs($this->user);

        $r = $this->get('/books?search=Laravel');
        $r->assertStatus(200);
        $r->assertInertia(fn (Assert $page) => $page
            ->component('Books/Index', false)
            ->has('books.data', 1)
        );

        $r2 = $this->get('/books?search=Rasmus');
        $r2->assertStatus(200);
        $r2->assertInertia(fn (Assert $page) => $page->has('books.data', 1));

        $r3 = $this->get('/books?search=333-333');
        $r3->assertStatus(200);
        $r3->assertInertia(fn (Assert $page) => $page->has('books.data', 1));
    }

    public function test_book_index_genre_filter(): void
    {
        Book::factory()->create(['genre' => 'Ficção']);
        Book::factory()->create(['genre' => 'Ficção']);
        Book::factory()->create(['genre' => 'Romance']);

        $this->actingAs($this->user);

        $r = $this->get('/books?genre=Ficção');
        $r->assertStatus(200);
        $r->assertInertia(fn (Assert $page) => $page->has('books.data', 2));

        $r2 = $this->get('/books?genre=Romance');
        $r2->assertStatus(200);
        $r2->assertInertia(fn (Assert $page) => $page->has('books.data', 1));
    }

    public function test_book_index_available_filter(): void
    {
        $available = Book::factory()->create(['stock' => 5]);
        $unavailable = Book::factory()->create(['stock' => 1]);

        // Make unavailable book fully borrowed
        Loan::factory()->create([
            'book_id' => $unavailable->id,
            'user_id' => $this->user->id,
            'returned_at' => null,
        ]);

        $this->assertTrue($available->fresh()->isAvailableForLoan());
        $this->assertFalse($unavailable->fresh()->isAvailableForLoan());

        $this->actingAs($this->user);

        $r = $this->get('/books?available=true');
        $r->assertStatus(200);
        $r->assertInertia(fn (Assert $page) => $page
            ->has('books.data')
            ->where('filters.available', 'true')
        );

        // Available filter should exclude unavailable book
        $books = $r->inertiaPage()['props']['books']['data'];
        $ids = array_column($books, 'id');
        $this->assertContains($available->id, $ids);
        $this->assertNotContains($unavailable->id, $ids);
    }

    // --- User canBorrow ---

    public function test_user_can_borrow_logic(): void
    {
        $this->assertTrue($this->user->canBorrow());

        for ($i = 0; $i < 3; $i++) {
            Loan::factory()->create([
                'user_id' => $this->user->id,
                'book_id' => Book::factory()->create()->id,
                'returned_at' => null,
            ]);
        }

        $this->assertFalse($this->user->fresh()->canBorrow());

        // Return one
        $loan = $this->user->loans()->first();
        $loan->update(['returned_at' => now()]);

        $this->assertTrue($this->user->fresh()->canBorrow());
    }

    public function test_book_is_available_for_loan(): void
    {
        $book = Book::factory()->create(['stock' => 2]);

        $this->assertTrue($book->isAvailableForLoan());

        Loan::factory()->create(['book_id' => $book->id, 'returned_at' => null]);
        $this->assertTrue($book->fresh()->isAvailableForLoan());

        Loan::factory()->create(['book_id' => $book->id, 'returned_at' => null]);
        $this->assertFalse($book->fresh()->isAvailableForLoan());

        // Return one
        $book->loans()->first()->update(['returned_at' => now()]);
        $this->assertTrue($book->fresh()->isAvailableForLoan());
    }

    public function test_guest_cannot_access_books(): void
    {
        $response = $this->get('/books');
        $response->assertRedirect('/login');

        $book = Book::factory()->create();
        $this->get("/books/{$book->id}")->assertRedirect('/login');
        $this->postJson('/books', [])->assertStatus(401);
    }

    public function test_feedback_requires_auth(): void
    {
        $book = Book::factory()->create();
        $this->get('/feedbacks')->assertRedirect('/login');
        $this->post("/books/{$book->id}/feedback", ['rating' => 5])->assertRedirect('/login');
    }

    public function test_loan_requires_auth(): void
    {
        $book = Book::factory()->create();
        $this->postJson("/books/{$book->id}/borrow", ['book_id' => $book->id])->assertStatus(401);
        $this->get('/loans')->assertRedirect('/login');
    }
}
