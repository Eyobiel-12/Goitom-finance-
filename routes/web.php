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
// Admin impersonation routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/impersonate/{user}', [App\Http\Controllers\ImpersonationController::class, 'impersonate'])
        ->name('admin.impersonate');
    Route::post('/stop-impersonation', [App\Http\Controllers\ImpersonationController::class, 'stopImpersonation'])
        ->name('admin.stop-impersonation');
});

// Admin OTP routes (passwordless login for Filament)
Route::prefix('admin')->middleware('web')->group(function () {
    Route::post('/otp/send-login', [App\Http\Controllers\OtpController::class, 'sendLoginOtp'])
        ->name('admin.otp.send-login');
    Route::post('/otp/verify-login', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);
        $service = app(\App\Services\OtpService::class);
        $result = $service->verifyLoginOtp($request->email, $request->code);
        if (!($result['success'] ?? false)) {
            return response()->json($result, 422);
        }
        // Login via Filament guard
        \Filament\Facades\Filament::auth()->login($result['user']);
        $request->session()->regenerate();
        return response()->json([
            'success' => true,
            'redirect' => url('/admin'),
        ]);
    })->name('admin.otp.verify-login');
});

// OTP routes - moved inside web middleware group for CSRF protection
Route::middleware('web')->group(function () {
    Route::post('/otp/send-login', [App\Http\Controllers\OtpController::class, 'sendLoginOtp'])->name('otp.send-login');
    Route::post('/otp/send-registration', [App\Http\Controllers\OtpController::class, 'sendRegistrationOtp'])->name('otp.send-registration');
    Route::post('/otp/verify-login', [App\Http\Controllers\OtpController::class, 'verifyLoginOtp'])->name('otp.verify-login');
    Route::post('/otp/verify-registration', [App\Http\Controllers\OtpController::class, 'verifyRegistrationOtp'])->name('otp.verify-registration');
    Route::post('/otp/resend', [App\Http\Controllers\OtpController::class, 'resendOtp'])->name('otp.resend');
});

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
