<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;

class LoanAdvancedTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $librarian;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Role::where('name', 'librarian')->exists()) {
            Role::create(['name' => 'librarian']);
        }

        $this->user = User::factory()->create();
        $this->librarian = User::factory()->create();
        $this->librarian->assignRole('librarian');
        $this->otherUser = User::factory()->create();
    }

    public function test_user_cannot_borrow_when_at_limit(): void
    {
        // 3 active loans
        for ($i = 0; $i < 3; $i++) {
            Loan::factory()->create([
                'user_id' => $this->user->id,
                'book_id' => Book::factory()->create(['stock' => 5])->id,
                'returned_at' => null,
            ]);
        }

        $book = Book::factory()->create(['stock' => 5]);
        $this->actingAs($this->user);
        $response = $this->post("/books/{$book->id}/borrow");

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Você atingiu o limite máximo de 3 empréstimos ativos.');
        $this->assertDatabaseMissing('loans', ['book_id' => $book->id, 'user_id' => $this->user->id]);
    }

    public function test_user_cannot_borrow_same_book_twice(): void
    {
        $book = Book::factory()->create(['stock' => 5]);
        $this->actingAs($this->user);

        $r1 = $this->post("/books/{$book->id}/borrow");
        $r1->assertStatus(302);
        $r1->assertRedirect(route('loans.index'));

        $r2 = $this->post("/books/{$book->id}/borrow");
        $r2->assertStatus(302);
        $r2->assertSessionHas('error', 'Você já tem um empréstimo ativo para este livro.');
    }

    public function test_user_cannot_borrow_when_stock_exhausted(): void
    {
        $book = Book::factory()->create(['stock' => 1]);

        // Another user borrows the only copy
        Loan::factory()->create([
            'user_id' => $this->otherUser->id,
            'book_id' => $book->id,
            'returned_at' => null,
        ]);

        $this->assertFalse($book->fresh()->isAvailableForLoan());

        $this->actingAs($this->user);
        $response = $this->post("/books/{$book->id}/borrow");

        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Este livro não está disponível para empréstimo.');
    }

    public function test_is_overdue_detection(): void
    {
        $overdue = Loan::factory()->overdue()->create(['user_id' => $this->user->id]);
        $active = Loan::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);
        $returnedOverdue = Loan::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->subDays(5)->format('Y-m-d'),
            'returned_at' => now(),
        ]);

        $this->assertTrue($overdue->isOverdue());
        $this->assertFalse($active->isOverdue());
        $this->assertFalse($returnedOverdue->isOverdue());
    }

    public function test_extend_success(): void
    {
        $book = Book::factory()->create();
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        $originalDue = $loan->due_date->copy();

        $this->actingAs($this->user);
        $response = $this->post("/loans/{$loan->id}/extend");
        $response->assertStatus(302);
        $response->assertRedirect(route('loans.index'));

        $loan->refresh();
        $this->assertEquals($originalDue->addDays(7)->format('Y-m-d'), $loan->due_date->format('Y-m-d'));
    }

    public function test_extend_fails_when_overdue(): void
    {
        $loan = Loan::factory()->overdue()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user);
        $response = $this->post("/loans/{$loan->id}/extend");

        $response->assertStatus(302);
        // Controller uses back()->with('error', 'Não é possível estender um empréstimo atrasado.')
        $this->assertEquals($loan->due_date->format('Y-m-d'), $loan->fresh()->due_date->format('Y-m-d'));
    }

    public function test_extend_fails_when_returned(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'returned_at' => now(),
        ]);

        $this->actingAs($this->user);
        $response = $this->post("/loans/{$loan->id}/extend");
        $response->assertStatus(302);
    }

    public function test_extend_fails_when_not_owner_and_not_librarian(): void
    {
        $loan = Loan::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->otherUser);
        $response = $this->post("/loans/{$loan->id}/extend");
        $response->assertStatus(302);
        // Should not extend
        $this->assertEquals($loan->due_date->format('Y-m-d'), $loan->fresh()->due_date->format('Y-m-d'));
    }

    public function test_librarian_can_extend_other_users_loan(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        $this->actingAs($this->librarian);
        $response = $this->post("/loans/{$loan->id}/extend");
        $response->assertStatus(302);
        $response->assertRedirect(route('loans.index'));
    }

    public function test_destroy_fails_when_active(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'returned_at' => null,
        ]);

        $this->actingAs($this->librarian);
        $response = $this->delete("/loans/{$loan->id}");

        // Policy requires returned_at not null, so 403
        $response->assertStatus(403);
        $this->assertDatabaseHas('loans', ['id' => $loan->id]);
    }

    public function test_destroy_success_as_librarian_with_returned(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'returned_at' => now(),
        ]);

        $this->actingAs($this->librarian);
        $response = $this->delete("/loans/{$loan->id}");
        $response->assertStatus(302);
        $response->assertRedirect(route('loans.index'));
        $this->assertDatabaseMissing('loans', ['id' => $loan->id]);
    }

    public function test_destroy_fails_as_regular_user(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'returned_at' => now(),
        ]);

        $this->actingAs($this->user);
        $response = $this->delete("/loans/{$loan->id}");
        $response->assertStatus(403);
    }

    public function test_index_librarian_sees_all_vs_user_sees_own(): void
    {
        $book = Book::factory()->create();
        Loan::factory()->create(['user_id' => $this->user->id, 'book_id' => $book->id]);
        Loan::factory()->create(['user_id' => $this->otherUser->id, 'book_id' => Book::factory()->create()->id]);

        // User sees only own
        $this->actingAs($this->user);
        $rUser = $this->get('/loans');
        $rUser->assertStatus(200);
        $rUser->assertInertia(fn (Assert $page) => $page
            ->component('Loans/Index', false)
            ->where('isLibrarian', false)
        );

        // Librarian sees all
        $this->actingAs($this->librarian);
        $rLib = $this->get('/loans');
        $rLib->assertStatus(200);
        $rLib->assertInertia(fn (Assert $page) => $page
            ->component('Loans/Index', false)
            ->where('isLibrarian', true)
        );
    }

    public function test_index_status_filter(): void
    {
        // active
        Loan::factory()->create([
            'user_id' => $this->user->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        // overdue
        Loan::factory()->overdue()->create(['user_id' => $this->user->id]);

        // returned
        Loan::factory()->create([
            'user_id' => $this->user->id,
            'returned_at' => now(),
        ]);

        $this->actingAs($this->user);

        $rActive = $this->get('/loans?status=active');
        $rActive->assertStatus(200);

        $rOverdue = $this->get('/loans?status=overdue');
        $rOverdue->assertStatus(200);

        $rReturned = $this->get('/loans?status=returned');
        $rReturned->assertStatus(200);
    }

    public function test_show_permission(): void
    {
        $loan = Loan::factory()->create(['user_id' => $this->user->id]);

        // Owner can see
        $this->actingAs($this->user);
        $r1 = $this->get("/loans/{$loan->id}");
        $r1->assertStatus(200);

        // Librarian can see
        $this->actingAs($this->librarian);
        $r2 = $this->get("/loans/{$loan->id}");
        $r2->assertStatus(200);

        // Other user cannot
        $this->actingAs($this->otherUser);
        $r3 = $this->get("/loans/{$loan->id}");
        $r3->assertStatus(403);
    }

    public function test_return_by_librarian_for_other_user(): void
    {
        $book = Book::factory()->create(['stock' => 5]);
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'returned_at' => null,
        ]);

        $this->actingAs($this->librarian);
        $response = $this->post("/loans/{$loan->id}/return");
        $response->assertStatus(302);
        $response->assertRedirect(route('loans.index'));
        $this->assertNotNull($loan->fresh()->returned_at);
    }

    public function test_return_fails_when_already_returned(): void
    {
        $loan = Loan::factory()->create([
            'user_id' => $this->user->id,
            'returned_at' => now(),
        ]);

        $this->actingAs($this->user);
        $response = $this->post("/loans/{$loan->id}/return");
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Este livro já foi devolvido.');
        $this->assertNotNull($loan->fresh()->returned_at);
    }
}
