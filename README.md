## Laravel Mpesa STK Push

The web app performs an mpesa stk push to the phone number provided together with the amount given

### Usage

-   Copy the `.env.example` to `.env` and edit database credentials together with your Mpesa credentials
-   Run `composer install`
-   Run `npm run dev`
-   Run `php artisan key:generate`
-   Run `php artisan migrate`

**NOTE:** MPESA_CALLBACK_URL in local development should be served with ngrok or other https tunneling softwares
e.g MPESA_CALLBACK_URL=https://-----.ngrok.io

### License

open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
