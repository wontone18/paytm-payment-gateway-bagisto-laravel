<?php

namespace Wontonee\Paytm\Payment;

use Webkul\Payment\Payment\Payment;
use Illuminate\Support\Facades\Storage;

class Paytm extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code  = 'paytm';

    public function getRedirectUrl()
    {
        return route('paytm.process');
    }

    /**
     * Get payment method image.
     *
     * @return array
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : '';
    }
}
