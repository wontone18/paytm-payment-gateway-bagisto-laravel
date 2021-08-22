<?php

return [
    [
        'key'    => 'sales.paymentmethods.paytm',
        'name'   => 'Paytm',
        'sort'   => 4,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'admin::app.admin.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'admin::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ],
			[
                'name'          => 'merchant_id',
                'title'         => 'admin::app.admin.system.merchant-id',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],	
			[
                'name'          => 'merchant_key',
                'title'         => 'admin::app.admin.system.merchant-key',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
			[
                'name'    => 'website',
                'title'   => 'admin::app.admin.system.websitestatus',
                'type'    => 'select',
                'options' => [
                    [
                        'title' => 'Staging',
                        'value' => 'WEBSTAGING',
                    ], [
                        'title' => 'Live',
                        'value' => 'DEFAULT',
                    ],
                ],
            ],
			[
                'name'          => 'active',
                'title'         => 'admin::app.admin.system.paytmstatus',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true
            ],
				
        ]
    ]
];