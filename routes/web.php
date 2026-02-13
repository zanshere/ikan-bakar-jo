<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\RestockController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes - untuk user yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes - untuk user yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/quick-stats', [DashboardController::class, 'getQuickStats'])->name('dashboard.quick-stats');

    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    // ==================== MENU MANAGEMENT ====================
    Route::prefix('menus')->name('menus.')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::get('/create', [MenuController::class, 'create'])->name('create');
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::get('/{menu}', [MenuController::class, 'show'])->name('show');
        Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
        Route::post('/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/get/details', [MenuController::class, 'getMenuDetails'])->name('get.details');
    });

    // ==================== INGREDIENT MANAGEMENT ====================
    Route::prefix('ingredients')->name('ingredients.')->group(function () {
        Route::get('/', [IngredientController::class, 'index'])->name('index');
        Route::get('/create', [IngredientController::class, 'create'])->name('create');
        Route::post('/', [IngredientController::class, 'store'])->name('store');
        Route::get('/{ingredient}', [IngredientController::class, 'show'])->name('show');
        Route::get('/{ingredient}/edit', [IngredientController::class, 'edit'])->name('edit');
        Route::put('/{ingredient}', [IngredientController::class, 'update'])->name('update');
        Route::delete('/{ingredient}', [IngredientController::class, 'destroy'])->name('destroy');
        Route::post('/{ingredient}/adjust-stock', [IngredientController::class, 'adjustStock'])->name('adjust-stock');
        Route::get('/get/list', [IngredientController::class, 'getIngredients'])->name('get.list');
        Route::get('/get/details', [IngredientController::class, 'getIngredientDetails'])->name('get.details');
    });

    // ==================== SALES MANAGEMENT ====================
    // ROUTE KHUSUS UNTUK REPORT - DILUAR PREFIX GROUP
    // Ini adalah SOLUSI PALING AMAN - letakkan di luar prefix group
    Route::get('/sales/daily-report', [SaleController::class, 'getDailyReport'])->name('sales.daily-report');
    Route::get('/sales/daily-report/print', [SaleController::class, 'printDailyReport'])->name('sales.daily-report.print');

    // Route dengan prefix group untuk resource sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/create', [SaleController::class, 'create'])->name('create');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');
        Route::post('/add-item', [SaleController::class, 'addItem'])->name('add-item');
        Route::delete('/remove-item/{id}', [SaleController::class, 'removeItem'])->name('remove-item');
        Route::get('/{sale}/print', [SaleController::class, 'printReceipt'])->name('print');
    });

    // ==================== RESTOCK MANAGEMENT ====================
    // ROUTE KHUSUS UNTUK REPORT - DILUAR PREFIX GROUP
    // Ini adalah SOLUSI PALING AMAN - letakkan di luar prefix group
    Route::get('/restocks/monthly-report', [RestockController::class, 'getMonthlyReport'])->name('restocks.monthly-report');
    Route::get('/restocks/monthly-report/print', [RestockController::class, 'printMonthlyReport'])->name('restocks.monthly-report.print');

    // Route dengan prefix group untuk resource restocks
    Route::prefix('restocks')->name('restocks.')->group(function () {
        Route::get('/', [RestockController::class, 'index'])->name('index');
        Route::get('/create', [RestockController::class, 'create'])->name('create');
        Route::post('/', [RestockController::class, 'store'])->name('store');
        Route::get('/{restock}', [RestockController::class, 'show'])->name('show');
        Route::delete('/{restock}', [RestockController::class, 'destroy'])->name('destroy');
        Route::post('/add-item', [RestockController::class, 'addItem'])->name('add-item');
        Route::delete('/remove-item/{id}', [RestockController::class, 'removeItem'])->name('remove-item');
    });

    // ==================== REPORTS MANAGEMENT ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/income', [ReportController::class, 'income'])->name('income');
        Route::get('/expense', [ReportController::class, 'expense'])->name('expense');
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::post('/export', [ReportController::class, 'export'])->name('export');
        Route::get('/income/pdf/{startDate}/{endDate}', [ReportController::class, 'exportIncomePdf'])->name('income.pdf');
        Route::get('/expense/pdf/{startDate}/{endDate}', [ReportController::class, 'exportExpensePdf'])->name('expense.pdf');
        Route::get('/profit/pdf/{startDate}/{endDate}', [ReportController::class, 'exportProfitPdf'])->name('profit.pdf');
        Route::get('/stock/pdf', [ReportController::class, 'exportStockPdf'])->name('stock.pdf');
    });
});
