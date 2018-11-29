<?php
header("content-type: application/json");

$array = array(7,4,2,8,4,1,9,3,2,16,7,12);

echo $_GET['callback']. '('. json_encode($array) . ')';

?>