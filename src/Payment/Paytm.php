<?php

namespace Wontonee\Paytm\Payment;

use Webkul\Payment\Payment\Payment;

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
}
