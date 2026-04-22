<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Auth/Login', [
        'canResetPassword' => true,
        'status' => session('status'),
    ]);
})->middleware('guest');

Route::get('/login', function () {
    return Inertia::render('Auth/Login', [
        'canResetPassword' => true,
        'status' => session('status'),
    ]);
})->middleware('guest')->name('login');

Route::get('/register', function () {
    return Inertia::render('Auth/Register');
})->middleware('guest')->name('register');

Route::get('/forgot-password', function () {
    return Inertia::render('Auth/ForgotPassword', [
        'status' => session('status'),
    ]);
})->middleware('guest')->name('password.request');

Route::get('/reset-password/{token}', function ($token) {
    return Inertia::render('Auth/ResetPassword', [
        'token' => $token,
        'email' => request('email'),
    ]);
})->middleware('guest')->name('password.reset');

Route::get('/verify-email', function () {
    return Inertia::render('Auth/VerifyEmail', [
        'status' => session('status'),
    ]);
})->middleware('auth')->name('verification.notice');

Route::get('/confirm-password', function () {
    return Inertia::render('Auth/ConfirmPassword');
})->middleware('auth')->name('password.confirm');

Route::get('/two-factor-challenge', function () {
    return Inertia::render('Auth/TwoFactorChallenge');
})->middleware('auth')->name('two-factor.login');

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Book routes
Route::resource('books', App\Http\Controllers\BookController::class)
    ->middleware('auth');

// Loan routes
Route::resource('loans', App\Http\Controllers\LoanController::class)
    ->middleware('auth');

Route::post('/books/{book}/borrow', [App\Http\Controllers\LoanController::class, 'borrow'])
    ->middleware('auth')
    ->name('books.borrow');

Route::post('/loans/{loan}/return', [App\Http\Controllers\LoanController::class, 'return'])
    ->middleware('auth')
    ->name('loans.return');

Route::post('/loans/{loan}/extend', [App\Http\Controllers\LoanController::class, 'extend'])
    ->middleware('auth')
    ->name('loans.extend');

// Feedback routes
Route::resource('feedbacks', App\Http\Controllers\FeedbackController::class)
    ->middleware('auth');

Route::post('/books/{book}/feedback', [App\Http\Controllers\FeedbackController::class, 'store'])
    ->middleware('auth')
    ->name('books.feedback.store');
