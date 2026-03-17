<?php

namespace App\Enums;

enum LoanStatus: string
{
    case Pending = 'pending';
    case Returned = 'returned';
    case Overdue = 'overdue';
}
