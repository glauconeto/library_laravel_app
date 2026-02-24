<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-role', function () {
    $user = User::where('email', 'librarian@example.com')->first();
    return $user?->hasRole('librarian') ? 'ok' : 'no';
});