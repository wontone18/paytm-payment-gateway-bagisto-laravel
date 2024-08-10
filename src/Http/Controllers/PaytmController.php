<?php


namespace Wontonee\Paytm\Http\Controllers;

use Illuminate\Contracts\Session\Session;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;
use Webkul\Sales\Repositories\InvoiceRepository;
use Wontonee\Paytm\lib\PaytmChecksum;
use Webkul\Payment\Payment\Payment;

class PaytmController extends Controller
{

    /**
     * OrderRepository $orderRepository
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;

    /**
     * InvoiceRepository $invoiceRepository
     *
     * @var \Webkul\Sales\Repositories\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\OrderRepository  $orderRepository
     * @return void
     */
    public function __construct(OrderRepository $orderRepository,  InvoiceRepository $invoiceRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Redirects to the paytm server.
     *
     * @return \Illuminate\View\View
     */
    public function redirect()
    {
        $cart = Cart::getCart();

        $billingAddress = $cart->billing_address;

        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount =  ($cart->sub_total + $cart->tax_total + $shipping_rate) - $discount_amount; // total amount
        $paytmParams = array();
        $order_id = $cart->id . '_' . now()->format('YmdHis');
        session()->put("order-id", $order_id);


        $paytmParams["body"] = array(
            "requestType"   => "Payment",
            "mid"           => core()->getConfigData('sales.payment_methods.paytm.merchant_id'),
            "websiteName"   => core()->getConfigData('sales.payment_methods.paytm.website'),
            "orderId"       => $order_id,
            "callbackUrl"   => route('paytm.callback'),
            "txnAmount"     => array(
                "value"     => $total_amount,
                "currency"  => "INR",
            ),
            "userInfo"      => array(
                "custId"    => $billingAddress->id,
            ),
        );

        $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), core()->getConfigData('sales.payment_methods.paytm.merchant_key'));
        $paytmParams["head"] = array(
            "channelId" => 'WEB',
            "signature"    => $checksum
        );

        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

        if (core()->getConfigData('sales.payment_methods.paytm.website') == "WEBSTAGING") :
            $token_url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=" . core()->getConfigData('sales.payment_methods.paytm.merchant_id') . "&orderId=" . $order_id;
            $payment_url = "https://securegw-stage.paytm.in/theia/api/v1/showPaymentPage?mid=" . core()->getConfigData('sales.payment_methods.paytm.merchant_id') . "&orderId=" . $order_id;
        else :
            $token_url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=" . core()->getConfigData('sales.payment_methods.paytm.merchant_id') . "&orderId=" . $order_id;
            $payment_url = "https://securegw.paytm.in/theia/api/v1/showPaymentPage?mid=" . core()->getConfigData('sales.payment_methods.paytm.merchant_id') . "&orderId=" . $order_id;
        endif;
        // retreive token from gateway server
        $ch = curl_init($token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($ch);
        $response_data = json_decode($response, true);
        if (isset($response_data['body']['txnToken'])) {
            $txnToken = $response_data['body']['txnToken']; // Extract txnToken value
            // echo "Transaction Token: " . $txnToken;
            return view('paytm::paytm-redirect', [
                'payment_url' => $payment_url,
                'txnToken' => $txnToken,
                'orderId' => $order_id,
                'mid' => core()->getConfigData('sales.payment_methods.paytm.merchant_id')
            ]);
        }
    }
    /**
     * order status check
     */

    public function checkstatus()
    {

        /* initialize an array */
        $paytmParams = array();

        /* body parameters */
        $paytmParams["body"] = array(
            "mid" => core()->getConfigData('sales.payment_methods.paytm.merchant_id'),
            "orderId" =>  session()->get('order-id')
        );


        $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), core()->getConfigData('sales.payment_methods.paytm.merchant_key'));

        /* head parameters */
        $paytmParams["head"] = array(
            "signature"    => $checksum
        );

        /* prepare JSON string for request */
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);


        if (core()->getConfigData('sales.payment_methods.paytm.website') == "WEBSTAGING") :
            $url = "https://securegw-stage.paytm.in/v3/order/status";
        else :
            $url = "https://securegw.paytm.in/v3/order/status";
        endif;


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = curl_exec($ch);
        $response_data = json_decode($response, true);

        if (isset($response_data['body']['resultInfo']['resultStatus']) && $response_data['body']['resultInfo']['resultStatus'] === 'TXN_SUCCESS') {
            // Transaction is successful
            session()->forget(session()->get('order-id')); // remove the order id from the session
            $cart = Cart::getCart();
            $data = (new OrderResource($cart))->jsonSerialize(); // new class v2.2
            $order = $this->orderRepository->create($data);
            $this->orderRepository->update(['status' => 'processing'], $order->id);
            if ($order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData($order));
            }
            Cart::deActivateCart();
            session()->flash('order_id', $order->id);
            // Order and prepare invoice
            return redirect()->route('shop.checkout.onepage.success');
        } else {
            // Transaction failed or resultStatus is not TXN_SUCCESS
            session()->flash('error', 'Paytm payment either cancelled or transaction failure.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Prepares order's invoice data for creation.
     *
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ["order_id" => $order->id,];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
