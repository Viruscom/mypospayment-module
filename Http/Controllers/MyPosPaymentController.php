<?php

namespace Modules\MyPosPayment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Shop\Entities\Orders\Order;
use Carbon\Carbon;

class MyPosPaymentController extends Controller
{  
    public function success($languageSlug, $orderId)
    {
        $order = Order::where('id', $orderId)->first();
        $order->update(['status_id' => Order::$PAYMENT_PAID, 'paid_at' => Carbon::now()]);

        return view('mypospayment::completed', compact('order'));
    }

    public function cancel($languageSlug, $orderId)
    {
        $order = Order::where('id', $orderId)->first();
        $order->update(['status_id' => Order::$PAYMENT_CANCELED, 'paid_at' => null]);

        return view('mypospayment::canceled');
    }

    public function notify()
    {
        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }
}
