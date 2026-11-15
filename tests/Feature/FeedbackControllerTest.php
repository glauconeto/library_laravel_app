<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Feedback;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $librarian;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Role::where('name', 'librarian')->exists()) {
            Role::create(['name' => 'librarian']);
        }

        $this->user = User::factory()->create();
        $this->librarian = User::factory()->create();
        $this->librarian->assignRole('librarian');

        $this->book = Book::factory()->create([
            'stock' => 5,
        ]);
    }

    public function test_feedback_index_requires_auth(): void
    {
        $response = $this->get('/feedbacks');
        $response->assertRedirect('/login');
    }

    public function test_feedback_index_as_user(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/feedbacks');
        $response->assertStatus(200);
    }

    public function test_feedback_store_success_with_returned_loan(): void
    {
        $this->actingAs($this->user);

        // Create a returned loan for this user/book
        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'loan_date' => now()->subDays(10)->format('Y-m-d'),
            'due_date' => now()->subDays(3)->format('Y-m-d'),
            'returned_at' => now()->subDays(2),
        ]);

        $response = $this->post("/books/{$this->book->id}/feedback", [
            'rating' => 5,
            'comment' => 'Excelente livro!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 5,
        ]);
    }

    public function test_feedback_store_as_librarian_without_loan(): void
    {
        $this->actingAs($this->librarian);

        $response = $this->post("/books/{$this->book->id}/feedback", [
            'rating' => 4,
            'comment' => 'Bom livro',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $this->librarian->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_feedback_store_fails_duplicate(): void
    {
        $this->actingAs($this->user);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'loan_date' => now()->subDays(10)->format('Y-m-d'),
            'due_date' => now()->subDays(3)->format('Y-m-d'),
            'returned_at' => now()->subDays(2),
        ]);

        Feedback::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 5,
            'comment' => 'Primeiro',
        ]);

        $response = $this->post("/books/{$this->book->id}/feedback", [
            'rating' => 3,
            'comment' => 'Segundo',
        ]);

        // Controller returns back()->with('error', ...) -> 302 redirect with error
        $response->assertStatus(302);
        $this->assertDatabaseCount('feedbacks', 1);
    }

    public function test_feedback_store_fails_without_returned_loan(): void
    {
        $this->actingAs($this->user);

        // No loan at all
        $response = $this->post("/books/{$this->book->id}/feedback", [
            'rating' => 5,
            'comment' => 'Tentativa sem empréstimo',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('feedbacks', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }

    public function test_feedback_store_validation_rating_required(): void
    {
        $this->actingAs($this->librarian);

        $response = $this->postJson("/books/{$this->book->id}/feedback", [
            'comment' => 'Sem rating',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    }

    public function test_feedback_store_validation_rating_out_of_range(): void
    {
        $this->actingAs($this->librarian);

        $response = $this->postJson("/books/{$this->book->id}/feedback", [
            'rating' => 6,
            'comment' => 'Rating inválido',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);

        $response = $this->postJson("/books/{$this->book->id}/feedback", [
            'rating' => 0,
        ]);

        $response->assertStatus(422);
    }

    public function test_feedback_show(): void
    {
        $this->actingAs($this->user);
        $feedback = Feedback::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->get("/feedbacks/{$feedback->id}");
        $response->assertStatus(200);
    }

    public function test_feedback_update_by_owner(): void
    {
        $this->actingAs($this->user);
        $feedback = Feedback::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'rating' => 3,
        ]);

        $response = $this->put("/feedbacks/{$feedback->id}", [
            'rating' => 5,
            'comment' => 'Atualizado',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
            'rating' => 5,
            'comment' => 'Atualizado',
        ]);
    }

    public function test_feedback_update_by_other_fails(): void
    {
        $other = User::factory()->create();
        $feedback = Feedback::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->actingAs($other);
        $response = $this->put("/feedbacks/{$feedback->id}", [
            'rating' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_feedback_destroy_by_owner(): void
    {
        $this->actingAs($this->user);
        $feedback = Feedback::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $response = $this->delete("/feedbacks/{$feedback->id}");
        $response->assertStatus(302);
        $response->assertRedirect(route('books.show', $this->book));
        $this->assertDatabaseMissing('feedbacks', ['id' => $feedback->id]);
    }

    public function test_feedback_destroy_by_other_fails(): void
    {
        $other = User::factory()->create();
        $feedback = Feedback::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);

        $this->actingAs($other);
        $response = $this->delete("/feedbacks/{$feedback->id}");
        $response->assertStatus(403);
    }

    public function test_feedback_store_with_active_loan_not_returned_fails(): void
    {
        $this->actingAs($this->user);

        // Active loan (not returned) should not allow feedback
        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'loan_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(14)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        $response = $this->post("/books/{$this->book->id}/feedback", [
            'rating' => 5,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('feedbacks', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
        ]);
    }
}
