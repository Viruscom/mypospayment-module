<?php

    use Illuminate\Support\Facades\Route;
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

    Route::middleware(['set.sbuuid'])->prefix('mypospayment')->group(function () {
    Route::post('/notify', [MyPosPaymentController::class, 'notify'])->name('mypospayment.notify');
    Route::post('/{orderId}/cancel', [MyPosPaymentController::class, 'cancel'])->name('mypospayment.cancel');
    Route::post('/{orderId}/success', [MyPosPaymentController::class, 'success'])->name('mypospayment.success');
});
