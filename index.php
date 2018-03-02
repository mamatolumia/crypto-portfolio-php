<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>
    <script type="text/javascript" src="script.js"></script>

</head>
<body>



<?php

$coinpaymentsZECBalance = 0;
$coinpaymentsTotalUSD = 0;

$miningpoolhubUSD = Array ();
$miningpoolhubUSDUnconfirmed = Array ();


$priceInUSD = Array ();
$priceInUSDOther = 0;

$coinInUSDInMiningpoolhub = Array ();

$coinInUSDInMiningpoolhub['DGB'] = 0;
$coinInUSDInMiningpoolhub['ETN'] = 0;
$coinInUSDInMiningpoolhub['VTC'] = 0;
$coinInUSDInMiningpoolhub['ZEC'] = 0;

$coinInUSDInMiningpoolhubUnconfirmed = Array ();

$coinInUSDInMiningpoolhubUnconfirmed['DGB'] = 0;
$coinInUSDInMiningpoolhubUnconfirmed['ETN'] = 0;
$coinInUSDInMiningpoolhubUnconfirmed['VTC'] = 0;
$coinInUSDInMiningpoolhubUnconfirmed['ZEC'] = 0;

$totalInUSDInMiningpoolhub = 0;
$totalInUSDInMiningpoolhubUnconfirmed = 0;
$totalInUSDInMiningpoolhubPlusUnconfirmed = 0;


$poloniexUSDTotal = 0;

$totalInUSD = 0;



function coinpayments_api_call($cmd, $req = array()) {
    // Fill these in from your API Keys page
    // erase file_get_contents and replace with api key in quotes
    $public_key = file_get_contents('./api keys/coinpaymentspublicapikey.txt');
    $private_key = file_get_contents('./api keys/coinpaymentsprivateapikey.txt');

    // Set the API command and required fields
    $req['version'] = 1;
    $req['cmd'] = $cmd;
    $req['key'] = $public_key;
    $req['format'] = 'json'; //supported values are json and xml


    // Generate the query string
    $post_data = http_build_query($req, '', '&');

    // Calculate the HMAC signature on the POST data
    $hmac = hash_hmac('sha512', $post_data, $private_key);

    // Create cURL handle and initialize (if needed)
    static $ch = NULL;
    if ($ch === NULL) {
        $ch = curl_init('https://www.coinpayments.net/api.php');
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    // Execute the call and close cURL handle
    $data = curl_exec($ch);
    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {
            $dec = json_decode($data, TRUE);
        }
        if ($dec !== NULL && count($dec)) {


            $coin = key($dec['result']);
            $GLOBALS['coinpaymentsZECBalance'] = $dec['result'][$coin]['balancef'];



            echo $coin . " " . $GLOBALS['coinpaymentsZECBalance'] .  "<br>" . "<br>";


            // return $dec;


        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }
    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }
}

//Get current coin exchange rates
print_r(coinpayments_api_call('balances'));

function coinpayments_api_call_rates($cmd, $req = array()) {
    // Fill these in from your API Keys page
    // erase file_get_contents and replace with api key in quotes
    $public_key = file_get_contents('./api keys/coinpaymentspublicapikey.txt');
    $private_key = file_get_contents('./api keys/coinpaymentsprivateapikey.txt');

    // Set the API command and required fields
    $req['version'] = 1;
    $req['cmd'] = $cmd;
    $req['key'] = $public_key;
    $req['format'] = 'json'; //supported values are json and xml


    // Generate the query string
    $post_data = http_build_query($req, '', '&');

    // Calculate the HMAC signature on the POST data
    $hmac = hash_hmac('sha512', $post_data, $private_key);

    // Create cURL handle and initialize (if needed)
    static $ch = NULL;
    if ($ch === NULL) {
        $ch = curl_init('https://www.coinpayments.net/api.php');
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    // Execute the call and close cURL handle
    $data = curl_exec($ch);
    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {
            $dec = json_decode($data, TRUE);
        }
        if ($dec !== NULL && count($dec)) {


            $coin = $dec['result']['ZEC']['name'];

            $ZECRate = $dec['result']['ZEC']['rate_btc'];
            $USDRate = $dec['result']['USD']['rate_btc'];

            $totalZECInBTC = $GLOBALS['coinpaymentsZECBalance'] * $ZECRate;
            $totalZECInUSD = $totalZECInBTC / $USDRate;

            $GLOBALS['coinpaymentsTotalUSD'] = $totalZECInUSD;



            echo $coin . " in USD " . $GLOBALS['coinpaymentsTotalUSD'] .  "<br>" . "<br>";


            // return $dec;


        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }
    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }
}



print_r(coinpayments_api_call_rates('rates'));



function mining_pool_hub_api_call($cmd, $req = array()) {

    $coin[0] = "digibyte-skein";
    $coin[1] = "electroneum";
    $coin[2] = "vertcoin";
    $coin[3] = "zcash";

    $coinName[0] = "DGB";
    $coinName[1] = "ETN";
    $coinName[2] = "VTC";
    $coinName[3] = "ZEC";

    // global $miningpoolhubUSD;

    $arrLength = count($coin);

    for ($i = 0; $i < $arrLength; $i++) {





    // Fill these in from your API Keys page
    // erase file_get_contents and replace with api key in quotes
    $public_key = file_get_contents('./api keys/miningpoolhubapikey.txt');
    $private_key = file_get_contents('./api keys/miningpoolhubapikey.txt');
    $user_id = file_get_contents('./api keys/miningpoolhubuserid.txt');

    // Set the API command and required fields
    $req['version'] = 1;
    $req['cmd'] = $cmd;
    $req['key'] = $public_key;
    $req['format'] = 'xml'; //supported values are json and xml



    // Generate the query string
    $post_data = http_build_query($req, '', '&');

    // Calculate the HMAC signature on the POST data
    $hmac = hash_hmac('sha512', $post_data, $private_key);

    // Create cURL handle and initialize (if needed)



        // 'https://miningpoolhub.com/index.php?page=api&action=getuserbalance&api_key=' . $private_key . '&id=' . $user_id

        $ch = curl_init('https://' . $coin[$i] . '.miningpoolhub.com/index.php?page=api&action=getuserbalance&api_key=' . $private_key . '&id=' . $user_id);
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    // Execute the call and close cURL handle
    $data = curl_exec($ch);

    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {
            $dec = json_decode($data, TRUE);
        }
        if ($dec !== NULL && count($dec)) {


               $xml = new SimpleXMLElement('<root/>');
               array_walk_recursive($dec, array ($xml, 'addChild'));
               $xml->asXML("file.xml");

            // $fp = fopen('results.json', 'w');
            // fwrite($fp, json_encode($dec));
            // fclose($fp);

            $Confirmed = $dec['getuserbalance']['data']['confirmed'];
            $Unconfirmed = $dec['getuserbalance']['data']['unconfirmed'];

            $GLOBALS['miningpoolhubUSD'][$coinName[$i]] = $Confirmed;
            $GLOBALS['miningpoolhubUSDUnconfirmed'][$coinName[$i]] = $Unconfirmed;


            echo $coinName[$i] . " confirmed" . " ". $GLOBALS['miningpoolhubUSD'][$coinName[$i]] . "<br>" . $coinName[$i] . " unconfirmed" . " " . $GLOBALS['miningpoolhubUSDUnconfirmed'][$coinName[$i]] . "<br>" . "<br>";



            // return $dec;


        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }






    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }


    }

}

//cmd getuserbalance is doing nothing
print_r(mining_pool_hub_api_call('test'));




function cryptocompare_api_call($cmd, $req = array()) {
    // Fill these in from your API Keys page
    // erase file_get_contents and replace with api key in quotes
    $public_key = file_get_contents('./api keys/coinpaymentspublicapikey.txt');
    $private_key = file_get_contents('./api keys/coinpaymentsprivateapikey.txt');

    // Set the API command and required fields
    $req['version'] = 1;
    $req['cmd'] = $cmd;
    $req['key'] = $public_key;
    $req['format'] = 'json'; //supported values are json and xml


    // Generate the query string
    $post_data = http_build_query($req, '', '&');

    // Calculate the HMAC signature on the POST data
    $hmac = hash_hmac('sha512', $post_data, $private_key);

    // Create cURL handle and initialize (if needed)
    static $ch = NULL;
    if ($ch === NULL) {
        $ch = curl_init('https://min-api.cryptocompare.com/data/pricemulti?fsyms=DGB,ETN,VTC,ZEC,XRP&tsyms=USD');
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    // Execute the call and close cURL handle
    $data = curl_exec($ch);
    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {
            $dec = json_decode($data, TRUE);
        }
        if ($dec !== NULL && count($dec)) {




           $localcoinInUSDInMiningpoolhub = Array ("DGB"=>0, "ETN"=>0, "VTC"=>0, "ZEC"=>0);
           $localcoinInUSDInMiningpoolhubUnconfirmed = Array ("DGB"=>0, "ETN"=>0, "VTC"=>0, "ZEC"=>0);

           $GLOBALS['priceInUSD[DGB]'] = $dec['DGB']['USD'];
           $GLOBALS['priceInUSD[ETN]'] = $dec['ETN']['USD'];
           $GLOBALS['priceInUSD[VTC]'] = $dec['VTC']['USD'];
           $GLOBALS['priceInUSD[ZEC]'] = $dec['ZEC']['USD'];




           $GLOBALS['priceInUSDOther'] = $dec['XRP']['USD'];





            $localPriceInUSD['DGB'] = $GLOBALS['priceInUSD[DGB]'];
            $localPriceInUSD['ETN'] = $GLOBALS['priceInUSD[ETN]'];
            $localPriceInUSD['VTC'] = $GLOBALS['priceInUSD[VTC]'];
            $localPriceInUSD['ZEC'] = $GLOBALS['priceInUSD[ZEC]'];
            // $localPriceInUSD['XRP'] = $GLOBALS['priceInUSD[XRP]'];

            $localMiningpoolhubUSD['DGB'] = $GLOBALS['miningpoolhubUSD']['DGB'];
            $localMiningpoolhubUSD['ETN'] = $GLOBALS['miningpoolhubUSD']['ETN'];
            $localMiningpoolhubUSD['VTC'] = $GLOBALS['miningpoolhubUSD']['VTC'];
            $localMiningpoolhubUSD['ZEC'] = $GLOBALS['miningpoolhubUSD']['ZEC'];

            // $localcoinInUSDInMiningpoolhub['DGB'] = $GLOBALS['coinInUSDInMiningpoolhub[DGB]'];
            // $localcoinInUSDInMiningpoolhub['ETN'] = $GLOBALS['coinInUSDInMiningpoolhub[ETN]'];
            // $localcoinInUSDInMiningpoolhub['VTC'] = $GLOBALS['coinInUSDInMiningpoolhub[VTC]'];
            // $localcoinInUSDInMiningpoolhub['ZEC'] = $GLOBALS['coinInUSDInMiningpoolhub[ZEC]'];




            foreach($localPriceInUSD as $x => $x_value) {




                $localcoinInUSDInMiningpoolhub[$x] = $x_value * $GLOBALS['miningpoolhubUSD'][$x];
                $localcoinInUSDInMiningpoolhubUnconfirmed[$x] = $x_value * $GLOBALS['miningpoolhubUSDUnconfirmed'][$x];

                echo $x . " in USD Confirmed" . " " . $localcoinInUSDInMiningpoolhub[$x]  . "<br>" . $x . " in USD Unconfirmed" . " " . $localcoinInUSDInMiningpoolhubUnconfirmed[$x]  . "<br>" . "<br>";



                $GLOBALS['totalInUSDInMiningpoolhub'] = $GLOBALS['totalInUSDInMiningpoolhub'] + ($x_value * $GLOBALS['miningpoolhubUSD'][$x]);
                $GLOBALS['totalInUSDInMiningpoolhubUnconfirmed'] = $GLOBALS['totalInUSDInMiningpoolhubUnconfirmed'] + ($x_value * $GLOBALS['miningpoolhubUSDUnconfirmed'][$x]);


                // echo $GLOBALS['totalInUSD'][$x];
                // $localPriceInUSD[$x] = $x_value * $localMiningpoolhubUSD[$x];


                // echo $localPriceInUSD[$x];
                // echo "<br>";
            }

            $GLOBALS['totalInUSDInMiningpoolhubPlusUnconfirmed'] = $GLOBALS['totalInUSDInMiningpoolhub'] + $GLOBALS['totalInUSDInMiningpoolhubUnconfirmed'];



             echo "Total USD on Miningpoolhub Confirmed " . $GLOBALS['totalInUSDInMiningpoolhub'] . "<br>" . "Total USD on Miningpoolhub Unconfirmed " . $GLOBALS['totalInUSDInMiningpoolhubUnconfirmed'] . "<br>" . "<br>" . "Total USD on Miningpoolhub including Unconfirmed " . $GLOBALS['totalInUSDInMiningpoolhubPlusUnconfirmed'] . "<br>" . "<br>";


            // return $dec;


        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }
    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }
}


cryptocompare_api_call('test');

// FINAL TESTED CODE - Created by Compcentral

// NOTE: currency pairs are reverse of what most exchanges use...
//       For instance, instead of XPM_BTC, use BTC_XPM

class poloniex {
    protected $api_key;
    protected $api_secret;
    protected $trading_url = "https://poloniex.com/tradingApi";
    protected $public_url = "https://poloniex.com/public";

    public function __construct($api_key, $api_secret) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    private function query(array $req = array()) {
        // API settings
        $key = $this->api_key;
        $secret = $this->api_secret;

        // generate a nonce to avoid problems with 32bit systems
        $mt = explode(' ', microtime());
        $req['nonce'] = $mt[1].substr($mt[0], 2, 6);

        // generate the POST data string
        $post_data = http_build_query($req, '', '&');
        $sign = hash_hmac('sha512', $post_data, $secret);

        // generate the extra headers
        $headers = array(
            'Key: '.$key,
            'Sign: '.$sign,
        );

        // curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                'Mozilla/4.0 (compatible; Poloniex PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
            );
        }
        curl_setopt($ch, CURLOPT_URL, $this->trading_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // run the query
        $res = curl_exec($ch);

        if ($res === false) throw new Exception('Curl error: '.curl_error($ch));
        //echo $res;
        $dec = json_decode($res, true);
        if (!$dec){
            //throw new Exception('Invalid data: '.$res);
            return false;
        }else{
            return $dec;
        }
    }

    protected function retrieveJSON($URL) {
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'timeout' => 10
            )
        );
        $context = stream_context_create($opts);
        $feed = file_get_contents($URL, false, $context);
        $json = json_decode($feed, true);
        return $json;
    }

    public function get_balances() {
        return $this->query(
            array(
                'command' => 'returnBalances'
            )
        );
    }

    public function get_open_orders($pair) {
        return $this->query(
            array(
                'command' => 'returnOpenOrders',
                'currencyPair' => strtoupper($pair)
            )
        );
    }

    public function get_my_trade_history($pair) {
        return $this->query(
            array(
                'command' => 'returnTradeHistory',
                'currencyPair' => strtoupper($pair)
            )
        );
    }

    public function buy($pair, $rate, $amount) {
        return $this->query(
            array(
                'command' => 'buy',
                'currencyPair' => strtoupper($pair),
                'rate' => $rate,
                'amount' => $amount
            )
        );
    }

    public function sell($pair, $rate, $amount) {
        return $this->query(
            array(
                'command' => 'sell',
                'currencyPair' => strtoupper($pair),
                'rate' => $rate,
                'amount' => $amount
            )
        );
    }

    public function cancel_order($pair, $order_number) {
        return $this->query(
            array(
                'command' => 'cancelOrder',
                'currencyPair' => strtoupper($pair),
                'orderNumber' => $order_number
            )
        );
    }

    public function withdraw($currency, $amount, $address) {
        return $this->query(
            array(
                'command' => 'withdraw',
                'currency' => strtoupper($currency),
                'amount' => $amount,
                'address' => $address
            )
        );
    }

    public function get_trade_history($pair) {
        $trades = $this->retrieveJSON($this->public_url.'?command=returnTradeHistory&currencyPair='.strtoupper($pair));
        return $trades;
    }

    public function get_order_book($pair) {
        $orders = $this->retrieveJSON($this->public_url.'?command=returnOrderBook&currencyPair='.strtoupper($pair));
        return $orders;
    }

    public function get_volume() {
        $volume = $this->retrieveJSON($this->public_url.'?command=return24hVolume');
        return $volume;
    }

    public function get_ticker($pair = "ALL") {
        $pair = strtoupper($pair);
        $prices = $this->retrieveJSON($this->public_url.'?command=returnTicker');
        if($pair == "ALL"){
            return $prices;
        }else{
            $pair = strtoupper($pair);
            if(isset($prices[$pair])){
                return $prices[$pair];
            }else{
                return array();
            }
        }
    }

    public function get_trading_pairs() {
        $tickers = $this->retrieveJSON($this->public_url.'?command=returnTicker');
        return array_keys($tickers);
    }

    public function get_total_btc_balance() {
        $balances = $this->get_balances();
        $prices = $this->get_ticker();

        $tot_btc = 0;

        foreach($balances as $coin => $amount){
            $pair = "BTC_".strtoupper($coin);

            // convert coin balances to btc value
            if($amount > 0){
                if($coin != "BTC"){
                    $tot_btc += $amount * $prices[$pair];
                }else{
                    $tot_btc += $amount;
                }
            }

            // process open orders as well
            if($coin != "BTC"){
                $open_orders = $this->get_open_orders($pair);
                foreach($open_orders as $order){
                    if($order['type'] == 'buy'){
                        $tot_btc += $order['total'];
                    }elseif($order['type'] == 'sell'){
                        $tot_btc += $order['amount'] * $prices[$pair];
                    }
                }
            }
        }

        return $tot_btc;
    }
}

$poloniex_api_key = file_get_contents('./api keys/poloniexapikey.txt');
$poloniex_secret = file_get_contents('./api keys/poloniexsecret.txt');

// change parameters in object declaration to api key and secret in quotes

$polo = new poloniex($poloniex_api_key, $poloniex_secret);

$poloniexBalance = $polo->get_balances();

echo "Ripple " . $poloniexBalance['XRP'] . "<br>";

// print_r($poloniexBalance);

// var_dump($poloniexBalance);

$poloniexUSDTotal = $poloniexBalance['XRP'] * $priceInUSDOther;

echo "Total in USD in Poloniex " . $poloniexUSDTotal . "<br>" . "<br>";

$totalInUSD = $coinpaymentsTotalUSD + $totalInUSDInMiningpoolhub + $poloniexUSDTotal;

echo "Total in USD " . $totalInUSD;



?>



</body>
</html>