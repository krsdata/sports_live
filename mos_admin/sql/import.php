<?php
$sql = file_get_contents('file/matchons_cric_1 (25).sql');

$mysqli = new mysqli("matchon.cluster-ccssspmiqenl.ap-south-1.rds.amazonaws.com", "matchon", "Matchon2020", "matchons_cric_1_100919");
if (mysqli_connect_errno()) { /* check connection */
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

/* execute multi query */
if ($mysqli->multi_query($sql)) {
    echo "success";
} else {
   echo "error";
}
?>