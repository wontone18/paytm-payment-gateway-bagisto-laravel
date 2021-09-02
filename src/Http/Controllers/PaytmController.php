<?php


namespace Wontonee\Paytm\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\Config;

class PaytmController extends Controller
{

    /**
     * OrderRepository $orderRepository
     *
     * @var \Webkul\Sales\Repositories\OrderRepository
     */
    protected $orderRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Attribute\Repositories\OrderRepository  $orderRepository
     * @return void
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
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

        include __DIR__ . '/../../lib/encdec_paytm.php';

        $shipping_rate = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->price : 0; // shipping rate
        $discount_amount = $cart->discount_amount; // discount amount
        $total_amount =  ($cart->sub_total + $cart->tax_total + $shipping_rate) - $discount_amount; // total amount

        $paytmParams = array(
            "MID" => core()->getConfigData('sales.paymentmethods.paytm.merchant_id'),
            "WEBSITE" => core()->getConfigData('sales.paymentmethods.paytm.website'),
            "INDUSTRY_TYPE_ID" => "Retail",
            "CHANNEL_ID" => "WEB",
            "ORDER_ID" => $cart->id,
            "CUST_ID" => $billingAddress->id,
            "MOBILE_NO" => $billingAddress->phone,
            "EMAIL" => $billingAddress->email,
            "TXN_AMOUNT" => $total_amount,
            "CALLBACK_URL" => route('paytm.callback'),
        );

        $checksum = getChecksumFromArray($paytmParams, core()->getConfigData('sales.paymentmethods.paytm.merchant_key'));

        if (core()->getConfigData('sales.paymentmethods.paytm.website') == "WEBSTAGING") :
            $url = "https://securegw-stage.paytm.in/order/process"; // test mode
        else :
            $url = "https://securegw.paytm.in/order/process"; // Live mode
        endif;

        return view('paytm::paytm-redirect')->with(compact('checksum', 'paytmParams', 'url'));
    }

    /***
     * 
     * Call back url
     */
    public function checkstatus()
    {

        include __DIR__ . '/../../lib/encdec_paytm.php';
        $cart = Cart::getCart();

        $paytmParams = array();
        $paytmParams["MID"] = core()->getConfigData('sales.paymentmethods.paytm.merchant_id');
        $paytmParams["ORDERID"] = $cart->id;
        $checksum = getChecksumFromArray($paytmParams, core()->getConfigData('sales.paymentmethods.paytm.merchant_key'));
        $paytmParams["CHECKSUMHASH"] = $checksum;
        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

        if (core()->getConfigData('sales.paymentmethods.paytm.website') == "WEBSTAGING") :
            $url = "https://securegw-stage.paytm.in/order/status"; // Test mode
        else :
            $url = "https://securegw.paytm.in/order/status"; // Live mode
        endif;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $response = json_decode(curl_exec($ch), true);

        if ($response['STATUS'] == "TXN_SUCCESS") {
            $order = $this->orderRepository->create(Cart::prepareDataForOrder());
            Cart::deActivateCart();
            session()->flash('order', $order);
            return redirect()->route('shop.checkout.success');
        } else {
            session()->flash('error', 'Paytm payment either cancelled or transaction failure.');

            return redirect()->route('shop.checkout.cart.index');
        }
    }
}
