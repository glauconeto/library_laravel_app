<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'genre',
        'year',
        'isbn',
        'stock',
        'description',
        'user_id'
    ];

    /** @return HasMany<Loan, $this> */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /** @return HasMany<Feedback, $this> */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Determine if given book is available for loan.
     * 
     * @return bool
     */
    public function isAvailableForLoan(): bool
    {
        $activeLoans = $this->loans()
            ->whereNull('returned_at')
            ->count();

        return $this->stock > $activeLoans;
    }
}
