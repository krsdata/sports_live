<?php
include("../../../db/config.php");
session_start();

$match_id=$_GET['trans1'];



$ty=rand(101,1000);
 $_SESSION["mid"]=$ty;


?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
  
  <!-- CSS Files -->
  <link href="./assets/css/material-kit.css?v=2.0.3" rel="stylesheet" />
  <!-- CSS Just for demo purpose, don't include it in your project -->
  <link href="./assets/demo/demo.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
rel="stylesheet">
        <style>


.header4 {
    background-color: white;
    color: black;
   
     text-align: center;
     width: 98%;
height: 23%;
     font-size: 12px;
     
    position: absolute;
   top: 63%;
    left: 1%;
       
}


.header5 {
    background-color: #3b5998;
    color: white;
    padding: 1%;
     text-align: center;
     width: 30%;
     font-size: 15px;
     
    position: absolute;
   top: 5%;
    left: 33%;
       
}

.header6{
    background-color: #3b5998;
    color: white;
    padding: 1%;
      width: 30%;
     font-size: 15px;
     
    position: absolute;
   top: 5%;
    left: 3%;
       
}

.header7{
    background-color: #3b5998;
    color: white;
    padding: 1%;
      width: 30%;
     font-size: 15px;
    text-align:right;
     
    position: absolute;
   top: 5%;
    right: 3%;
       
}




        body
        {
            background: url(./assets/img/bg.jpg)no-repeat;
            background-color:#3b5998;
            background-size: 115%;
            background-position-x: 35%;
        }
    .navbar
        {
            width:555px;
            margin:auto;
        }
    .navbar-top
    {
         background-color:#3b5998 !important;
        height: 90px;
        font-size: 30px;
    }
    .navbar-brand
       {
           margin:auto;
           color: white !important;
           font-weight:bolder !important;
           font-size: 25px !important;
       }
    .middle
    {
        width: 555px;
    height: 89vh;
    margin: auto;
    margin-top: 70px;
    background-color: white;
    padding: 15px;
    overflow: auto;
    }
    .navbar-bottom
    {
 background-color: #3b5998;
        padding: 10px;
        height: 50px;
    }
    .navbar-bottom .material-icons
    {
        margin-left: 4px;
        font-size: 45px;
        color: #0078d780;
        transition: .3s;
        cursor:pointer;
    }
    .navicon
    {
        width: 50px;
        height: 30px;
        margin-left: auto;
        margin-right: auto;
    }


/* Match cards starts */

.card
{
  margin-bottom: 0px;
    margin-top: 10px;
}
.left
{
  height: 100px;
    border-radius: 5px;
    display: inline;
    margin-right: 4px;
    margin-top: 0px;
}
 
.right
{
  height: 100px;
    border-radius: 5px;
    display: inline;
    margin-left: 2px;
    margin-right: 0px;
    margin-top: 0px;
}
  .font
  {
    font-size:15.5px;
    font-weight:bold;
  }
  small
  {
    color:black;
    font-weight:bold;
  }
  .main
  { 
    padding: 10px;
  height: 100vh;
  overflow: auto;
  
  }

  @media only screen and (min-width: 768px) {
   .card
   {
     width:500px;
   }
   .left
  {
    height: 100px;
    border-radius: 5px;
    display: inline;
    margin-right: 40px;
    margin-top: 0px;
  }
  .right
  {
    height: 100px;
    border-radius: 5px;
    display: inline;
    margin-left: 40px;
    margin-right: 0px;
    margin-top: 0px;
  }
   .main
  {
  
    padding: 15px;
    width:555px;
  height: 92vh;
  overflow: auto;
 
  }
  .mar
  {
    margin: -15px;
    width: 555px;
    color: white;
    font-weight: bold;
    background: #3582ff;
  }
}
.mar
  {
    margin: -15px;
    width: 109%;
    border-radius: 0px;
    background: #3582ff;
  }
  
/* Match cards ends */


       /* media query starts for bellow 600px*/
@media only screen and (max-width: 600px)
       {
        body
        {
            background: url(./assets/img/bg.jpg)no-repeat;
           background-color:#0078d7;
            background-position-x: 45%;
        }
        .navbar
        {
            width:100%;
            margin:auto;
           
        }
        .middle
    {
        width:100%;
        height:auto;
        margin:auto;
        margin-top:70px;
        background-color:#cccccccc;
    }
    .navbar-bottom .material-icons
    {
        margin-left: 8px;
        font-size: 35px;

    }
    .navicon
    {
        width: 50px;
        height: 30px;
        margin-left: auto;
        margin-right: auto;
    }
       }
       /* media query ends*/
.navbar-bottom .material-icons:hover
{
    color:#0078d7;
    transition: .5s;
}
.modal .modal-header .close i
  {
    color:white;
  }
  .modal-title
  {
    margin-bottom: 20px;
    color:white;
  }
  .modal-open .modal
  {
    margin-top: 140px;
  }
 .top
  {
      margin-top: 75px;
  }
  .btm
  {
    margin-top: 11px;
    margin-bottom: 11px;
  }
  
  .row {
    display: flex;
    flex-wrap: wrap;
    margin-right: auto;
    margin-left: auto;
  }
  .tit
  {
    font-size:12px;
    font-weight:bolder;
 font-color:black;
  }
  .blder
  {
    font-weight:bolder;
  }
.bttn
{
  border:none;
  background-color: #ffffff00;
}

.main
  {
    padding: 15px;
    margin-top: 46px;
  height: 86vh;
  overflow: auto;
  background: #03a9f4;
  }

  @media only screen and (min-width: 768px) {
   .card
   {
     width:500px;
   }
   .main
  {
  
    padding: 15px;
    width:555px;
  height: 92vh;
  overflow: auto;
  background: #03a9f4;
  }
}

@media only screen and (min-width: 768px) {
  .modal .modal-dialog
{
  margin-top:28%;
}
}


         </style>

    </head>
    <body>
    
<div class="navbar navbar-top fixed-top">
<div class="header6">
<?php
echo '
<a href="mycontests_new.php">
<i class="material-icons" style="font-size: 20px; color:white;">
arrow_back
</i></a>

';

?>
</div>

<div class="header7">
<?php
/*
echo '
<a href="#" onClick="alert(\'Wallet Balance: Rs.0\')">
<i class="material-icons" style="font-size: 20px; color:white;">
account_balance_wallet
</i></a>

';
*/
?>
</div>


<div class="header5">
CONTESTS</div>
<div class="header4">

<?php
                       //$qry='SELECT matches.matchid,matches.time,matches.match_type, team1.team_name AS team1, team1.image AS team1_image, team2.team_name AS team2, team2.image AS team2_image FROM matches JOIN team team1 ON matches.t1= team1.id LEFT OUTER JOIN team team2 ON matches.t2=team2.id WHERE matches.matchid='.$match_id;
					   $qry='SELECT * FROM API_matches WHERE `mat_id`='.$match_id;

                        $result1 = mysqli_query($conn,$qry);
                        $rows1 = mysqli_num_rows($result1);

                        if($rows1>0)
                        {
                          while ($rows1 = mysqli_fetch_array($result1)) {
                            $mat_comp_id = $rows1['mat_comp_id'];
							$t1 = $rows1['mat_team1_id'];
							$t2 = $rows1['mat_team2_id'];
							
							$sql0 = "SELECT comp_title FROM API_competition WHERE `comp_id` = '$mat_comp_id'";
							$result0 = mysqli_query($conn,$sql0);
							$row0 = mysqli_fetch_assoc($result0);
							$comp_title = $row0['comp_title'];
							
							$sql1 = "SELECT team_logo_url FROM API_team WHERE `team_id` = '$t1'";
							$r1 = mysqli_query($conn,$sql1);
							$row1 = mysqli_fetch_assoc($r1);
							$sql2 = "SELECT team_logo_url FROM API_team WHERE `team_id` = '$t2'";
							$r2 = mysqli_query($conn,$sql2);
							$row2 = mysqli_fetch_assoc($r2);

                            $t1_image=$row1['team_logo_url'];

                            $t2_image=$row2['team_logo_url'];


                            $matchtype=$rows1['mat_sub_title'];
							$mat_short_title=$rows1['mat_short_title'];

                            $match3=$rows1['mat_id'];

                            $finaldate=$rows1['mat_startdate'];

                          /*  echo '<table><tr><td><p><i  style="font-size: 13px; color:black; text-align: left; padding-right:130px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$t1.' vs '.$t2.'</i></p></td>

<td>

<p class="text-center"><i style="font-size: 13px; color:red;" class="material-icons">alarm</i><i  style="font-size: 13px; color:red;" id="'.$match3.'"></i></p></td>


<td>
<p><i  style="font-size: 13px; color:black; text-align: right; padding-left:130px;">'.$matchtype.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</i></p>
</td>

</tr></table>*/

echo '

<style>
.flex-container {
  display: flex;
  flex-wrap: nowrap;
  background-color: white;
  
}


.new {
  background-color: white;
  width: 200PX;
  margin: 3px;
  color: Black;
  text-align: center;
  line-height: 22px;
  font-size: 14px;
 text-decoration: none;
}
</style>


<div class="flex-container">
<div class="new">'.$mat_short_title.'</div>
<div class="new"><i style="font-size: 13px; color:red;" class="material-icons">alarm</i><i  style="font-size: 13px; color:red;" id="'.$match3.'"></i></div>
<div class="new">'.$matchtype.'</div>
</div>';


echo'

<script>
// Set the date were counting down to
 var countDownDate'.$match3.'= new Date("'.$finaldate.'").getTime();

// Update the count down every 1 second
var x'.$match3.' = setInterval(function() {

    // Get todays date and time
    var now'.$match3.' = new Date().getTime();
    
    // Find the distance between now an the count down date
    var distance'.$match3.' = countDownDate'.$match3.' - now'.$match3.';
    
    // Time calculations for days, hours, minutes and seconds
    var days'.$match3.' = Math.floor(distance'.$match3.' / (1000 * 60 * 60 * 24));
    var hours'.$match3.' = Math.floor((distance'.$match3.' % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    var minutes'.$match3.' = Math.floor((distance'.$match3.' % (1000 * 60 * 60)) / (1000 * 60));
    var seconds'.$match3.' = Math.floor((distance'.$match3.' % (1000 * 60)) / 1000);
    
    // Output the result in an element with id="demo"
    document.getElementById("'.$match3.'").innerHTML =days'.$match3.' + "d:" + hours'.$match3.' + "h:"
    + minutes'.$match3.' + "m:" + seconds'.$match3.' + "s";
    
    // If the count down is over, write some text 
    if (distance'.$match3.' < 0) {
        clearInterval(x'.$match3.');
        document.getElementById("'.$match3.'").innerHTML = "Completed";



    }
}, 1000);
</script> ';

}
}
?>


</div>
</div>


      <div class="middle">
<br />
<?php
$email=$_SESSION["email"] ;
$ctccount=0;
$arr = array(1,2);

$qry="SELECT `contests_id`, `amount`, `no_of_teams`, `fee` FROM `contests` where `matchid`='$match_id'";
$result=mysqli_query($conn,$qry);
$rows=mysqli_num_rows($result);
while($rows=mysqli_fetch_assoc($result))
{
  $contest_id=$rows['contests_id'];
  $amt=$rows['amount'];
  $no_of_teams=$rows['no_of_teams'];
  $fee=$rows['fee'];
$winners=0;

$qry1="SELECT  max(max1) as ct FROM `prize` where `contestid`='$contest_id'";
$result1=mysqli_query($conn,$qry1);
while($rows1=mysqli_fetch_assoc($result1))
{

  $winners=$rows1['ct'];

}
  
  
$qry2="SELECT count(*) as cont1 FROM `matches_joined` where  contests_id='$contest_id'  and  `match_id`='$match_id'";
$result2=mysqli_query($conn,$qry2);
$rows2=mysqli_num_rows($result2);
while($rows2=mysqli_fetch_assoc($result2))
{

$contest_id111=$rows2['cont1'];
$diff1=  $no_of_teams - $contest_id111;
$perc1= (100-($diff1*100)/$no_of_teams);
$ctccount++;
$arr[$ctccount]=$contest_id;

$ty1=((intval($contest_id)+$ty)*3)-$ty;

  echo '
<a href="contests3_new.php?trans1='.$contest_id.'&trans2='.$match_id.'">
<div class="card btm mx-auto">
  <div class="row">
  <div class="col float-left" style="width: 255px;">
  <span class="tit" style="color:black;" >Total Prize Money</span><br><span class="blder" style="color:black;">'.$amt.'</span>
  </div>
  <div class="col text-center" style="width: 150px;">
  <span class="tit" style="color:black;">Winners </span><br><span class="blder"><button class="bttn"  style="color:black;" data-toggle="modal" data-target="#myModal'.$ctccount.'">'.$winners.'<span class="dropdown-toggle"></span></button></span>
  </div>
  <div class="col  text-right float-right">
  <span class="tit" style="color:black;">Entry Fee</span><br><span class="blder" style="color:black;">'.$fee.'</span>
  </div>
  </div>

  <div class="container">
  <div class=" progress progress-line-danger" style="width:80%;">
                <div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: '.$perc1.'%;">
                  <span class="sr-only">50% Complete</span>
                </div>
              </div>
    </div>
    <div class="container">
    <div style="display:inline; color:black;">
    '.$diff1.' Teams Left
    </div>
     ';

/*
  if($diff1>0)
{
       echo '<div style="display:inline;float: right;"><a href="matchteam1t.php?trans1='.$ty1.'">
<div class="header3">JOIN</div></a></div>';
}
else
{
     echo '<div style="display:inline;float: right;"><a href="#">
<div class="header3">FULL</div></a></div>';
}
*/
    
   echo '
    <div style="float: right;display: inline;margin-right: 20px; color:black;">
    '.$no_of_teams.' Teams
    </div>
   

    </div>
    <br />
   </div>
</a>
';







}

  
}


?>
<br /><br /><br />
</div>
<div>

<?php

for($i=1;$i<=$ctccount;$i++)
{
 echo '<!-- Classic Modal -->
   <div class="modal fade" id="myModal'.$i.'" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h5 class="modal-title">Rank and Prize</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i class="material-icons">clear</i>
          </button>
        </div>
        <div class="modal-body">';


$qry1="SELECT  * FROM `prize` where `contestid`='$arr[$i]'";
$result1=mysqli_query($conn,$qry1);
while($rows1=mysqli_fetch_assoc($result1))
{

$min1=$rows1['min1'];
$max1=$rows1['max1'];
$prize1=$rows1['prize'];

    if($min1==$max1)
   {  
     echo '<span>Rank '.$min1.'</span><span class="float-right">'.$prize1.'</span><br>';

    }
else
  {
 echo '<span>Rank '.$min1.'-'.$max1.'</span><span class="float-right">'.$prize1.'</span><br>';

   }
         

}


echo'
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger btn-link" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!--  End Modal -->
</div>';
}  

?>
          

 </div>




      </div>



      <?php

echo '
<div class="navbar navbar-bottom fixed-bottom">
     

      <div class="navicon">
      <a href="logout.php" style="color:white;"><i class="material-icons">account_circle</i>
<br />
&nbsp; Logout</a>
      </div>
   

     <div>';

      ?>
      
      
        <!--   Core JS Files   -->
  <script src="./assets/js/core/jquery.min.js" type="text/javascript"></script>
  <script src="./assets/js/core/popper.min.js" type="text/javascript"></script>
  <script src="./assets/js/core/bootstrap-material-design.min.js" type="text/javascript"></script>
  <script src="./assets/js/plugins/moment.min.js"></script>
  <!--	Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
  <script src="./assets/js/plugins/bootstrap-datetimepicker.js" type="text/javascript"></script>
  <!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
  <script src="./assets/js/plugins/nouislider.min.js" type="text/javascript"></script>
  <!-- Control Center for Now Ui Kit: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/material-kit.js?v=2.0.3" type="text/javascript"></script>
  <script>
    $(document).ready(function() {
      //init DateTimePickers
      materialKit.initFormExtendedDatetimepickers();

      // Sliders Init
      materialKit.initSliders();
    });


    function scrollToDownload() {
      if ($('.section-download').length != 0) {
        $("html, body").animate({
          scrollTop: $('.section-download').offset().top
        }, 1000);
      }
    }
  </script>
    </body>
</html>