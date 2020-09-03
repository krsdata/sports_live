<?php
include("disable.php");

session_start();











if (isset($_SESSION['name'])) 
{
    $auser= $_SESSION["name"];
}

 $con= mysqli_connect("13.235.148.7","admin_trin","hTURIoE4yD","admin_default");

                       
 if(!$con)
 {
     echo "unsucessfull connection connection error";
 }
?>