<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Root: show landing to guests, redirect users to dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Keep a named route to the welcome page as well
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/forecast', [DashboardController::class, 'forecast'])->name('dashboard.forecast');
    
    // Client routes
    Route::resource('clients', ClientController::class);
    
    // Invoice routes with financial audit logging
    Route::middleware(['financial'])->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::post('/invoices/{invoice}/email', [InvoiceController::class, 'email'])->name('invoices.email');
        Route::post('/invoices/{invoice}/reminder', [InvoiceController::class, 'sendReminder'])->name('invoices.reminder');
    });
    
    // Expense routes with financial audit logging
    Route::middleware(['financial'])->group(function () {
        Route::resource('expenses', ExpenseController::class);
        Route::post('/expenses/suggest-category', [ExpenseController::class, 'suggestCategory'])->name('expenses.suggest-category');
    });
    
    // Project routes
    Route::resource('projects', ProjectController::class);

    // Quotes routes tijdelijk uitgeschakeld

    // Time entries (uren)
    Route::get('time-entries', [TimeEntryController::class, 'index'])->name('time-entries.index');
    Route::post('time-entries', [TimeEntryController::class, 'store'])->name('time-entries.store');
    
    // Payment routes with financial audit logging
    Route::middleware(['financial'])->group(function () {
        Route::resource('payments', PaymentController::class);
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Language switching
    Route::post('/language', [LanguageController::class, 'switch'])->name('language.switch');
});

require __DIR__.'/auth.php';
