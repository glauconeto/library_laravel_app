<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function assignRolesToUsers()
    {
        $user = User::find(1); // Suponha que tenha um usuário com ID 1
        $user->assignRole('reader'); // Atribuindo a role de "leitor" ao usuário

        $librarian = User::find(2); // Suponha que tenha um usuário com ID 2
        $librarian->assignRole('librarian'); // Atribuindo a role de "bibliotecário"
    }
}
