<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignRolesToUsers()
    {
        $user = User::find(1);
        $user->assignRole('reader');

        $librarian = User::find(2);
        $librarian->assignRole('librarian');
    }
}
