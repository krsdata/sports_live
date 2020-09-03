<?php
$servername = "matchon.cluster-coypwqvokxbj.ap-south-1.rds.amazonaws.com";
//$servername = "localhost";
//$password = "E[;?1Uhq}SnB";
//$dbname = "matchons_cric_1_100919";

//LIVE DATABASE
$username = "matchon";
$password = "matchon#2020";
$dbname = "matchon_live";

//TEST DATABASE
/*$servername = 'localhost';
$username = "root";
$password = "Server@db2019";
$dbname = "pro_backup";*/

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
