<?php

Route::group([
    //   'prefix'     => 'paytm',
       'middleware' => ['web', 'theme', 'locale', 'currency']
   ], function () {
       Route::get('redirect','Wontonee\Paytm\Http\Controllers\PaytmController@redirect')->name('paytm.process');
       Route::post('paytmcheck','Wontonee\Paytm\Http\Controllers\PaytmController@checkstatus')->name('paytm.callback'); 
});