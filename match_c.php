<?php
$con= mysqli_connect("localhost","matchons_cric_1","uwTH3diB[!=&","matchons_cric_1");

$query1211="SELECT *  FROM `contests` WHERE `matchid`='****'";

$result1211=mysqli_query($con,$query1211);

$rows1211=mysqli_num_rows($result1211);

while ($rows1211 = mysqli_fetch_array($result1211)) 

{

	

$contest_id11=$rows1211['contests_id'];

$fees12=$rows1211['fee'];





$querye="SELECT user_id  FROM `matches_joined` WHERE  contests_id='$contest_id11'";

$resulte=mysqli_query($con,$querye);

$rowse=mysqli_num_rows($resulte);

while ($rowse = mysqli_fetch_array($resulte)) 

{



$emailoo=$rowse['user_id'];







$qry111="UPDATE `wallet1` set  damount=damount+'$fees12'  where userid='$emailoo'";

    $result11121 = mysqli_query($con,$qry111);





if($fees12>0)

{

$query112="INSERT INTO `transaction`(`userid`, `amount`, `type`,contestid) VALUES ('$emailoo','$fees12','cancelled','$contest_id11')";

$result112=mysqli_query($con,$query112);

}





}


$querye="SELECT userid,amount  FROM `transaction` WHERE  contestid='$contest_id11' AND type='winning'";

$resulte=mysqli_query($con,$querye);

$rowse=mysqli_num_rows($resulte);

while ($rowse = mysqli_fetch_array($resulte)) 

{



$emailoo=$rowse['userid'];
$wamount=$rowse['amount'];






$qry111="UPDATE `wallet1` set  wamount=wamount-'$wamount'  where userid='$emailoo'";

    $result11121 = mysqli_query($con,$qry111);





if($fees12>0)

{

$query112="INSERT INTO `transaction`(`userid`, `amount`, `type`,contestid) VALUES ('$emailoo','$wamount','technical issue','$contest_id11')";

$result112=mysqli_query($con,$query112);

}





}



}?>