 <?php

include("disable.php");
           session_start();


if(isset($_SESSION["mid"]))
{
$trans1=$_GET['trans1'];

//$trans12=$_GET['trans121'];

try{
$valu=intval($trans1);
$valu1=intval($_SESSION["mid"]);


$valu=$valu+$valu1;
$valu=$valu/3;
$valu=$valu-$valu1;

$_SESSION["val11"]=$valu;

/*
$valu12=intval($trans12);
$valu12=$valu12+$valu1;
$valu12=$valu12/3;
$valu12=$valu12-$valu1;

$_SESSION["middle201"]=$valu12;
*/

$_SESSION["broker"]="16";
 include("homepath1.php");


}
catch(Exception $e)
{
 header("Location: homet.php");

}



}
else
{
 header("Location: homet.php");
}   
	