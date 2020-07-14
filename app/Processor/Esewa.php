<?php
namespace App\Processor;

use Exception;
use Omnipay\Omnipay;

class Esewa
{
    /**
     * @return \SecureGateway
     */
    public function gateway()
    {
        $gateway = Omnipay::create('Esewa_Secure');

        $gateway->setMerchantCode(config('services.esewa.merchant'));
        $gateway->setTestMode(config('services.esewa.sandbox'));

        return $gateway;
    }

    /**
     * @param array $parameters
     * @return $response
     */
    public function purchase(array $parameters)
    {
        try {
            $response = $this->gateway()
                ->purchase($parameters)
                ->send();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $response;
    }

    /**
     * @param array $parameters
     * @return $response
     */
    public function verifyPayment(array $parameters)
    {
        try {
            $response = $this->gateway()
                ->verifyPayment($parameters)
                ->send();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        return $response;
    }

    /**
     * @param $amount
     */
    public function formatAmount($amount)
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * @param $order
     */
    public function getFailedUrl($order)
    {
        return route('checkout.payment.esewa.failed', $order->id);
    }

    /**
     * @param $order
     */
    public function getReturnUrl($order)
    {
        return route('checkout.payment.esewa.completed', $order->id);
    }
}
