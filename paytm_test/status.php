<?php
/**
* import checksum generation utility
* You can get this utility from https://developer.paytm.com/docs/checksum/
*/
require_once("encdec_paytm.php");

/* initialize an array */
$paytmParams = array();

/* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
$paytmParams["MID"] = "MATCHO28893336383296";

/* Enter your order id which needs to be check status for */
$paytmParams["ORDERID"] = "862151582776288528";

/**
* Generate checksum by parameters we have
* Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
*/
$checksum = getChecksumFromArray($paytmParams, "+mYdaYZaHoZVKawjvk6KudZmo2VUTqS9BmyrsEqODqQf5D9R7HtBiEavG8BkbxYvCT+0L2AGFNGyWzIkXRtD\/0ZLW6AC6dBKE+wg3yqox5U=");

/* put generated checksum value here */
$paytmParams["CHECKSUMHASH"] = $checksum;

/* prepare JSON string for request */
$post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

/* for Staging */
//$url = "https://securegw-stage.paytm.in/order/status";

/* for Production */
 $url = "https://securegw.paytm.in/order/status";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  
$response = curl_exec($ch);
print_r($response);
?>