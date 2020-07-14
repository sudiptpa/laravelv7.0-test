<?php

namespace App\Http\Controllers;

use App\Order;
use App\Processor\Esewa;
use Exception;
use Illuminate\Http\Request;

class EsewaController extends Controller
{
    /**
     * @param Request $request
     */
    public function checkout(Request $request)
    {
        $order = Order::findOrFail(mt_rand(1, 100));

        return view('esewa.checkout', compact('order'));
    }

    /**
     * @param $order_id
     * @param Request $request
     */
    public function payment($order_id, Request $request)
    {
        $order = Order::findOrFail($order_id);

        $gateway = with(new Esewa);

        try {
            $response = $gateway->purchase([
                'deliveryCharge' => 0,
                'serviceCharge' => 0,
                'taxAmount' => 0,
                'amount' => $gateway->formatAmount($order->amount),
                'totalAmount' => $gateway->formatAmount($order->amount),
                'productCode' => vsprintf('TEST20%s', [$order->id]),
                'failedUrl' => $gateway->getFailedUrl($order),
                'returnUrl' => $gateway->getReturnUrl($order),
            ]);

        } catch (Exception $e) {
            $order->update(['payment_status' => Order::PAYMENT_PENDING]);

            return redirect()
                ->route('checkout.payment.esewa.failed', [$order->id])
                ->with('message', sprintf("Your payment failed with error: %s", $e->getMessage()));
        }

        if ($response->isRedirect()) {
            $response->redirect();
        }

        return redirect()->back()->with([
            'message' => "We're unable to process your payment at the moment, please try again.",
        ]);
    }

    /**
     * @param $order_id
     * @param Request $request
     */
    public function completed($order_id, Request $request)
    {
        $order = Order::findOrFail($order_id);

        $gateway = with(new Esewa);

        $response = $gateway->verifyPayment([
            'amount' => $gateway->formatAmount($order->amount),
            'referenceNumber' => $request->get('refId'),
            'productCode' => $request->get('oid'),
        ]);

        if ($response->isSuccessful()) {
            $order->update([
                'transaction_id' => $request->get('refId'),
                'payment_status' => Order::PAYMENT_COMPLETED,
            ]);

            return redirect()->route('checkout.payment.esewa')->with([
                'message' => 'Thank you for your shopping, Your recent payment was successful.',
            ]);
        }

        return redirect()->route('checkout.payment.esewa')->with([
            'message' => 'Thank you for your shopping, However, the payment has been declined.',
        ]);
    }

    /**
     * @param $order_id
     * @param Request $request
     */
    public function failed($order_id, Request $request)
    {
        $order = Order::findOrFail($order_id);

        return view('esewa.checkout', compact('order'));
    }
}
