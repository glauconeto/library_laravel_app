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
     * Relashionship one-to-many wih Book model.
     *
     * @return void
     */
    public function book()
    {
        return $this->hasMany(Book::class);
    }

    public function isOverdue(): bool
    {
        return $this->due_date < now() && is_null($this->returned_at);
    }

    public function isAvailable(): bool
    {
        $activeLoans = $this->where('book_id', $this->book_id)
            ->whereNull('returned_at')
            ->exists();

        return !$activeLoans;
    }
}
