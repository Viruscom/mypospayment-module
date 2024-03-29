<?php

    namespace Modules\MyPosPayment\Http\Controllers;

    use Carbon\Carbon;
    use Illuminate\Routing\Controller;
    use Modules\Shop\Entities\Orders\Order;

    class MyPosPaymentController extends Controller
    {
        public function success($languageSlug, $orderId)
        {
            $order = Order::where('uid', $orderId)->first();
            if (is_null($order)) {
                return abort(404);
            }
            $order->update(['payment_status' => Order::PAYMENT_PAID, 'paid_at' => Carbon::now()]);

            return redirect(route('basket.order.preview', ['id' => $order->id]))->with('success', __('Successful update'));
        }

        public function cancel($languageSlug, $orderId)
        {
            $order = Order::where('uid', $orderId)->first();
            if (is_null($order)) {
                return abort(404);
            }
            $order->update(['payment_status' => Order::PAYMENT_CANCELED, 'paid_at' => null]);

            return redirect(route('basket.order.canceled_payment', ['id' => $order->id]))->with('success', __('Successful update'));
        }

        public function notify()
        {
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');
        }
    }
