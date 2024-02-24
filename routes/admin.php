<?php

use App\Http\Controllers\AdminControllers\AuthController;
use App\Http\Controllers\AdminControllers\DashboardController;
use App\Http\Controllers\AdminControllers\UserController;
use App\Http\Controllers\AdminControllers\VendorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;




/*------------------------------------------
--------------------------------------------
All Admin Routes List
--------------------------------------------
--------------------------------------------*/

Route::post('/login', [AuthController::class, 'login'])->name('admin.login');
Route::middleware(['auth', 'user-access:admin'])->namespace('App\Http\Controllers\AdminControllers')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('admin.dashboard');

    //Users Routes
    Route::resource('users', 'UserController');
    Route::put('/user/update/password/{user_id}', [UserController::class, 'update_password'])->name('users.update.password');
    Route::resource('vendors', 'VendorController');
    Route::post('/quickChange', [VendorController::class, 'quickChange'])->name('admin.quickChange');
    Route::resource('cars', 'CarController');
    Route::resource('brands', 'BrandModelController');
    Route::get('model_of_brand/destroy/{id}', [\App\Http\Controllers\AdminControllers\BrandModelController::class,'modelDestroy'])->name('model.destroy');
    Route::resource('tanants', 'TanantController');
    Route::resource('settings', 'SettingController');
    Route::resource('reports', 'ReportController');
    Route::post('reports/report', [\App\Http\Controllers\AdminControllers\ReportController::class,'report'])->name('reports.report');
    Route::resource('seos', 'SeoController');
    Route::resource('invoices', 'InvoiceController');

});
