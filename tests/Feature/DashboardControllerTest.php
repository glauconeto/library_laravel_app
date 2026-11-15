<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Feedback;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;

class DashboardControllerTest extends TestCase
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

        $this->user = User::factory()->create();
        $this->librarian = User::factory()->create();
        $this->librarian->assignRole('librarian');
    }

    public function test_dashboard_requires_auth(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_as_librarian_renders_librarian_component(): void
    {
        $this->actingAs($this->librarian);
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard/Librarian', false));
    }

    public function test_dashboard_as_user_renders_user_component(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page->component('Dashboard/User', false));
    }

    public function test_librarian_dashboard_stats(): void
    {
        Book::factory()->count(3)->create();
        User::factory()->count(2)->create();

        // active loan
        $book = Book::first();
        Loan::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        // overdue loan
        Loan::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => Book::factory()->create()->id,
            'loan_date' => now()->subDays(20)->format('Y-m-d'),
            'due_date' => now()->subDays(5)->format('Y-m-d'),
            'returned_at' => null,
        ]);

        Feedback::factory()->create(['rating' => 4, 'book_id' => $book->id]);
        Feedback::factory()->create(['rating' => 5, 'book_id' => $book->id]);

        $this->actingAs($this->librarian);
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/Librarian', false)
            ->has('stats')
            ->where('stats.total_books', 4)
            ->where('stats.active_loans', 2)
            ->where('stats.overdue_loans', 1)
            ->where('stats.total_feedbacks', 2)
            ->has('recentBooks')
            ->has('recentLoans')
            ->has('overdueLoans')
            ->has('popularBooks')
        );
    }

    public function test_user_dashboard_stats_can_borrow(): void
    {
        $this->actingAs($this->user);
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/User', false)
            ->where('stats.can_borrow', true)
            ->where('stats.active_loans', 0)
            ->where('stats.max_loans', 3)
        );
    }

    public function test_user_dashboard_cannot_borrow_when_at_limit(): void
    {
        // Create 3 active loans
        for ($i = 0; $i < 3; $i++) {
            Loan::factory()->create([
                'user_id' => $this->user->id,
                'book_id' => Book::factory()->create()->id,
                'returned_at' => null,
            ]);
        }

        $this->actingAs($this->user);
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/User', false)
            ->where('stats.can_borrow', false)
            ->where('stats.active_loans', 3)
        );
    }

    public function test_user_dashboard_shows_active_loans_and_available_books(): void
    {
        $book = Book::factory()->create(['stock' => 5]);
        Loan::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id,
            'returned_at' => null,
        ]);

        Book::factory()->count(2)->create(['stock' => 5]);

        Feedback::factory()->create([
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);

        $this->actingAs($this->user);
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/User', false)
            ->has('activeLoans', 1)
            ->has('recentFeedbacks', 1)
            ->has('availableBooks')
        );
    }

    public function test_dashboard_stats_endpoint_not_exposed_without_route(): void
    {
        // Stats is not routed via web.php, so should 404
        $this->actingAs($this->user);
        $response = $this->getJson('/dashboard/stats');
        $response->assertStatus(404);
    }
}
