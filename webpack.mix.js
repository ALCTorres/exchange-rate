const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.copy('node_modules/angular/angular.min.js', 'public/js/angular/angular.js');
mix.copy('node_modules/angularjs-currency-input-mask/dist/angularjs-currency-input-mask.min.js', 'public/js/angular/angularjs-currency-input-mask.js');
mix.copy('resources/js/angular-locale_pt-br.js', 'public/js/angular/angular-locale_pt-br.js');
mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sourceMaps();
