<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Feedback;
use App\Models\Loan;
use App\Models\User;
use App\Policies\BookPolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\LoanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test BookPolicy viewAny.
     */
    public function test_book_policy_view_any(): void
    {
        $user = User::factory()->create();
        $policy = new BookPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    /**
     * Test BookPolicy view.
     */
    public function test_book_policy_view(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $policy = new BookPolicy();

        $this->assertTrue($policy->view($user, $book));
    }

    /**
     * Test BookPolicy create - regular user cannot create.
     */
    public function test_book_policy_create_regular_user_cannot_create(): void
    {
        $user = User::factory()->create();
        $policy = new BookPolicy();

        $this->assertFalse($policy->create($user));
    }

    /**
     * Test BookPolicy create - librarian can create.
     */
    public function test_book_policy_create_librarian_can_create(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $policy = new BookPolicy();

        $this->assertTrue($policy->create($user));
    }

    /**
     * Test BookPolicy update - regular user cannot update.
     */
    public function test_book_policy_update_regular_user_cannot_update(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $policy = new BookPolicy();

        $this->assertFalse($policy->update($user, $book));
    }

    /**
     * Test BookPolicy update - librarian can update.
     */
    public function test_book_policy_update_librarian_can_update(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $book = Book::factory()->create();
        $policy = new BookPolicy();

        $this->assertTrue($policy->update($user, $book));
    }

    /**
     * Test BookPolicy delete - regular user cannot delete.
     */
    public function test_book_policy_delete_regular_user_cannot_delete(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $policy = new BookPolicy();

        $this->assertFalse($policy->delete($user, $book));
    }

    /**
     * Test BookPolicy delete - librarian cannot delete book with active loans.
     */
    public function test_book_policy_delete_librarian_cannot_delete_with_active_loans(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $book = Book::factory()->create();

        Loan::factory()->create([
            'user_id' => User::factory()->create(),
            'book_id' => $book->id,
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);

        $policy = new BookPolicy();

        $this->assertFalse($policy->delete($user, $book));
    }

    /**
     * Test BookPolicy delete - librarian can delete book without active loans.
     */
    public function test_book_policy_delete_librarian_can_delete_without_active_loans(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $book = Book::factory()->create();
        $policy = new BookPolicy();

        $this->assertTrue($policy->delete($user, $book));
    }

    /**
     * Test BookPolicy delete - librarian can delete book with returned loans.
     */
    public function test_book_policy_delete_librarian_can_delete_with_returned_loans(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $book = Book::factory()->create();

        Loan::factory()->returned()->create([
            'user_id' => User::factory()->create(),
            'book_id' => $book->id,
        ]);

        $policy = new BookPolicy();

        $this->assertTrue($policy->delete($user, $book));
    }

    /**
     * Test LoanPolicy viewAny.
     */
    public function test_loan_policy_view_any(): void
    {
        $user = User::factory()->create();
        $policy = new LoanPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    /**
     * Test LoanPolicy view - user can view own loan.
     */
    public function test_loan_policy_view_user_can_view_own_loan(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create(['user_id' => $user->id]);
        $policy = new LoanPolicy();

        $this->assertTrue($policy->view($user, $loan));
    }

    /**
     * Test LoanPolicy view - user cannot view other user's loan.
     */
    public function test_loan_policy_view_user_cannot_view_others_loan(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create();
        $policy = new LoanPolicy();

        $this->assertFalse($policy->view($user, $loan));
    }

    /**
     * Test LoanPolicy view - librarian can view any loan.
     */
    public function test_loan_policy_view_librarian_can_view_any_loan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $loan = Loan::factory()->create();
        $policy = new LoanPolicy();

        $this->assertTrue($policy->view($user, $loan));
    }

    /**
     * Test LoanPolicy create - user with less than 3 active loans can create.
     */
    public function test_loan_policy_create_user_with_less_than_3_loans_can_create(): void
    {
        $user = User::factory()->create();
        $policy = new LoanPolicy();

        $this->assertTrue($policy->create($user));
    }

    /**
     * Test LoanPolicy create - user with 3 active loans cannot create.
     */
    public function test_loan_policy_create_user_with_3_loans_cannot_create(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            Loan::factory()->create([
                'user_id' => $user->id,
                'book_id' => Book::factory()->create(),
                'loan_date' => now(),
                'due_date' => now()->addDays(14),
                'returned_at' => null,
            ]);
        }

        $user->refresh();
        $policy = new LoanPolicy();

        $this->assertFalse($policy->create($user));
    }

    /**
     * Test LoanPolicy create - user with returned loans can still create.
     */
    public function test_loan_policy_create_user_with_returned_loans_can_create(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            Loan::factory()->returned()->create([
                'user_id' => $user->id,
                'book_id' => Book::factory()->create(),
            ]);
        }

        $user->refresh();
        $policy = new LoanPolicy();

        $this->assertTrue($policy->create($user));
    }

    /**
     * Test LoanPolicy update - always false.
     */
    public function test_loan_policy_update_always_false(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create();
        $policy = new LoanPolicy();

        $this->assertFalse($policy->update($user, $loan));
    }

    /**
     * Test LoanPolicy delete - regular user cannot delete.
     */
    public function test_loan_policy_delete_regular_user_cannot_delete(): void
    {
        $user = User::factory()->create();
        $loan = Loan::factory()->create();
        $policy = new LoanPolicy();

        $this->assertFalse($policy->delete($user, $loan));
    }

    /**
     * Test LoanPolicy delete - librarian can delete returned loan.
     */
    public function test_loan_policy_delete_librarian_can_delete_returned_loan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $loan = Loan::factory()->returned()->create();
        $policy = new LoanPolicy();

        $this->assertTrue($policy->delete($user, $loan));
    }

    /**
     * Test LoanPolicy delete - librarian cannot delete active loan.
     */
    public function test_loan_policy_delete_librarian_cannot_delete_active_loan(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');
        $loan = Loan::factory()->create([
            'user_id' => User::factory()->create(),
            'book_id' => Book::factory()->create(),
            'loan_date' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => null,
        ]);
        $policy = new LoanPolicy();

        $this->assertFalse($policy->delete($user, $loan));
    }

    /**
     * Test FeedbackPolicy viewAny.
     */
    public function test_feedback_policy_view_any(): void
    {
        $user = User::factory()->create();
        $policy = new FeedbackPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    /**
     * Test FeedbackPolicy view.
     */
    public function test_feedback_policy_view(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create();
        $policy = new FeedbackPolicy();

        $this->assertTrue($policy->view($user, $feedback));
    }

    /**
     * Test FeedbackPolicy create - user who never returned the book cannot create.
     */
    public function test_feedback_policy_create_without_returned_loan_is_false(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $policy = new FeedbackPolicy();

        $this->assertFalse($policy->create($user, $book));

        $user->assignRole('librarian');
        $this->assertFalse($policy->create($user));
    }

    /**
     * Test FeedbackPolicy update - user can update own feedback.
     */
    public function test_feedback_policy_update_user_can_update_own_feedback(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $user->id]);
        $policy = new FeedbackPolicy();

        $this->assertTrue($policy->update($user, $feedback));
    }

    /**
     * Test FeedbackPolicy update - user cannot update other user's feedback.
     */
    public function test_feedback_policy_update_user_cannot_update_others_feedback(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create();
        $policy = new FeedbackPolicy();

        $this->assertFalse($policy->update($user, $feedback));
    }

    /**
     * Test FeedbackPolicy delete - user can delete own feedback.
     */
    public function test_feedback_policy_delete_user_can_delete_own_feedback(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create(['user_id' => $user->id]);
        $policy = new FeedbackPolicy();

        $this->assertTrue($policy->delete($user, $feedback));
    }

    /**
     * Test FeedbackPolicy delete - user cannot delete other user's feedback.
     */
    public function test_feedback_policy_delete_user_cannot_delete_others_feedback(): void
    {
        $user = User::factory()->create();
        $feedback = Feedback::factory()->create();
        $policy = new FeedbackPolicy();

        $this->assertFalse($policy->delete($user, $feedback));
    }

    /**
     * Test all restore policies return false.
     */
    public function test_all_restore_policies_return_false(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');

        $book = Book::factory()->create();
        $loan = Loan::factory()->create();
        $feedback = Feedback::factory()->create();

        $bookPolicy = new BookPolicy();
        $loanPolicy = new LoanPolicy();
        $feedbackPolicy = new FeedbackPolicy();

        $this->assertFalse($bookPolicy->restore($user, $book));
        $this->assertFalse($loanPolicy->restore($user, $loan));
        $this->assertFalse($feedbackPolicy->restore($user, $feedback));
    }

    /**
     * Test all forceDelete policies return false.
     */
    public function test_all_force_delete_policies_return_false(): void
    {
        $user = User::factory()->create();
        $user->assignRole('librarian');

        $book = Book::factory()->create();
        $loan = Loan::factory()->create();
        $feedback = Feedback::factory()->create();

        $bookPolicy = new BookPolicy();
        $loanPolicy = new LoanPolicy();
        $feedbackPolicy = new FeedbackPolicy();

        $this->assertFalse($bookPolicy->forceDelete($user, $book));
        $this->assertFalse($loanPolicy->forceDelete($user, $loan));
        $this->assertFalse($feedbackPolicy->forceDelete($user, $feedback));
    }
}
