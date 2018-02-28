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

$coinInUSDInMiningpoolhub = Array ();

$coinInUSDInMiningpoolhub['DGB'] = 0;
$coinInUSDInMiningpoolhub['ETN'] = 0;
$coinInUSDInMiningpoolhub['VTC'] = 0;
$coinInUSDInMiningpoolhub['ZEC'] = 0;

$totalInUSDInMiningpoolhub = 0;

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

echo "<br>" . "<br>";



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

           $GLOBALS['priceInUSD[DGB]'] = $dec['DGB']['USD'];
           $GLOBALS['priceInUSD[ETN]'] = $dec['ETN']['USD'];
           $GLOBALS['priceInUSD[VTC]'] = $dec['VTC']['USD'];
           $GLOBALS['priceInUSD[ZEC]'] = $dec['ZEC']['USD'];
           // $GLOBALS['priceInUSD[XRP]'] = $dec['XRP']['USD'];

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




                $localcoinInUSDInMiningpoolhub[$x] = $x_value * $GLOBALS['miningpoolhubUSD'][$x] . "<br>";
                echo $localcoinInUSDInMiningpoolhub[$x];



                $GLOBALS['totalInUSDInMiningpoolhub'] = $GLOBALS['totalInUSDInMiningpoolhub'] + ($x_value * $GLOBALS['miningpoolhubUSD'][$x]);

                // echo $GLOBALS['totalInUSD'][$x];
                // $localPriceInUSD[$x] = $x_value * $localMiningpoolhubUSD[$x];


                // echo $localPriceInUSD[$x];
                // echo "<br>";
            }





             echo $GLOBALS['totalInUSDInMiningpoolhub'];





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






?>



</body>
</html>