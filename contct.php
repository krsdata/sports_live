<?php
session_start();
//print_r($_POST);die();
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    $fname = $_POST["fname"];
    $lname = $_POST["lname"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
	$msg = $_POST["msg"];
	//require_once "../db/config.php";
	require_once "mos_admin/db/config.php";
	$qry = "INSERT INTO `contact` (`fname`, `lname`, `phone`, `email`, `msg`) VALUES ('$fname', '$lname', '$phone', '$email', '$msg');";
	$res = mysqli_query($conn,$qry);
	if ($res)
		$_SESSION['TYPE'] = "Thank you for contact with us. We will contact to you soon.";
	else
		$_SESSION['TYPE'] = "Thank you for contact with us. We will contact to you soon.";

	header("location: http://matchonsports.com/");
	mysqli_close($conn);
}else
	{
		$_SESSION['TYPE'] = "Thank you for contact with us. We will contact to you soon.";
		header("location: http://matchonsports.com/");
		exit;
	}
?>