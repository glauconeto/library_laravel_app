<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Feedback;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test librarian can view all feedbacks.
     */
    public function test_librarian_can_view_all_feedbacks(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $user = User::factory()->create();
        $book = Book::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'Great book!',
        ]);

        $this->actingAs($librarian);

        $response = $this->get('/feedbacks');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('feedbacks.data.0'));
    }

    /**
     * Test guest cannot view feedbacks.
     */
    public function test_guest_cannot_view_feedbacks(): void
    {
        $response = $this->get('/feedbacks');

        $response->assertRedirect('/login');
    }

    /**
     * Test user can create feedback after returning a book.
     */
    public function test_user_can_create_feedback_after_returning_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Create and return a loan
        $loan = Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now()->subDays(20),
            'due_date' => now()->subDays(6),
            'returned_at' => now()->subDays(5),
        ]);

        $this->actingAs($user);

        $data = [
            'rating' => 5,
            'comment' => 'Excellent book!',
        ];

        $response = $this->post("/books/{$book->id}/feedback", $data);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'Excellent book!',
        ]);
    }

    /**
     * Test user cannot create feedback without returning book.
     */
    public function test_user_cannot_create_feedback_without_returning_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Create active loan
        Loan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $this->actingAs($user);

        $data = [
            'rating' => 5,
            'comment' => 'Excellent book!',
        ];

        $response = $this->post("/books/{$book->id}/feedback", $data);

        $response->assertSessionHas('error', 'Você só pode avaliar livros que pegou emprestado e devolveu.');
    }

    /**
     * Test user cannot create duplicate feedback.
     */
    public function test_user_cannot_create_duplicate_feedback(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Create and return a loan
        Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // Create first feedback
        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'Good book',
        ]);

        $this->actingAs($user);

        $data = [
            'rating' => 5,
            'comment' => 'Excellent book!',
        ];

        $response = $this->post("/books/{$book->id}/feedback", $data);

        $response->assertSessionHas('error', 'Você já avaliou este livro.');
    }

    /**
     * Test librarian can create feedback for any book.
     */
    public function test_librarian_can_create_feedback_for_any_book(): void
    {
        $librarian = User::factory()->create();
        $librarian->assignRole('librarian');

        $book = Book::factory()->create();

        $this->actingAs($librarian);

        $data = [
            'rating' => 5,
            'comment' => 'Librarian review',
        ];

        $response = $this->post("/books/{$book->id}/feedback", $data);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('feedbacks', [
            'user_id' => $librarian->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'Librarian review',
        ]);
    }

    /**
     * Test feedback creation validation.
     */
    public function test_feedback_creation_validation(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        // Create and return a loan
        Loan::factory()->returned()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/books/{$book->id}/feedback", [
            'rating' => '',
            'comment' => '',
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    /**
     * Test user can view feedback details.
     */
    public function test_user_can_view_feedback_details(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'Good book',
        ]);

        $this->actingAs($user);

        $response = $this->get("/feedbacks/{$feedback->id}");

        $response->assertStatus(200);
    }

    /**
     * Test user can edit their own feedback.
     */
    public function test_user_can_edit_own_feedback(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'Average book',
        ]);

        $this->actingAs($user);

        $response = $this->get("/feedbacks/{$feedback->id}/edit");

        $response->assertStatus(200);
    }

    /**
     * Test user cannot edit another user's feedback.
     */
    public function test_user_cannot_edit_another_users_feedback(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'Average book',
        ]);

        $this->actingAs($user2);

        $response = $this->get("/feedbacks/{$feedback->id}/edit");

        $response->assertForbidden();
    }

    /**
     * Test user can update their own feedback.
     */
    public function test_user_can_update_own_feedback(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'Average book',
        ]);

        $this->actingAs($user);

        $data = [
            'rating' => 5,
            'comment' => 'Actually great book!',
        ];

        $response = $this->put("/feedbacks/{$feedback->id}", $data);

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseHas('feedbacks', [
            'id' => $feedback->id,
            'rating' => 5,
            'comment' => 'Actually great book!',
        ]);
    }

    /**
     * Test user can delete their own feedback.
     */
    public function test_user_can_delete_own_feedback(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'Average book',
        ]);

        $this->actingAs($user);

        $response = $this->delete("/feedbacks/{$feedback->id}");

        $response->assertRedirect("/books/{$book->id}");
        $this->assertDatabaseMissing('feedbacks', ['id' => $feedback->id]);
    }

    /**
     * Test user cannot delete another user's feedback.
     */
    public function test_user_cannot_delete_another_users_feedback(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user1->id,
            'book_id' => $book->id,
            'rating' => 3,
            'comment' => 'Average book',
        ]);

        $this->actingAs($user2);

        $response = $this->delete("/feedbacks/{$feedback->id}");

        $response->assertForbidden();
    }

    /**
     * Test feedback filtering by rating.
     */
    public function test_can_filter_feedbacks_by_rating(): void
    {
        $user = User::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'rating' => 5,
        ]);

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'rating' => 3,
        ]);

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => Book::factory()->create(),
            'rating' => 5,
        ]);

        $this->actingAs($user);

        $response = $this->get('/feedbacks?rating=5');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('feedbacks'));
    }

    /**
     * Test feedback filtering by book.
     */
    public function test_can_filter_feedbacks_by_book(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 4,
        ]);

        Feedback::factory()->create([
            'user_id' => User::factory()->create(),
            'book_id' => $book1->id,
            'rating' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get("/feedbacks?book_id={$book1->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('feedbacks'));
    }

    /**
     * Test feedback average rating calculation.
     */
    public function test_average_rating_calculation(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        Feedback::factory()->create([
            'user_id' => User::factory()->create(),
            'book_id' => $book->id,
            'rating' => 3,
        ]);

        Feedback::factory()->create([
            'user_id' => User::factory()->create(),
            'book_id' => $book->id,
            'rating' => 4,
        ]);

        $average = Feedback::where('book_id', $book->id)->avg('rating');

        $this->assertEquals(4.0, round($average, 1));
    }

    /**
     * Test feedback relationships.
     */
    public function test_feedback_relationships(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $feedback->load(['user', 'book']);

        $this->assertInstanceOf(User::class, $feedback->user);
        $this->assertInstanceOf(Book::class, $feedback->book);
        $this->assertEquals($user->id, $feedback->user->id);
        $this->assertEquals($book->id, $feedback->book->id);
    }

    /**
     * Test feedback fillable attributes.
     */
    public function test_feedback_fillable_attributes(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $feedback = Feedback::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'Test comment',
        ]);

        $this->assertEquals($user->id, $feedback->user_id);
        $this->assertEquals($book->id, $feedback->book_id);
        $this->assertEquals(4, $feedback->rating);
        $this->assertEquals('Test comment', $feedback->comment);
    }
}
