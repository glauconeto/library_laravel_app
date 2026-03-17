<?php

namespace App\Models;

use App\Enums\LoanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'loan_date',
        'due_date',
        'returned_at'
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'loan_date' => 'date',
            'due_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && is_null($this->returned_at);
    }

    protected static function booted(): void
    {
        static::saving(function (Loan $loan) {
            if ($loan->returned_at !== null) {
                $loan->status = LoanStatus::Returned;
                return;
            }

            if ($loan->due_date < now()) {
                $loan->status = LoanStatus::Overdue;
                return;
            }

            $loan->status = LoanStatus::Pending;
        });
    }
}
