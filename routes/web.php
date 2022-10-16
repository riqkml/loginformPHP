<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [AuthController::class, 'getLoginPage'])->name('loginPage');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name("login");
Route::get('/forgot', [AuthController::class, 'forgotPage'])->name('forgotPage');
// Route::get('page',function(req))
