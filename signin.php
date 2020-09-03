<?php
session_start();

$email=$_POST["email"];
$pass=$_POST["password"];

include("connectionnew.php");

$_SESSION["broker"]="1";



if(!$con)
{
    echo "Error in connection";
 header("Location: https://matchonsports.com/index.php");
}


//$email=mysqli_real_escape_string($con, $_POST["email"]);
//$pass=mysqli_real_escape_string($con, $_POST["password"]);

$email=trim($email);
$pass=trim($pass);


$email=str_replace(' ','t',$email);
$pass=str_replace(' ','t',$pass);

$email=str_replace(';','t',$email);
$pass=str_replace(';','t',$pass);

$email=str_replace('=','t',$email);
$pass=str_replace('=','t',$pass);

$email=str_replace(')','t',$email);
$pass=str_replace(')','t',$pass);

$email=str_replace('(','t',$email);
$pass=str_replace('(','t',$pass);


//$email=strip_tags(trim($email));
//$pass=strip_tags(trim($pass));

//$params = array($email, $pass);


//$email=mysqli_real_escape_string($link11, trim($email));
//$pass=mysqli_real_escape_string($link11, trim($pass));


$qry="SELECT * FROM `user` WHERE `email1`='$email' and `password`='$pass'";
$result = mysqli_query($con,$qry);
$rows = mysqli_num_rows($result);
$stat=0;

//echo "$qry";
if($rows>=1)
{
//echo "$qry";
    while ($rows = mysqli_fetch_array($result)) 
    {
        $_SESSION["name"] = $rows['username'];
        $_SESSION["email"] = $rows['email'];
          $_SESSION["email1"] = $rows['email1'];
      $stat=$rows['status'];
    }



$gmail1=$_SESSION["email"];
$qry12="SELECT * FROM `wallet1` WHERE `userid`='$gmail1'";
$result12 = mysqli_query($con,$qry12);
$rows12 = mysqli_num_rows($result12);
//echo "$qry";
if($rows12>=1)
{
  
}
else
{
 $qry112="INSERT INTO `wallet1`(`userid`, `damount`, `wamount`, `bamount`) VALUES ('$gmail1','0','0','0')";
    $result112 = mysqli_query($con,$qry112);
}

   

    $_SESSION["log"]=3;



  if($stat==2)
  header("Location: https://matchonsports.com/home");
 else
   header("Location: https://matchonsports.com/register/otp.php");




}
else
{
$_SESSION["log"]=4;
 echo "<script type='text/javascript'>alert('Invalid Credits.. Please Try again.');
window.location='https://matchonsports.com/index.php';
</script>";
}

?>