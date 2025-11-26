<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'author',
        'gender',
        'year',
        'ISBN',
        'stock'
    ];

    protected $guarded = [];

    /**
     * Relationship one-to-many with loans model.
     *
     * @return void
     */
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relationship one-to-many with feedbacks model.
     *
     * @return void
     */
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Determine if given book is available for loan.
     *
     * @return boolean copies_available
     */
    public function isAvailableForLoan(): bool
    {
        return $this->copies_available > 0;
    }

    /**
     * Method to reserve a book
     *
     * @return void
     */
    public function reserve(): void
    {
        if ($this->isAvailableForLoan()) {
            $this->copies_available -= 1;
            $this->save();
        }
    }

    /**
     * Method to return book to stock.
     *
     * @return void
     */
    public function returnBook(): void
    {
        $this->copies_available += 1;
        $this->save();
    }
}
