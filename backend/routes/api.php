<?php

use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\FinancialReportController;
use App\Http\Controllers\Api\PersonalDataController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Sales_transactionsController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\Stock_receiptsController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('product')->controller(ProductController::class)->group(function () {
    Route::post('/create', 'createProduct');
    Route::get('/get', 'getProduct');
    Route::get('/search', 'searchProduct');
    Route::post('/edit/{id}', 'editProduct');
    Route::post('/editSellingPrice/{id}', 'editSellingPrice');
    Route::patch('/delete/{id}', 'deleteProduct');
});

Route::prefix('stock_receipt')->controller(Stock_receiptsController::class)->group(function () {
    Route::post('/create', 'createStockReceipts');
});

Route::prefix('supplier')->controller(SupplierController::class)->group(function () {
    Route::post('/create', 'createSupplier');
    Route::get('/get', 'getSupplier');
    Route::get('/search', 'searchSupplier');
    Route::post('/edit/{id}', 'editSupplier');
    Route::patch('/delete/{id}', 'deleteSupplier');
});

Route::prefix('category')->controller(CategorieController::class)->group(function () {
    Route::post('/create', 'createCategory');
    Route::get('/get', 'getCategory');
    Route::get('/search', 'searchCategory');
    Route::post('/edit/{id}', 'editCategory');
    Route::patch('/delete/{id}', 'deleteCategory');
});

Route::post('/sales/create', [SalesController::class, 'createSales']);
Route::get('/sales/get', [SalesController::class, 'getSales']);
Route::get('/sales/search', [SalesController::class, 'searchSales']);
Route::post('/sales/edit/{id}', [SalesController::class, 'editSales']);
Route::patch('/sales/delete/{id}', [SalesController::class, 'deleteSales']);


Route::post('/unit/create', [UnitController::class, 'createUnit']);
Route::get('/unit/get', [UnitController::class, 'getUnit']);
Route::get('/unit/search', [UnitController::class, 'searchUnit']);
Route::post('/unit/edit/{id}', [UnitController::class, 'editUnit']);
Route::patch('/unit/delete/{id}', [UnitController::class, 'deleteUnit']);

Route::post('/login', [UserController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::patch('/delete/{uuid}', [UserController::class, 'delete']);
    Route::post('/logout', [UserController::class, 'logout']);

    Route::prefix('sales_transactions')->controller(Sales_transactionsController::class)->group(function () {
        Route::post('/create', 'createSalesTransaction');
    });

    Route::prefix('return')->controller(ReturnController::class)->group(function () {
        Route::post('/create', 'createReturn');
    });

    Route::post('/role/create', [RoleController::class, 'createRole']);
    Route::get('/role/get', [RoleController::class, 'getRole']);
    Route::patch('/role/edit/{id}', [RoleController::class, 'editRole']);
    Route::get('/role/search', [RoleController::class, 'searchRole']);
    Route::put('/role/delete/{id}', [RoleController::class, 'delete']);

    Route::post('/personal-data/create', [PersonalDataController::class, 'create']);
    Route::get('/personal-data/get', [PersonalDataController::class, 'getPersonalData']);
    Route::post('/personal-data/edit', [PersonalDataController::class, 'editPersonalData']);
    Route::get('/personal-data/search', [PersonalDataController::class, 'searchPersonalData']);
    Route::post('/personal-data/delete', [PersonalDataController::class, 'deletePersonalData']);

    Route::get('/financialReport/create', [FinancialReportController::class, 'financialReport']);
});
