<?php
error_reporting(1);
$con=mysqli_connect("localhost","root","Server@db2019","matchons_cric_1");

//get match contests
$query1211 = 'SELECT 
				userid,
				amount, 
				type, 
				contestid, 
				date1, 
				count(*) as NumDuplicates 
			FROM transaction 
			WHERE 
				type = "fee" AND 
				date1 >= "2020-08-10 00:00:01" 
			GROUP BY 
				userid, 
				contestid, 
				amount, 
				date1 
			ORDER BY transaction.date1 ASC';
$result1211=mysqli_query($con,$query1211);
//echo $rows1211=mysqli_num_rows($result1211);
mysqli_close($con);

$i=0;
while ($rows1211 = mysqli_fetch_assoc($result1211)) 
{
	//echo "<pre>"; print_r($rows1211); die;
	$querye = "SELECT count(*) as NumDuplicates 
			FROM transaction 
			WHERE 
				type = 'cancelled' AND 
				userid = ".$rows1211['userid']." AND 
				contestid = ".$rows1211['contestid']." 
			group by `userid`, `contestid`, `amount`, date1 
			having NumDuplicates > 1  
			ORDER BY `transaction`.`date1`  DESC";
	// echo "<br />";

	$con=mysqli_connect("localhost","root","Server@db2019","matchons_cric_1");
	$resulte=mysqli_query($con,$querye);
	$rowse=mysqli_fetch_assoc($resulte);
	mysqli_close($con);
	echo $i++."<br />";
	echo "<pre>"; var_dump($rowse);

	if ($i == 20)
		break;
	//delete transaction row for $rowse['NumDuplicates']-1 times
	// if ($rowse) 
	// {
	// 	echo "<pre>"; print_r($rowse);
	// }
	//var_dump($rowse);

	/*while ($rowse = mysqli_fetch_array($resulte)) 
	{
		$emailoo=$rowse['userid'];
		$amount=$rowse['amount'];
		$transaction_id=$rowse['transaction_id'];
		//die;

		$qry111="UPDATE `wallet1` set wamount=wamount-'$amount' where userid='$emailoo'";
    	mysqli_query($con,$qry111);

		if($amount>0)
		{
			$query112="DELETE FROM transaction WHERE transaction_id = ".$transaction_id;
			mysqli_query($con,$query112);

			//$query112="INSERT INTO `transaction`(`userid`, `amount`, `type`,contestid) VALUES ('$emailoo','$amount','technical issue','$contest_id11')";
			//mysqli_query($con,$query112);
		}
	}*/
}
?>