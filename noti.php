 
 <?php
 
 $user=$_SESSION['email'];
 
 
 
 echo '
 
 
 
<style>
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-content {
    display: none;
    position:absolute;
    top:5%;
    right:5%;
    background-color: #f9f9f9;
    min-width: 300px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    padding: 12px 16px;
   align:right;
}

.dropdown:hover .dropdown-content {
    display: block;
}
</style>
 
 
 
 <div class="dropdown">
  <span><p class="notfication"><i class="material-icons" >notifications</i></p> 
      </span>
  <div class="dropdown-content" style="overflow:scroll; height:500px;">
  ';
  
  
  $count=0;
  
  $qry1="SELECT * FROM `transaction` WHERE `userid`='$user' order by date1";
$result1 = mysqli_query($con,$qry1);
    while ($rows1 = mysqli_fetch_array($result1)) 
    {
        $damount=$rows1['type'];
 $wamount=$rows1['amount'];
 
 if($damount=='deposit')
 {
     echo '<p>Your deposit of Rs.'.$wamount.' is successful.</p><hr>';
      $count++;
 }
 
  if($damount=='winnings')
 {
     echo '<p>Your have won Rs.'.$wamount.' in a contest.</p><hr>';
      $count++;
 }
 
  if($damount=='Fees')
 {
     echo '<p>Your have successfully joined with Rs.'.$wamount.' in a contest.</p><hr>';
      $count++;
 }
 
 
 if($count>10)
   break; 
  

    }
    
    
    if($count==0)
        echo '<p>No New Notifications</p><hr>';
  
  
  
  
             
             echo '
  </div>
</div>

<p class="noti-color"><a class="" href="#popup1" style="color: #f8f9fa;"> '.$count.'</a></p>
 ';
 
 
 

      
      
      
      
      
      
      
      ?>
      
      