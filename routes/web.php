<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinePaymentController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/belajar', 'belajar')->name('learning');
    Route::resource('books', BookController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('members', MemberController::class);
    Route::resource('loans', LoanController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
    Route::post('/loans/{loan}/fine-payments', [FinePaymentController::class, 'store'])->name('fine-payments.store');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::middleware('can:manage-users')->group(function (): void {
        Route::resource('users', UserController::class)->except(['show']);
    });
});
