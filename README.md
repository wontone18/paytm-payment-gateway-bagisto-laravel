# Bagisto Paytm Payment Gateway
Paytm is a popular payment gateway in india. This package provides a additional strong help for the user to use the paytm payment gateway in their Bagisto laravel ecommerce application.

## Manual Installation
1. Download the zip folder from the github repository.
2. Unzip the folder and go to your bagisto application path `package` and create a new folder name `Wontonee` then upload paytm folder inside the wontonee folder.
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
5. Now open the command prompt and run `composer dump-autoload`.
6. Now run `php artisan config:cache`
7. Now go to your bagisto admin section `admin/configuration/sales/paymentmethods` you will see the new payment gateway paytm. 

For any help or customisation  <https://www.wontonee.com> or email us <hello@wontonee.com>

