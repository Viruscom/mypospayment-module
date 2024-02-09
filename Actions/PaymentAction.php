<?php

    namespace Modules\MyPosPayment\Actions;

    use App\Helpers\LanguageHelper;
    use Exception;
    use Modules\MyPosPayment\Entities\MyPos;
    use Modules\Shop\Entities\Orders\Order;

    class PaymentAction
    {

        public static function initPayment(Order $order)
        {
            if (empty($order)) {
                return ['result' => false, 'error' => 'Invalid order'];
            }

            $payment = null;
            try {
                $lang     = LanguageHelper::getCurrentLanguage()->code;
                $currency = $order->country->currency;
                $payment  = new MyPos($lang, $currency);
                $customer = [
                    'firstName'    => $order->first_name,
                    'lastName'     => $order->last_name,
                    'email'        => $order->email,
                    'phone'        => $order->phone,
                    'countryCode3' => $order->country->iso3,
                    'address'      => $order->street . ' ' . $order->street_number,

                    'city' => $order->city->name,
                    'zip'  => $order->zip_code,
                ];

                $cartItems = [];
                foreach ($order->order_products as $orderProduct) {
                    $cartItem = ['name' => $orderProduct->product->title, 'quantity' => $orderProduct->product_quantity, 'price' => $orderProduct->vat_applied_discounted_price];
                    array_push($cartItems, $cartItem);
                }

                $payment->setConfig();
                $payment->createCustomer($customer);
                $payment->createCart($cartItems);
                $payment->makePurchase($order);
            } catch (Exception $e) {
                return ['result' => false, 'error' => $e->getMessage()];
            }
        }
    }

