var loadingImg = 'images/loading.gif';
var appExchangeRate = angular.module( 'appExchangeRate', ['cur.$mask'] );
appExchangeRate.controller( 'exchangeRateCtrl', ['$scope', '$http', function( $scope, $http )
{
    $scope.Message = '';
    $scope.Color = '';
    $scope.displayLoading = false;
    $scope.ExchangeRates = [];
    $scope.Currency = {
        from: 'BRL',
        to: 'all',
        amount: ''
    };

    $scope.Currencies = [
        {name: "All", value: 'all'},
        {name: "BRL", value: "BRL"},
        {name: "USD", value: "USD"},
        {name: "EUR", value: "EUR"}
    ];

    // Disable equivalent currency code in from
    $scope.disableCurrencyCode = function()
    {
        $( '#currency-to' ).children( 'option' ).each( function()
        {
            $( this ).prop( "disabled", false );
            if ( $( this ).val() === $scope.Currency.from )
            {
                $( this ).prop( "disabled", true );
            }
        });
    };

    // Get exchange rates
    $scope.getExchangeRate = function()
    {
        $scope.Message = '';
        $scope.displayLoading = true;
        $scope.ExchangeRates = [];
        $http( {
            url: '/api/exchange-rates/?from=' + $scope.Currency.from + '&to=' + $scope.Currency.to + '&amount=' + $scope.Currency.amount,
            method: 'GET'
        } ).then( function success( result )
        {
            $scope.displayLoading = false;
            if ( typeof result.data.success !== 'undefined' && result.data.success === true )
            {
                if ( angular.isArray( result.data.rates ) && result.data.rates.length > 0 )
                {
                    $scope.ExchangeRates = result.data.rates;
                }
            }
            else
            {
                $scope.Message = 'Sorry, an error occurred during your request. Please, try again later. If this error persists contact our technical support.';
                $scope.Color = 'danger';
            }
        }, function error( error )
        {
            $scope.displayLoading = false;
            $scope.Message = 'Sorry, an error occurred during your request. Please, try again later. If this error persists contact our technical support.';
            $scope.Color = 'danger';
        });
    };
}]);