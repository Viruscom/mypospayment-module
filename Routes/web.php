<?php

use Modules\MyPosPayment\Http\Controllers\MyPosPaymentController;

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

Route::prefix('mypospayment')->group(function() {
    Route::get('/', 'MyPosPaymentController@index');
});

Route::group(['prefix' => 'mypospayment'], static function () {
    Route::post('/notify', [MyPosPaymentController::class, 'notify'])->name('mypospayment.notify');
    Route::post('/cancel', [MyPosPaymentController::class, 'cancel'])->name('mypospayment.cancel');
    Route::post('/success', [MyPosPaymentController::class, 'success'])->name('mypospayment.success');
});