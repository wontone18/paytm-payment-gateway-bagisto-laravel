# Bagisto Paytm Payment Gateway
Paytm is a popular payment gateway in india. This package provides a additional strong help for the user to use the paytm payment gateway in their Bagisto laravel ecommerce application.

## Automatic Installation
1. Use command prompt to run this package `composer require wontonee/paytm`
2. Now open `config/app.php` and register paytm provider.
```sh
'providers' => [
        // Paytm provider
        Wontonee\Paytm\Providers\PaytmServiceProvider::class,
]
```
3. Now open composer.json and go to `autload psr-4`.
```sh
"autoload": {
        "psr-4": {
        "Wontonee\\Paytm\\": "packages/Wontonee/Paytm/src"
        }
    }
```
4. Now go to `package/Webkul/Admin/src/Resources/lang/en` copy these line at the bottom end of code.
```sh
'merchant-id'                      => 'Merchant Id',
'merchant-key'                      => 'Merchant Key',
'websitestatus'                      => 'Website',
'industrytype'                      => 'Industry Type',
'paytmstatus'                      => 'Status',
'callback-url'                      => 'Call Back URL'
```
5. Now open the command prompt and run `composer dump-autoload`.
6. Now run `php artisan config:cache`
7. Now go to your bagisto admin section `admin/configuration/sales/paymentmethods` you will see the new payment gateway paytm. 
8. Now open `app\Http\Middleware\VerifyCsrfToken.php` and add this route to the exception list.
```sh
protected $except = [
                 '/paytmcheck'
           ];

```

## Manual Installation
1. Download the zip folder from the github repository.
2. Unzip the folder and go to your bagisto application path `package` and create a folder name `Wontonee/Paytm/` upload `src` folder inside this path.
3. Now open `config/app.php` and register paytm provider.
```sh
'providers' => [
        // Paytm provider
        Wontonee\Paytm\Providers\PaytmServiceProvider::class,
]
```
4. Now open composer.json and go to `autload psr-4`.
```sh
"autoload": {
        "psr-4": {
        "Wontonee\\Paytm\\": "packages/Wontonee/Paytm/src"
        }
    }
```
5. Now go to `package/Webkul/Admin/src/Resources/lang/en` copy these line at the bottom end of code.
```sh
'merchant-id'                      => 'Merchant Id',
'merchant-key'                      => 'Merchant Key',
'websitestatus'                      => 'Website',
'industrytype'                      => 'Industry Type',
'paytmstatus'                      => 'Status',
'callback-url'                      => 'Call Back URL'
```
6. Now open the command prompt and run `composer dump-autoload`.
7. Now run `php artisan config:cache`
9. Now go to your bagisto admin section `admin/configuration/sales/paymentmethods` you will see the new payment gateway paytm. 
9. Now open `app\Http\Middleware\VerifyCsrfToken.php` and add this route to the exception list.
```sh
protected $except = [
                 '/paytmcheck'
           ];

```

For any help or customisation  <https://www.wontonee.com> or email us <hello@wontonee.com>
