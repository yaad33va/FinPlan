<?php

use Illuminate\Support\Facades\Route;

/*
| User interface pages. Each Blade view is only an HTML shell: the data is loaded by
| JavaScript from the REST API (JSON) with the JWT access token, see resources/js.
| Who may open a page is decided in the browser (data-auth attribute); the API itself
| enforces the real access rules.
*/

Route::view('/', 'pages.landing')->name('home');
Route::view('/login', 'pages.login')->name('login');
Route::view('/register', 'pages.register')->name('register');

Route::view('/dashboard', 'pages.dashboard')->name('pages.dashboard');
Route::view('/categories', 'pages.categories')->name('pages.categories');
Route::get('/categories/{category}', fn (int $category) => view('pages.category', ['categoryId' => $category]))
    ->whereNumber('category')
    ->name('pages.category');
Route::get('/categories/{category}/budgets/{budget}', fn (int $category, int $budget) => view('pages.budget', [
    'categoryId' => $category,
    'budgetId' => $budget,
]))->whereNumber(['category', 'budget'])->name('pages.budget');
Route::view('/profile', 'pages.profile')->name('pages.profile');
Route::view('/admin/users', 'pages.admin-users')->name('pages.admin.users');

Route::view('/docs', 'docs')->name('docs');
