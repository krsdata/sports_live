<?php
phpinfo();

//echo "hi";die();

function httpGet($url)
{
    $ch = curl_init();  
 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
//  curl_setopt($ch,CURLOPT_HEADER, false); 
 
    $output=curl_exec($ch);
 
    curl_close($ch);
    return $output;
}

echo httpGet("https://rest.entitysport.com/v2/matches/43685/scorecard?token=8740931958a5c24fed8b66c7609c1c49");
?>
