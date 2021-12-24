<?php

namespace Binance;

use Exception;

// PHP version check
if (version_compare(phpversion(), '7.0', '<=')) {
    fwrite(STDERR, "Hi, PHP " . phpversion() . " support will be removed very soon as part of continued development.\n");
    fwrite(STDERR, "Please consider upgrading.\n");
}

/**
 * Main Binance class
 *
 * Eg. Usage:
 * require 'vendor/autoload.php';
 * $api = new Binance\\API();
 */
class API
{
    protected $base = 'https://api.binance.com/api/'; // /< REST endpoint for the currency exchange
    protected $baseTestnet = 'https://testnet.binance.vision/api/'; // /< Testnet REST endpoint for the currency exchange
    protected $wapi = 'https://api.binance.com/wapi/'; // /< REST endpoint for the withdrawals
    protected $sapi = 'https://api.binance.com/sapi/'; // /< REST endpoint for the supporting network API
    protected $fapi = 'https://fapi.binance.com/'; // /< REST endpoint for the futures API
    protected $bapi = 'https://www.binance.com/bapi/'; // /< REST endpoint for the internal Binance API
    protected $stream = 'wss://stream.binance.com:9443/ws/'; // /< Endpoint for establishing websocket connections
    protected $streamTestnet = 'wss://testnet.binance.vision/ws/'; // /< Testnet endpoint for establishing websocket connections
    protected $api_key; // /< API key that you created in the binance website member area
    protected $api_secret; // /< API secret that was given to you when you created the api key
    protected $useTestnet = false; // /< Enable/disable testnet (https://testnet.binance.vision/)
    protected $depthCache = []; // /< Websockets depth cache
    protected $depthQueue = []; // /< Websockets depth queue
    protected $chartQueue = []; // /< Websockets chart queue
    protected $charts = []; // /< Websockets chart data
    protected $curlOpts = []; // /< User defined curl coptions
    protected $info = [
        "timeOffset" => 0,
    ]; // /< Additional connection options
    protected $proxyConf = null; // /< Used for story the proxy configuration
    protected $caOverride = false; // /< set this if you donnot wish to use CA bundle auto download feature
    protected $transfered = 0; // /< This stores the amount of bytes transfered
    protected $requestCount = 0; // /< This stores the amount of API requests
    protected $httpDebug = false; // /< If you enable this, curl will output debugging information
    protected $subscriptions = []; // /< View all websocket subscriptions
    protected $btc_value = 0.00; // /< value of available assets
    protected $btc_total = 0.00;

    // /< value of available onOrder assets
    
    protected $exchangeInfo = null;
    protected $lastRequest = [];

    protected $xMbxUsedWeight = 0;
    protected $xMbxUsedWeight1m = 0;

    /**
     * Constructor for the class,
     * send as many argument as you want.
     *
     * No arguments - use file setup
     * 1 argument - file to load config from
     * 2 arguments - api key and api secret
     * 3 arguments - api key, api secret and use testnet flag
     *
     * @return null
     */
    public function __construct()
    {
        $param = func_get_args();
        switch (count($param)) {
            case 0:
                $this->setupApiConfigFromFile();
                $this->setupProxyConfigFromFile();
                $this->setupCurlOptsFromFile();
                break;
            case 1:
                $this->setupApiConfigFromFile($param[0]);
                $this->setupProxyConfigFromFile($param[0]);
                $this->setupCurlOptsFromFile($param[0]);
                break;
            case 2:
                $this->api_key = $param[0];
                $this->api_secret = $param[1];
                break;
            case 3:
                $this->api_key = $param[0];
                $this->api_secret = $param[1];
                $this->useTestnet = (bool)$param[2];
                break;
            default:
                echo 'Please see valid constructors here: https://github.com/jaggedsoft/php-binance-api/blob/master/examples/constructor.php';
        }
    }
/**

     * @deprecated
     *
     * Fetch current(daily) trade fee of symbol, values in percentage.
     * for more info visit binance official api document
     *
     * $symbol = "BNBBTC"; or any other symbol or even a set of symbols in an array
     * @param string $symbol
     * @return mixed
     */
    public function tradeFee(string $symbol)
    {
        $params = [
            "symbol" => $symbol,
            "wapi" => true,
        ];
        trigger_error('Function tradeFee is deprecated and will be removed from Binance on Aug 1, 2021. Please use $api->commissionFee', E_USER_DEPRECATED);
        
        return $this->httpRequest("v3/tradeFee.html", 'GET', $params, true);
    }
    



    
    /**
     * commissionFee - Fetch commission trade fee
     * 
     * @link https://binance-docs.github.io/apidocs/spot/en/#trade-fee-user_data
     * 
     * @property int $weight 1
     * 
     * @param string $symbol  (optional)  Should be a symbol, e.g. BNBUSDT or empty to get the full list
     * 
     * @return array containing the response
     * @throws \Exception
     */    
    public function commissionFee($symbol = '')
    {
        $params = array('sapi' => true);
        if ($symbol != '' && gettype($symbol) == 'string')
            $params['symbol'] = $symbol;

        return $this->httpRequest("v1/asset/tradeFee", 'GET', $params, true);
    }



     /**
     * withdrawFee - Get the withdrawal fee for an asset
     * 
     * @property int $weight 1
     * 
     * @param string $asset    (mandatory)  An asset, e.g. BTC
     * 
     * @return array containing the response
     * @throws \Exception
     */
    public function withdrawFee(string $asset)
    {
        $return = $this->assetDetail();

        if (isset($return['success'], $return['assetDetail'], $return['assetDetail'][$asset]) && $return['success']) {
            return $return['assetDetail'][$asset];
        } else {
            return array();
        }
    }

}
    ?>