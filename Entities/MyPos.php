<?php

    namespace Modules\MyPosPayment\Entities;

    use Exception;
    use Modules\Shop\Entities\Settings\Payment;
    use Mypos\IPC\Cart;
    use Mypos\IPC\Config;
    use Mypos\IPC\Customer;
    use Mypos\IPC\IPC_Exception;
    use Mypos\IPC\Purchase;

    class MyPos
    {
        protected $privateKey;   // = 'TEST';
        protected $publicKey; //= ['TEST' => 'https://mypos.com/vmp/checkout-test/', 'PRODUCTION' => 'https://mypos.com/vmp/checkout/'];
        private   $environment;
        private   $environmentUrl;      //= '000000000000010';
        private   $cnf;      //= '61938166610';
        private   $cnfShopId;     //= '1.4';
        private   $cnfWallet;    //= '1';
        private   $cnfVersion;
        private   $cnfKeyIndex;
        private   $customer;
        private   $cart;
        private   $purchase;
        private   $lang;
        private   $currency;

        function __construct($lang, $currency)
        {
            $this->lang     = $lang;
            $this->currency = $currency;
            $this->loadParams();
        }

        public function loadParams()
        {
            $payment = Payment::where('type', 'mypos')->get()->first();
            if (is_null($payment)) {
                throw new Exception(trans('payment.no_mypos_params'));
            }

            $data = json_decode($payment->data, true);
            if (!isset($data['environment']) || !isset($data['environment_url'])
                || !isset($data['shop_id']) || !isset($data['wallet'])
                || !isset($data['key_index']) || !isset($data['version'])
                || !isset($data['private_key']) || !isset($data['public_key'])) {
                throw new Exception(trans('payment.no_mypos_params'));
            }

            $this->environment    = $data['environment'];
            $this->environmentUrl = $data['environment_url'];
            $this->cnfShopId      = $data['shop_id'];
            $this->cnfWallet      = $data['wallet'];
            $this->cnfVersion     = $data['version'];
            $this->cnfKeyIndex    = $data['key_index'];
            $this->privateKey     = $data['private_key']; //$this->trimValue($data['private_key']);
            $this->publicKey      = $data['public_key']; //$this->trimValue($data['public_key']);
        }
        private function trimValue($value): string
        {
            return trim($value);
        }
        public function setConfig()
        {
            $cnf = new Config();
            $cnf->setIpcURL($this->environmentUrl);
            $cnf->setLang($this->lang);
            $cnf->setPrivateKey($this->privateKey);
            $cnf->setAPIPublicKey($this->publicKey);
            $cnf->setKeyIndex($this->getCnfKeyIndex());
            $cnf->setSid($this->getCnfShopId());
            $cnf->setVersion($this->getCnfVersion());
            $cnf->setWallet($this->getCnfWallet());

            $this->setCnf($cnf);
        }
        /**
         * @return string
         */
        public function getCnfKeyIndex(): string
        {
            return $this->cnfKeyIndex;
        }
        /**
         * @param string $cnfKeyIndex
         */
        public function setCnfKeyIndex(string $cnfKeyIndex): void
        {
            $this->cnfKeyIndex = $cnfKeyIndex;
        }
        /**
         * @return string
         */
        public function getCnfShopId(): string
        {
            return $this->cnfShopId;
        }
        /**
         * @param string $cnfShopId
         */
        public function setCnfShopId(string $cnfShopId): void
        {
            $this->cnfShopId = $cnfShopId;
        }
        /**
         * @return string
         */

        public function getCnfVersion(): string
        {
            return $this->cnfVersion;
        }
        /**
         * @param string $cnfVersion
         */
        public function setCnfVersion(string $cnfVersion): void
        {
            $this->cnfVersion = $cnfVersion;
        }
        /**
         * @return string
         */
        public function getCnfWallet(): string
        {
            return $this->cnfWallet;
        }
        /**
         * @param string $cnfWallet
         */
        public function setCnfWallet(string $cnfWallet): void
        {
            $this->cnfWallet = $cnfWallet;
        }
        public function createCustomer($array): void
        {
            $customer = new Customer();
            $customer->setFirstName($array['firstName']);
            $customer->setLastName($array['lastName']);
            $customer->setEmail($array['email']);
            $customer->setPhone($array['phone']);
            $customer->setCountry($array['countryCode3']);
            $customer->setAddress($array['address']);
            $customer->setCity($array['city']);
            $customer->setZip($array['zip']);

            $this->setCustomer($customer);
        }
        public function createCart($cartItemsArray): void
        {
            $cart = new Cart();
            foreach ($cartItemsArray as $cartItem) {
                $cart->add($cartItem['name'], $cartItem['quantity'], $cartItem['price']);
            }

            $this->setCart($cart);
        }
        public function makePurchase($order)
        {
            $purchase = new Purchase($this->getCnf());
            $purchase->setUrlCancel(route('mypospayment.cancel', ['languageSlug' => $this->lang, 'orderId' => $order->uid]));
            $purchase->setUrlOk(route('mypospayment.success', ['languageSlug' => $this->lang, 'orderId' => $order->uid]));
            $purchase->setUrlNotify(route('mypospayment.notify', ['languageSlug' => $this->lang]));
            $purchase->setOrderID($order->uid); //Some unique ID
            $purchase->setCurrency($this->currency);
            $purchase->setCustomer($this->getCustomer());
            $purchase->setCart($this->getCart());
            $purchase->setCardTokenRequest(Purchase::CARD_TOKEN_REQUEST_PAY_AND_STORE);
            $purchase->setPaymentParametersRequired(Purchase::PURCHASE_TYPE_FULL);
            $purchase->setPaymentMethod(Purchase::PAYMENT_METHOD_BOTH);

            try {
                $purchase->process();
            } catch (IPC_Exception $ex) {
                echo $ex->getMessage();
            }
        }
        /**
         * @return mixed
         */
        public function getCnf()
        {
            return $this->cnf;
        }
        /**
         * @param mixed $cnf
         */
        public function setCnf($cnf): void
        {
            $this->cnf = $cnf;
        }
        /**
         * @return mixed
         */
        public function getCustomer()
        {
            return $this->customer;
        }
        /**
         * @param mixed $customer
         */
        public function setCustomer($customer): void
        {
            $this->customer = $customer;
        }
        /**
         * @return mixed
         */
        public function getCart()
        {
            return $this->cart;
        }
        /**
         * @param mixed $cart
         */
        public function setCart($cart): void
        {
            $this->cart = $cart;
        }
    }
