<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user can view their own loans.
     */
    public function test_user_can_view_own_loans(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/loans');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('loans.data.0'));
    }

    /**
     * Test librarian can view all loans.
     */
    public function test_librarian_can_view_all_loans(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($librarian);

        $response = $this->get('/loans');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('loans.data.0'));
    }

    /**
     * Test user can only view their own loans.
     */
    public function test_user_can_only_view_own_loans(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        Loan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        Loan::factory()->create([
            'user_id' => $user2->id,
            'book_id' => Book::factory()->create(),
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user1);

        $response = $this->get('/loans');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('loans.data.0'));
    }

    /**
     * Test user can borrow a book.
     */
    public function test_user_can_borrow_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['stock' => 5]);

        $this->actingAs($user);

        $response = $this->post("/books/{$book->id}/borrow");

        $response->assertRedirect('/loans');
        $this->assertDatabaseHas('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'returned_at' => null,
        ]);
        $this->assertDatabaseHas('loans', [
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ]);
    }

    /**
     * Test user cannot borrow more than 3 books.
     */
    public function test_user_cannot_borrow_more_than_3_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['stock' => 5]);

        // Create 3 active loans
        for ($i = 0; $i < 3; $i++) {
            Loan::factory()->create([
                'user_id' => $user->id,
                'book_id' => Book::factory()->create(),
                'loan_date' => now(),
                'due_date' => now()->addDays(14),
                'returned_at' => null,
            ]);
        }

        $this->actingAs($user);

        $response = $this->post("/books/{$book->id}/borrow");

        $response->assertSessionHas('error', 'Você atingiu o limite máximo de 3 empréstimos ativos.');
        $this->assertDatabaseMissing('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Test user cannot borrow unavailable book.
     */
    public function test_user_cannot_borrow_unavailable_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['stock' => 1]);

        // Create active loan for this book
        Loan::factory()->create([
            'user_id' => User::factory()->create(),
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post("/books/{$book->id}/borrow");

        $response->assertSessionHas('error', 'Este livro não está disponível para empréstimo.');
        $this->assertDatabaseMissing('loans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * Test user cannot borrow same book twice.
     */
    public function test_user_cannot_borrow_same_book_twice(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['stock' => 5]);

        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post("/books/{$book->id}/borrow");

        $response->assertSessionHas('error', 'Você já tem um empréstimo ativo para este livro.');
    }

    /**
     * Test user can return a book.
     */
    public function test_user_can_return_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post("/loans/{$loan->id}/return");

        $response->assertRedirect('/loans');
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'returned_at' => now(),
        ]);
    }

    /**
     * Test user cannot return already returned book.
     */
    public function test_user_cannot_return_already_returned_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/loans/{$loan->id}/return");

        $response->assertSessionHas('error', 'Este livro já foi devolvido.');
    }

    /**
     * Test user cannot return another user's book.
     */
    public function test_user_cannot_return_another_users_book(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user2);

        $response = $this->post("/loans/{$loan->id}/return");

        $response->assertSessionHas('error', 'Ação não autorizada.');
    }

    /**
     * Test librarian can return any book.
     */
    public function test_librarian_can_return_any_book(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($librarian);

        $response = $this->post("/loans/{$loan->id}/return");

        $response->assertRedirect('/loans');
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'returned_at' => now(),
        ]);
    }

    /**
     * Test user can view loan details.
     */
    public function test_user_can_view_loan_details(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get("/loans/{$loan->id}");

        $response->assertStatus(200);
    }

    /**
     * Test user cannot view another user's loan details.
     */
    public function test_user_cannot_view_another_users_loan_details(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user2);

        $response = $this->get("/loans/{$loan->id}");

        $response->assertForbidden();
    }

    /**
     * Test librarian can view any loan details.
     */
    public function test_librarian_can_view_any_loan_details(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($librarian);

        $response = $this->get("/loans/{$loan->id}");

        $response->assertStatus(200);
    }

    /**
     * Test user can extend loan due date.
     */
    public function test_user_can_extend_loan_due_date(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now()->subDays(7),
            'due_date' => now()->addDays(7),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->post("/loans/{$loan->id}/extend");

        $response->assertRedirect('/loans');
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'due_date' => now()->addDays(14)->format('Y-m-d'),
        ]);
    }

    /**
     * Test user cannot extend overdue loan.
     */
    public function test_user_cannot_extend_overdue_loan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->overdue()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/loans/{$loan->id}/extend");

        $response->assertSessionHas('error', 'Não é possível estender um empréstimo atrasado.');
    }

    /**
     * Test user cannot extend returned loan.
     */
    public function test_user_cannot_extend_returned_loan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/loans/{$loan->id}/extend");

        $response->assertSessionHas('error', 'Não é possível estender um empréstimo devolvido.');
    }

    /**
     * Test librarian can delete returned loan.
     */
    public function test_librarian_can_delete_returned_loan(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($librarian);

        $response = $this->delete("/loans/{$loan->id}");

        $response->assertRedirect('/loans');
        $this->assertDatabaseMissing('loans', ['id' => $loan->id]);
    }

    /**
     * Test librarian cannot delete active loan.
     */
    public function test_librarian_cannot_delete_active_loan(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($librarian);

        $response = $this->delete("/loans/{$loan->id}");

        // Policy denies deletion of active loans
        $response->assertForbidden();
        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    }

    /**
     * Test isOverdue method.
     */
    public function test_is_overdue_method(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Loan not overdue
        $loan1 = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->assertFalse($loan1->isOverdue());

        // Overdue loan
        $loan2 = Loan::factory()->overdue()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertTrue($loan2->isOverdue());

        // Returned loan
        $loan3 = Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertFalse($loan3->isOverdue());
    }

    /**
     * Test loan filtering by status.
     */
    public function test_can_filter_loans_by_status(): void
    {
        $user = User::factory()->create();

        // Active loan
        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        // Returned loan
        Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
        ]);

        // Overdue loan
        Loan::factory()->overdue()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
        ]);

        $this->actingAs($user);

        // Filter active loans
        $response = $this->get('/loans?status=active');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('loans'));

        // Filter returned loans
        $response = $this->get('/loans?status=returned');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('loans'));
    }

    /**
     * Test user canBorrow method.
     */
    public function test_user_can_borrow_method(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->canBorrow());

        // Add 2 active loans
        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $user->refresh();
        $this->assertTrue($user->canBorrow());

        // Add 3rd active loan
        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $user->refresh();
        $this->assertFalse($user->canBorrow());
    }
}
