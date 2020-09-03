
<html>
   <head>
       <title>404</title> 
              
        <meta name="viewport" content="width=device-width">
       
       
        <link href="/favicon.ico" rel="icon" type="image/x-icon" /> <link href="https://fonts.googleapis.com/css?family=Abhaya+Libre|Laila" rel="stylesheet">
        
<style>
a{font-weight:bold;text-decoration:none;}
body,input{text-align:center;letter-spacing:1px;font-family: Laila;}
hr{border: 0;height: 0.7px;background-image:linear-gradient(to right, red, blue, Green);}input,select{outline:none;}
.text,select{background-image:linear-gradient(to right, green, green);width: 83%;padding: 10px 20px;margin: 8px 0;display: inline-block; border: 3px solid ;border-radius: px; box-sizing: border-box;text-align:center;font-weight:bold;color:yellow;
}
::placeholder{font-weight:bold;color:White;}
.submit{font-weight:bold;width: 45%;background-image:linear-gradient(to right, Blue, blue);color: white;padding: 14px 20px;margin: 8px 0;border: none;border-radius: 4px;cursor: pointer;}
.submit:hover{background-image:linear-gradient(to right, red);}

bold{color:linear-gradient(to right, red, blue, red);font-weight:bold;}body{color:#F50057;}
</style>



<?php
error_reporting(0);
	
		function RandomNumber($length){
$str="";
for($i=0;$i<$length;$i++){
$str.=mt_rand(0,9);
}
return $str;
}

$f = array("sameer","suresh","chettiar","jatin","chauhan","agarwal","rahul","tanmay","tiwari","kunal","rasania","sunil","kumar","gaurav","arihant","jain","falguni","yashashree","arpi","arshish","gupta","tanmay","samtgr","kiyera","atul","abhay","chandra","shoibaakriti","aanchal","talreja","aatholiy","abhijeet","akkalwar","abhijeet","bajpai","abhijeetsh","abhirup","roy","abhishek","sumit","kapil","suman","rani","ramu","souvik","yogesh","umesh","sahadat","ankit","prasant","pravakar","sunil","sibaram");
$fname = $f[mt_rand(0,50)];

$l = array("sameer","suresh","chettiar","jatin","chauhan","agarwal","rahul","tanmay","tiwari","kunal","rasania","sunil","kumar","gaurav","arihant","jain","falguni","yashashree","arpi","arshish","gupta","tanmay","samtgr","kiyera","atul","abhay","chandra","shoibaakriti","aanchal","talreja","aatholiy","abhijeet","akkalwar","abhijeet","bajpai","abhijeetsh","abhirup","roy","abhishek","sumit","kapil","rani","ramu","souvik","yogesh","umesh","sahadat","ankit","prasant","pravakar","sunil","sibaram");
$lname = $l[mt_rand(0,50)];



$no = rand(1,999);


function rando($length) {
    $characters = '1234567890abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$name=''.$fname.''.$lname.''.$no.'';

$i3=RandomNumber(3);
$token=RandomNumber(16);
$i10=RandomNumber(9);
$number='9738'.$numb.'';
$i16=Rando(150);


$url1="https://matchonsports.com:443/app_code/V1/ServiceController/generateChecksum";


$data1="MID=MATCHO28893$i10&EMAIL=$i10@gmail.com&MOBILE_NO=9$i10&ORDER_ID=order893$i3&CUST_ID=179$i3&INDUSTRY_TYPE_ID=Retail&CHANNEL_ID=WAP&TXN_AMOUNT=100&WEBSITE=WEBPROD&CALLBACK_URL=https://securegw.paytm.in/theia/paytmCallback?ORDER_ID=order893$i3";

$headers1[]="token: $refer";
$headers1[]='Content-Type: application/x-www-form-urlencoded';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($ch, CURLOPT_HTTPHEADER,$headers1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output1= curl_exec ($ch);
	$json1=json_decode($output1,true);
	curl_close ($ch);
	
$cas=$json1["CHECKSUMHASH"];


$url="https://matchonsports.com:443/app_code/V1/ServiceController/insertCash";


$data="amount=100&checkSum=$cas";

$headers[]="token: $refer";
$headers[]='Content-Type: application/x-www-form-urlencoded';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$output= curl_exec ($ch);
	$json=json_decode($output,true);
	curl_close ($ch);
	
$ud=$json["cards"]['0']["scratch_card_id"];


echo "<font color='green' size='2'><bold>$output</bold><hr></font>
";

	
?>