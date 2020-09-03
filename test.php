<?php 
//

$test=$_GET["apkid"];

if($test<1.0)
{
    $match_fixtures="1";
}
else
{
    
        $match_fixtures="0";

}


 
    $json1 = json_encode($match_fixtures);
                        
                            
   echo $json1;  


?>