<?php
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");
// following files need to be included
require_once("lib/config_paytm.php");
require_once("lib/encdec_paytm.php");

$checkSum = "";
$paramList = array();

$ORDER_ID = time()."89357";
$CUST_ID = "89357";
$INDUSTRY_TYPE_ID = "Retail109";
$CHANNEL_ID = "WEB";
$TXN_AMOUNT = 1;

// Create an array having all required parameters for creating checksum.
$paramList["MID"] = "MATCHO28893336383296";
$paramList["ORDER_ID"] = $ORDER_ID;
$paramList["CUST_ID"] = $CUST_ID;
$paramList["INDUSTRY_TYPE_ID"] = $INDUSTRY_TYPE_ID;
$paramList["CHANNEL_ID"] = $CHANNEL_ID;
$paramList["TXN_AMOUNT"] = $TXN_AMOUNT;
$paramList["WEBSITE"] = "DEFAULT";


$paramList["CALLBACK_URL"] = "http://matchonsports.com/mos_admin/PaytmKit/pgResponse.php";
$paramList["MSISDN"] = "9713908893"; //Mobile number of customer
$paramList["EMAIL"] = "srp2587@gmail.com"; //Email ID of customer
$paramList["VERIFIED_BY"] = "srp2587@gmail.com"; //
$paramList["IS_USER_VERIFIED"] = "YES"; //


//Here checksum string will return by getChecksumFromArray() function.
$checkSum = getChecksumFromArray($paramList,"%FSOOp#MZJVTPrY4");

?>
<html>
<head>
<title>Merchant Check Out Page</title>
</head>
<body>
	<center><h1>Please do not refresh this page...</h1></center>
		<form method="post" action="<?php echo 'https://securegw.paytm.in/order/process'; ?>" name="f1">
		<table border="1">
			<tbody>
			<?php
			foreach($paramList as $name => $value) {
				echo '<input type="hidden" name="' . $name .'" value="' . $value . '">';
			}
			?>
			<input type="hidden" name="CHECKSUMHASH" value="<?php echo $checkSum ?>">
			</tbody>
		</table>
		<script type="text/javascript">
			document.f1.submit();
		</script>
	</form>
</body>
</html>