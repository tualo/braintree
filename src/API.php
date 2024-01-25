<?php
namespace Tualo\Office\Braintree;

use Tualo\Office\Basic\TualoApplication as App;
use Braintree\Gateway;


class API {

    private static $ENV = null;
    private static $gateway = null;
    
    public static function init():void{
        self::getEnvironment();

        self::$gateway = new Braintree\Gateway([
            'environment' => self::$ENV['BT_ENVIRONMENT'],
            'merchantId' => self::$ENV['BT_MERCHANT_ID'],
            'publicKey' => self::$ENV['BT_PUBLIC_KEY'],
            'privateKey' => self::$ENV['BT_PRIVATE_KEY']
        ]);
    }

    public static function getEnvironment(): array
    {
        if (is_null(self::$ENV)) {
            $db = App::get('session')->getDB();
            try {
                if (!is_null($db)) {
                    $data = $db->direct('select id,val from braintree_environments');
                    foreach ($data as $d) {
                        self::$ENV[$d['id']] = $d['val'];
                    }
                }
            } catch (\Exception $e) {
            }
        }
        return self::$ENV;
    }

    public static function getGateway(): Gateway
    {
        if (is_null(self::$gateway)) {
            self::init();
        }
        return self::$gateway;
    }

    public static function getClientToken(): string
    {
        $gateway = self::getGateway();
        return $gateway->clientToken()->generate();
    }

    
    
    public static function addShorthandCheckout(
        string $success_url,
        string $cancel_url,
        string $product_name,
        string $product_description,
        float $amount,
        int $quantity,
        int $expires_in=3600
    ):array{
        $db = App::get('session')->getDB();

        $amount = $_POST["amount"];
        $nonce = $_POST["payment_method_nonce"];

        $result = self::$gateway->transaction()->sale([
            'amount' => $amount,
            'paymentMethodNonce' => $nonce,
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        if ($result->success || !is_null($result->transaction)) {
            $transaction = $result->transaction;
            return  [
                'url'=>$transaction->url,
                'id'=>$transaction->id
            ];
        }
        
    }
}