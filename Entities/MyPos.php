<?php

namespace Modules\MyPosPayment\Entities;

use Mypos\IPC\Cart;
use Mypos\IPC\Config;
use Mypos\IPC\Customer;
use Mypos\IPC\IPC_Exception;
use Mypos\IPC\Purchase;

use Modules\Shop\Entities\Settings\Payment;

class MyPos
{
    private    $environment;   // = 'TEST';
    private    $environmentUrl; //= ['TEST' => 'https://mypos.com/vmp/checkout-test/', 'PRODUCTION' => 'https://mypos.com/vmp/checkout/'];
    private    $cnf;
    private    $cnfShopId;      //= '000000000000010';
    private    $cnfWallet;      //= '61938166610';
    private    $cnfVersion;     //= '1.4';
    private    $cnfKeyIndex;    //= '1';
    private    $customer;
    private    $cart;
    private    $purchase;
    private    $lang;
    private    $currency;
    protected $privateKey;
// '-----BEGIN RSA PRIVATE KEY-----
// MIICXAIBAAKBgQCf0TdcTuphb7X+Zwekt1XKEWZDczSGecfo6vQfqvraf5VPzcnJ
// 2Mc5J72HBm0u98EJHan+nle2WOZMVGItTa/2k1FRWwbt7iQ5dzDh5PEeZASg2UWe
// hoR8L8MpNBqH6h7ZITwVTfRS4LsBvlEfT7Pzhm5YJKfM+CdzDM+L9WVEGwIDAQAB
// AoGAYfKxwUtEbq8ulVrD3nnWhF+hk1k6KejdUq0dLYN29w8WjbCMKb9IaokmqWiQ
// 5iZGErYxh7G4BDP8AW/+M9HXM4oqm5SEkaxhbTlgks+E1s9dTpdFQvL76TvodqSy
// l2E2BghVgLLgkdhRn9buaFzYta95JKfgyKGonNxsQA39PwECQQDKbG0Kp6KEkNgB
// srCq3Cx2od5OfiPDG8g3RYZKx/O9dMy5CM160DwusVJpuywbpRhcWr3gkz0QgRMd
// IRVwyxNbAkEAyh3sipmcgN7SD8xBG/MtBYPqWP1vxhSVYPfJzuPU3gS5MRJzQHBz
// sVCLhTBY7hHSoqiqlqWYasi81JzBEwEuQQJBAKw9qGcZjyMH8JU5TDSGllr3jybx
// FFMPj8TgJs346AB8ozqLL/ThvWPpxHttJbH8QAdNuyWdg6dIfVAa95h7Y+MCQEZg
// jRDl1Bz7eWGO2c0Fq9OTz3IVLWpnmGwfW+HyaxizxFhV+FOj1GUVir9hylV7V0DU
// QjIajyv/oeDWhFQ9wQECQCydhJ6NaNQOCZh+6QTrH3TC5MeBA1Yeipoe7+BhsLNr
// cFG8s9sTxRnltcZl1dXaBSemvpNvBizn0Kzi8G3ZAgc=
// -----END RSA PRIVATE KEY-----';

    protected $publicKey; 
// '-----BEGIN CERTIFICATE-----
// MIIBsTCCARoCCQCCPjNttGNQWDANBgkqhkiG9w0BAQsFADAdMQswCQYDVQQGEwJC
// RzEOMAwGA1UECgwFbXlQT1MwHhcNMTgxMDEyMDcwOTEzWhcNMjgxMDA5MDcwOTEz
// WjAdMQswCQYDVQQGEwJCRzEOMAwGA1UECgwFbXlQT1MwgZ8wDQYJKoZIhvcNAQEB
// BQADgY0AMIGJAoGBAML+VTmiY4yChoOTMZTXAIG/mk+xf/9mjwHxWzxtBJbNncNK
// 0OLI0VXYKW2GgVklGHHQjvew1hTFkEGjnCJ7f5CDnbgxevtyASDGst92a6xcAedE
// adP0nFXhUz+cYYIgIcgfDcX3ZWeNEF5kscqy52kpD2O7nFNCV+85vS4duJBNAgMB
// AAEwDQYJKoZIhvcNAQELBQADgYEACj0xb+tNYERJkL+p+zDcBsBK4RvknPlpk+YP
// ephunG2dBGOmg/WKgoD1PLWD2bEfGgJxYBIg9r1wLYpDC1txhxV+2OBQS86KULh0
// NEcr0qEY05mI4FlE+D/BpT/+WFyKkZug92rK0Flz71Xy/9mBXbQfm+YK6l9roRYd
// J4sHeQc=
// -----END CERTIFICATE-----';

    function __construct($lang, $currency) throws \Exception {
        $this->lang = $lang;
        $this->currency = $currency;
        $this->loadParams();
    }

    public function loadParams() throws \Exception{
        $payment = Payment::where('type','mypos')->get()->first();
        if(is_null($payment)){
            throw new \Exception(trans('payment.no_mypos_params'));
        }

        $data = json_decode($payment->data);
        if(!isset($data['environment']) || !isset($data['environment_url'])
            || !isset($data['shop_id']) || !isset($data['wallet'])
            || !isset($data['key_index']) || !isset($data['version'])
            || !isset($data['private_key']) || !isset($data['public_key'])){
            throw new \Exception(trans('payment.no_mypos_params'));
        }
    
        $this->environment = $data['environment'];
        $this->environmentUrl = $data['environment_url'];
        $this->cnfShopId = $data['shop_id'];
        $this->cnfWallet = $data['wallet'];
        $this->cnfVersion = $data['version'];
        $this->cnfKeyIndex = $data['key_index'];
        $this->privateKey = $data['private_key'];
        $this->publicKey = $data['public_key'];
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
    public function getCnf()
    {
        return $this->cnf;
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
    public function getCnfShopId(): string
    {
        return $this->cnfShopId;
    }
    /**
     * @param string $cnfWallet
     */
    public function setCnfWallet(string $cnfWallet): void
    {
        $this->cnfWallet = $cnfWallet;
    }
    /**
     * @return string
     */
    public function getCnfWallet(): string
    {
        return $this->cnfWallet;
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
    public function getCnfVersion(): string
    {
        return $this->cnfVersion;
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
    public function getCnfKeyIndex(): string
    {
        return $this->cnfKeyIndex;
    }
    /**
     * @param mixed $cart
     */
    public function setCart($cart): void
    {
        $this->cart = $cart;
    }
    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    // public function initMyPos()
    // {

    //     $cnf = new Config();
    //     $cnf->setIpcURL(array_key_exists($this->environment, $this->environmentUrl) ? $this->environmentUrl[$this->environment] : $this->environmentUrl['TEST']);
    //     $cnf->setLang('en');
    //     $cnf->setPrivateKey($this->privateKey);
    //     $cnf->setAPIPublicKey($this->publicKey);
    //     $cnf->setKeyIndex($this->getCnfKeyIndex());
    //     $cnf->setSid($this->getCnfShopId());
    //     $cnf->setVersion($this->getCnfVersion());
    //     $cnf->setWallet($this->getCnfWallet());

    //     $this->setCnf($cnf);

    //     $customer = new Customer();
    //     $customer->setFirstName('John');
    //     $customer->setLastName('Smith');
    //     $customer->setEmail('demo@demo.demo');
    //     $customer->setPhone('+359889629134');
    //     $customer->setCountry('BGR');
    //     $customer->setAddress('Business Park Varna');
    //     $customer->setCity('Varna');
    //     $customer->setZip('9000');

    //     $cart = new Cart;
    //     $cart->add('Some Book', 1, 9.99); //name, quantity, price
    //     $cart->add('Some other book', 1, 4.56);
    //     $cart->add('Discount', 1, -2.05);

    //     $orderId  = random_int(1, 87987878978978);
    //     $purchase = new Purchase($cnf);
    //     $purchase->setUrlCancel(route('my-pos.cancel', ['languageSlug' => 'bg']));
    //     $purchase->setUrlOk(route('my-pos.success', ['languageSlug' => 'bg']));
    //     $purchase->setUrlNotify(route('my-pos.notify', ['languageSlug' => 'bg']));
    //     $purchase->setOrderID($orderId); //Some unique ID
    //     $purchase->setCurrency('EUR');
    //     $purchase->setNote('Some note'); //Not required
    //     $purchase->setCustomer($customer);
    //     $purchase->setCart($cart);
    //     $purchase->getFormParameters();

    //     $purchase->setCardTokenRequest(Purchase::CARD_TOKEN_REQUEST_PAY_AND_STORE);
    //     $purchase->setPaymentParametersRequired(Purchase::PURCHASE_TYPE_FULL);
    //     $purchase->setPaymentMethod(Purchase::PAYMENT_METHOD_BOTH);

    //     try {
    //         $purchase->process();
    //     } catch (IPC_Exception $ex) {
    //         echo $ex->getMessage();
    //     }
    // }

    public function setConfig()
    {
        $cnf = new Config();
        $cnf->setIpcURL(array_key_exists($this->environment, $this->environmentUrl) ? $this->environmentUrl[$this->environment] : $this->environmentUrl['TEST']);
        $cnf->setLang('en');
        $cnf->setPrivateKey($this->privateKey);
        $cnf->setAPIPublicKey($this->publicKey);
        $cnf->setKeyIndex($this->getCnfKeyIndex());
        $cnf->setSid($this->getCnfShopId());
        $cnf->setVersion($this->getCnfVersion());
        $cnf->setWallet($this->getCnfWallet());

        $this->setCnf($cnf);
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
    public function getCustomer()
    {
        return $this->customer;
    }

    public function createCart($cartItemsArray, $discountArray): void
    {
        $cart = new Cart();
        foreach ($cartItemsArray as $cartItem) {
            $cart->add($cartItem['name'], $cartItem['quantity'], $cartItem['price']);
        }

        foreach ($discountArray as $cartDiscountItem) {
            $cart->add($cartDiscountItem['name'], $cartDiscountItem['quantity'], $cartDiscountItem['price']);
        }

        $this->setCart($cart);
    }

    public function makePurchase($order) throws IPC_Exception
    {
        $purchase = new Purchase($this->getCnf());
        $purchase->setUrlCancel(route('mypospayment.cancel', ['languageSlug' => $this->lang, 'orderId' => $order->id]));
        $purchase->setUrlOk(route('mypospayment.success', ['languageSlug' =>  $this->lang, 'orderId' => $order->id]));
        $purchase->setUrlNotify(route('mypospayment.notify', ['languageSlug' =>  $this->lang]));
        $purchase->setOrderID($order->id); //Some unique ID
        $purchase->setCurrency($this->currency);
        //        $purchase->setNote('Some note'); //Not required
        $purchase->setCustomer($this->getCustomer());
        $purchase->setCart($this->getCart());
        $purchase->setCardTokenRequest(Purchase::CARD_TOKEN_REQUEST_PAY_AND_STORE);
        $purchase->setPaymentParametersRequired(Purchase::PURCHASE_TYPE_FULL);
        $purchase->setPaymentMethod(Purchase::PAYMENT_METHOD_BOTH);
        $purchase->process();
    }
}
