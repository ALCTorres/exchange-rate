<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ExchangeRateHistorical;

use Throwable;

class ExchangeRateController extends Controller
{
    /**
     * Available exchange rates
     *
     * @var (array)
     * @access private
     */
    private $availableExchangeRates = [ 'BRL', 'USD', 'EUR' ];

    /**
     * Available queries in request
     *
     * @var (array)
     * @access private
     */
    private $availableRequestQuery = [ 'from', 'to', 'amount' ];

    /**
     * Url access to external API
     *
     * @var (array)
     * @access private
     */
    private $apiUrl = [ 'all' => 'api.exchangerate.host/latest?places=2&', 'to' => "api.exchangerate.host/convert?places=2&" ];

    /**
     * Show exchange rate tools index
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view( 'exchange_rate_conversion.index' );
    }

    /**
     * Return object to format monetary values according currency code
     *
     * @param (string) currency - Currency code
     *
     * @return (object)
     */
    private function getMonetaryFormat( $currency )
    {
        // Format the currency according to the base code
        switch( $currency )
        {
            case "BRL":
                $fmt = numfmt_create( 'pt_BR', \NumberFormatter::CURRENCY );
                break;
            case "EUR":
                $fmt = numfmt_create( 'nl_NL', \NumberFormatter::CURRENCY );
                break;
            default:
                $fmt = numfmt_create( 'en_US', \NumberFormatter::CURRENCY );
                break;
        }

        return $fmt;
    }

    /**
     * Get historical rates if error or off line external API
     *
     * @param (string) from - Currency code base
     * @param (string) to - Specific currency code to verify
     * @param (string) amount - Amount consulted
     * @param (object) fmt - Monetary object formatter
     * @param (array) diff (optional) - Currency codes to verify if have all selected
     *
     * @return (array)
     */
    public function find( $from, $to, $amount, \NumberFormatter $fmt, $diff = null )
    {
        $response = [];
        try
        {
            if ( $to === 'all' && !empty( $diff ) )
            {
                foreach( $diff as $key => $currencyCode )
                {
                    $historical = ExchangeRateHistorical::where( 'code', "$from-$currencyCode" )->first();
                    if ( !empty( $historical ) )
                    {
                        // Get amount according historical rate
                        $value = ( $historical['rate'] * $amount );
                        $response[] = [ 'code' => "$from-$currencyCode", 'historical' => date( "d/m/Y", strtotime( $historical['historical'] ) ), 'amount' => numfmt_format_currency( $fmt, $value, $currencyCode ), 'rate' => $historical['rate'] ];
                    }
                }
            }
            else
            {
                $historical = ExchangeRateHistorical::where( 'code', "$from-$to" )->first();
                if ( !empty( $historical ) )
                {
                    // Get amount according historical rate
                    $value = ( $historical['rate'] * $amount );
                    $response[] = [ 'code' => "$from-$to", 'historical' => date( "d/m/Y", strtotime( $historical['historical'] ) ), 'amount' => numfmt_format_currency( $fmt, $value, $to ), 'rate' => $historical['rate'] ];
                }
            }
        }
        catch ( Throwable $e )
        {
            report( $e );
            /*
             * TO DO
             *
             * In case of exception save logs in database if you wish
             */
        }

        return $response;
    }

    /**
     * Save historical requests to use in case external API off line
     *
     * @param (object) JsonResponse json - Result of API request
     * @param (string) from - Currency code base
     * @param (string) to - Specific currency code to verify
     * @param (string) amount - Amount consulted
     * @param (object) fmt - Monetary object formatter
     * @param (array) diff (optional) - Currency codes to verify if have all selected
     *
     * @return (array)
     */
    public function store( JsonResponse $json, $from, $to, $amount, \NumberFormatter $fmt, $diff = null )
    {
        $data = json_decode( $json->content(), true );
        $response = [];
        try
        {
            if ( $to === 'all' && !empty( $data['rates'] ) && !empty( $diff ) )
            {
                foreach( $diff as $key => $currencyCode )
                {
                    $historical = ExchangeRateHistorical::where( 'code', "$from-$currencyCode" )->first();
                    $rate = sprintf( "%.2f", ( $data['rates'][$currencyCode] / $amount ) );
                    $value = $amount * $rate;
                    if ( !empty( $historical ) )
                    {
                        ExchangeRateHistorical::where( 'code', "$from-$currencyCode" )->update( [ 'rate' => $rate, 'historical' => $data['date'] ] );
                    }
                    else
                    {
                        ExchangeRateHistorical::create( [ 'code' => "$from-$currencyCode", 'rate' => $rate, 'historical' => $data['date'] ] );
                    }
                    $response[] = [ 'code' => "$from-$currencyCode", 'historical' => date( "d/m/Y", strtotime( $data['date'] ) ), 'amount' => numfmt_format_currency( $fmt, $value, $currencyCode ), 'rate' => $rate ];
                }
            }
            else if ( !empty( $data['result'] ) )
            {
                $historical = ExchangeRateHistorical::where( 'code', "$from-$to" )->first();
                if ( !empty( $historical ) )
                {
                    ExchangeRateHistorical::where( 'code', "$from-$to" )->update( [ 'rate' => $data['info']['rate'], 'historical' => $data['date'] ] );
                }
                else
                {
                    ExchangeRateHistorical::create( [ 'code' => "$from-$to", 'rate' => $data['info']['rate'], 'historical' => $data['date'] ] );
                }
                $rate = sprintf( "%.2f", $data['info']['rate'] );
                $value = $amount * $data['info']['rate'];
                $response[] = [ 'code' => "$from-$to", 'historical' => date( "d/m/Y", strtotime( $data['date'] ) ), 'amount' => numfmt_format_currency( $fmt, $value, $to ), 'rate' => $rate ];
            }
        }
        catch ( Throwable $e )
        {
            report( $e );
            /*
             * TO DO
             *
             * In case of exception save logs in database if you wish
             */
        }

        return $response;
    }

    /**
     * Get exchange rate values
     *
     * @param (object) Request request - Object request
     *
     * @return (string)
     */
    public function exchangeRateValues( Request $request )
    {
        $response = response()->json( [ 'rates' => [], 'success' => false ] );
        if ( $request->has( $this->availableRequestQuery ) )
        {
            $to = $request->input( 'to' );
            $from = $request->input( 'from' );
            $amount = ( !empty( $request->input( 'amount' ) ) ) ? $request->input( 'amount' ) : 1;
            $fmt = $this->getMonetaryFormat( $from );
            $diff = null;

            // Verify request have more than one currency code to define URL and data
            switch( $to )
            {
                case 'all':
                    $apiUrlKey = 'all';
                    // Remove base currency code from symbols
                    $diff = array_diff( $this->availableExchangeRates, [ $from ] );
                    $buildQuery = [ 'base' => $from, 'symbols' => implode( ',', $diff ), 'amount' => $amount ];
                    break;
                default:
                    $apiUrlKey = 'to';
                    $buildQuery = $request->all( $this->availableRequestQuery );
                    break;
            }

            // Send request to external API
            try
            {
                $getContent = file_get_contents( $this->apiUrl[$apiUrlKey] . http_build_query( $buildQuery ) );
            }
            catch ( Throwable $e )
            {
                report( $e );
                $getContent = false;
                /*
                 * TO DO
                 *
                 * In case of exception save logs in database if you wish
                 */
            }
            
            $result = [];
            if ( $getContent !== false )
            {
                $decode = json_decode( $getContent, true );
                if ( !empty( $decode['success'] ) && $decode['success'] === true )
                {
                    $result = $this->store( response()->json( $decode ), $from, $to, $amount, $fmt, $diff );
                    // If database is not available return external API result
                    if ( empty( $result ) )
                    {
                        if ( $to === 'all' && !empty( $decode['rates'] ) && !empty( $diff ) )
                        {
                            foreach( $diff as $key => $currencyCode )
                            {
                                $rate = sprintf( "%.2f", ( $decode['rates'][$currencyCode] / $amount ) );
                                $value = $amount * $rate;
                                $result[] = [ 'code' => "$from-$currencyCode", 'historical' => date( "d/m/Y", strtotime( $decode['date'] ) ), 'amount' => numfmt_format_currency( $fmt, $value, $currencyCode ), 'rate' => $rate ];
                            }
                        }
                        else if ( !empty( $decode['result'] ) )
                        {
                            $rate = sprintf( "%.2f", $decode['info']['rate'] );
                            $value = $amount * $rate;
                            $result[] = [ 'code' => "$from-$to", 'historical' => date( "d/m/Y", strtotime( $decode['date'] ) ), 'amount' => numfmt_format_currency( $fmt, $value, $to ), 'rate' => $rate ];
                        }
                    }
                }
            }

            // If external API is not available try return historical data
            if ( empty( $result ) )
            {
                $result = $this->find( $from, $to, $amount, $fmt, $diff );
            }

            // Return response in json
            if ( !empty( $result ) )
            {
                $response = response()->json( [ 'rates' => $result, 'success' => true ] );
            }
        }

        return $response;
    }
}