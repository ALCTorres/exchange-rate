<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Exchange Rate Tools</title>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/angular/angular.js') }}" defer></script>
        <script src="{{ asset('js/angular/angularjs-currency-input-mask.js') }}" defer></script>
        <script src="{{ asset('js/controller/app_controller.js') }}" defer></script>
        <script src="{{ asset('js/controller/app_controller.js') }}" defer></script>
        
        <!-- Favicon -->
        <link rel="shortcut icon" href="favicon.ico"/>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body ng-app="appExchangeRate" ng-cloak>
        <div>
            <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
                <div class="container">
                    <a class="navbar-brand text-uppercase" href="{{ url('/') }}">
                        <img src="images/pngegg.png" width="30" height="30" class="d-inline-block align-top" alt="Exchange Rate Tools">
                        Exchange Rate Tools
                    </a>
                </div>
            </nav>

            <main class="py-4" ng-controller="exchangeRateCtrl">
                @yield('content')
            </main>
        </div>
    </body>
</html>