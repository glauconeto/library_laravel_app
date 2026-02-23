<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
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

    /**
     * Relationship many-to-one with Book model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && is_null($this->returned_at);
    }

    public function refreshStatus(): void
    {
        if ($this->returned_at !== null) {
            $this->status = 'returned';
            $this->save();
            return;
        }

        if ($this->due_date < now()) {
            $this->status = 'overdue';
            $this->save();
            return;
        }

        $this->status = 'pending';
        $this->save();
    }

    protected static function booted(): void
    {
        static::saving(function (Loan $loan) {
            if ($loan->returned_at !== null) {
                $loan->status = 'returned';
                return;
            }

            if ($loan->due_date < now()) {
                $loan->status = 'overdue';
                return;
            }

            $loan->status = 'pending';
        });
    } 
}
