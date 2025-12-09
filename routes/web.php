<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\FinanceDashboardController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;

// Health check route for Render
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'database' => config('database.default'),
        'environment' => config('app.env')
    ]);
});

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Employee routes
Route::middleware(['auth', 'verified', 'employee', 'password_reset_required'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');

    // Claims routes for employees
    Route::resource('claims', ClaimController::class)->names([
        'index' => 'claims.index',
        'create' => 'claims.create',
        'store' => 'claims.store',
        'show' => 'claims.show',
        'edit' => 'claims.edit',
        'update' => 'claims.update',
        'destroy' => 'claims.destroy',
    ]);

    Route::post('/claims/{claim}/submit', [ClaimController::class, 'submit'])->name('claims.submit');
    Route::post('/claims/mark-all-read', [ClaimController::class, 'markAllReadEmployee'])->name('claims.mark-all-read');
    Route::get('/claims/{claim}/print', [ClaimController::class, 'printEmployeeClaim'])->name('claims.print');
    Route::post('/claims/export', [ClaimController::class, 'exportEmployeeClaims'])->name('claims.export');
    Route::post('/claims/export-pdf', [ClaimController::class, 'exportEmployeeClaimsPdf'])->name('claims.export-pdf');

    // Receipt routes
    Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
    Route::get('/receipts/{receipt}/preview', [ReceiptController::class, 'preview'])->name('receipts.preview');
    Route::get('/receipts/{receipt}/info', [ReceiptController::class, 'getReceiptInfo'])->name('receipts.info');
    Route::post('/receipts/{receipt}/set-primary', [ReceiptController::class, 'setPrimary'])->name('receipts.set-primary');
    Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
});

// Finance Admin routes
Route::middleware(['auth', 'verified', 'finance_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');

    // User management for finance admins
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\UserManagementController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [\App\Http\Controllers\Admin\UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/reset-password', [\App\Http\Controllers\Admin\UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/activate', [\App\Http\Controllers\Admin\UserManagementController::class, 'activate'])->name('activate');
        Route::get('/generate-email', [\App\Http\Controllers\Admin\UserManagementController::class, 'generateEmail'])->name('generate-email');
    });

    // Claims management for finance admins
    Route::get('/claims', [ClaimController::class, 'adminIndex'])->name('claims.index');
    Route::get('/claims/{claim}', [ClaimController::class, 'adminShow'])->name('claims.show');
    Route::get('/claims/{claim}/print', [ClaimController::class, 'printAdminClaim'])->name('claims.print');
    Route::post('/claims/export', [ClaimController::class, 'exportAdminClaims'])->name('claims.export');
    Route::post('/claims/export-pdf', [ClaimController::class, 'exportAdminClaimsPdf'])->name('claims.export-pdf');
    Route::post('/claims/{claim}/approve', [ClaimController::class, 'approve'])->name('claims.approve');
    Route::post('/claims/{claim}/reject', [ClaimController::class, 'reject'])->name('claims.reject');
    Route::post('/claims/mark-all-read', [ClaimController::class, 'markAllReadAdmin'])->name('claims.mark-all-read');

    // Receipt routes for admins
    Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
    Route::get('/receipts/{receipt}/preview', [ReceiptController::class, 'preview'])->name('receipts.preview');
    Route::get('/receipts/{receipt}/info', [ReceiptController::class, 'getReceiptInfo'])->name('receipts.info');
    Route::post('/receipts/{receipt}/set-primary', [ReceiptController::class, 'setPrimary'])->name('receipts.set-primary');
    Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
});

// Role-based dashboard redirect
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isEmployee()) {
        return redirect()->route('employee.dashboard');
    } elseif ($user->isFinanceAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    abort(403, 'Unauthorized role');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
