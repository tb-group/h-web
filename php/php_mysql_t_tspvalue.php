<?php
    //前端期望数据为json
    header("Content-Type:application/json");

    $mysqli=@new mysqli('localhost','root','pi!123','dahdb');
    if(mysqli_connect_errno()){
        echo "连接数据库失败：".mysqli_connect_error();
        $mysqli=null;
        exit;
    }

    //$i=60;
    $query='select datetime,value_real from t_tspvalue order by datetime desc limit 60';
    $result = $mysqli->query($query);

    //while($row = $result->fetch_array(MYSQLI_BOTH)){
    while($row = $result->fetch_assoc()){
        //var_dump($row);
        $target_record[]=$row;
    }

    //return to getJSON
    $back =$target_record;
    echo json_encode($back);

    $result->free();
    $mysqli->close();
?>