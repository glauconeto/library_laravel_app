<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoanControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->book = Book::factory()->create([
            'title' => 'Test Book for Loans',
            'isbn' => '123-456-789',
            'description' => 'This book is available for loans',
            'user_id' => $this->user->id,
            'author' => 'Test Author',
            'genre' => 'Fiction',
            'year' => 2024,
            'stock' => 5,
        ]);
    }

    /**
     * Test borrow book with valid user and available book
     */
    public function test_borrow_book_with_valid_credentials(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/books/' . $this->book->id . '/borrow');

        $response->assertStatus(302);
        $response->assertRedirect(route('loans.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'returned_at' => null,
        ]);
    }

    /**
     * Test borrow book with unavailable status
     */
    public function test_borrow_book_unavailable_status(): void
    {
        $this->actingAs($this->user);
        // Make book unavailable by filling its only copy with another loan
        $this->book->update(['stock' => 1]);
        Loan::create([
            'user_id' => User::factory()->create()->id,
            'book_id' => $this->book->id,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'returned_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->post('/books/' . $this->book->id . '/borrow');

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Este livro não está disponível para empréstimo.');
        $this->assertDatabaseMissing('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    /**
     * Test borrow book with invalid user (unauthenticated redirects to login)
     */
    public function test_borrow_book_with_invalid_user(): void
    {
        $response = $this->post('/books/' . $this->book->id . '/borrow');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('loans', [
            'book_id' => $this->book->id,
        ]);
    }

    /**
     * Test list user loans
     */
    public function test_list_user_loans(): void
    {
        $this->actingAs($this->user);
        $this->post('/books/' . $this->book->id . '/borrow');

        $response = $this->get('/loans');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Loans/Index', false));
    }

    /**
     * Test return book
     */
    public function test_return_book(): void
    {
        $this->actingAs($this->user);
        $this->post('/books/' . $this->book->id . '/borrow');

        $loan = Loan::where('user_id', $this->user->id)
            ->where('book_id', $this->book->id)
            ->firstOrFail();

        $response = $this->post('/loans/' . $loan->id . '/return');

        $response->assertStatus(302);
        $response->assertRedirect(route('loans.index'));
        $response->assertSessionHas('success');

        $loan->refresh();
        $this->assertNotNull($loan->returned_at);
        $this->assertTrue($this->book->isAvailableForLoan());
    }

    /**
     * Test return book with invalid loan ID
     */
    public function test_return_book_with_invalid_loan_id(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/loans/99999/return');

        $response->assertStatus(404);
    }

    /**
     * Test return book with non-existent loan
     */
    public function test_return_book_with_non_existent_loan(): void
    {
        $this->actingAs($this->user);
        // Try to return with invalid loan ID
        $response = $this->post('/loans/99999/return');

        $response->assertStatus(404);
    }

    /**
     * Test borrow book with non-existent book
     */
    public function test_borrow_book_with_non_existent_book(): void
    {
        $this->actingAs($this->user);
        $response = $this->post('/books/99999/borrow');

        $response->assertStatus(404);
    }

    /**
     * Test borrow book when the user already holds an active loan for it
     */
    public function test_borrow_book_with_invalid_input(): void
    {
        $this->actingAs($this->user);

        $this->post('/books/' . $this->book->id . '/borrow');

        // Second borrow of the same book must be refused with an error flash
        $response = $this->post('/books/' . $this->book->id . '/borrow');

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Você já tem um empréstimo ativo para este livro.');
        $this->assertDatabaseCount('loans', 1);
    }
}
