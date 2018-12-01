<?php
    //前端期望数据为json
    header("Content-Type:application/json;charset=utf-8");
    //post 请求 请求内容类型为 application/x-www-form-urlencoded 如果是 application/json 则需要另行处理 $_POST 数组不会被填充

    //$mysqli=@new mysqli('localhost','root','bxxhbxxh','dahdb');
    $mysqli=@new mysqli('localhost','root','pi!123','dahdb');
    if(mysqli_connect_errno()){
        echo "连接数据库失败：".mysqli_connect_error();
        $mysqli=null;
        exit;
    }

    $query='select * from t_codcrvalue order by datetime desc limit 1';
    $result = $mysqli->query($query);

    //while($row = $result->fetch_array(MYSQLI_BOTH)){
    while($row = $result->fetch_assoc()){
        //var_dump($row);
        //echo $row['datetime']." codcr = ".$row['codcr'].PHP_EOL;
        $target_record=$row;
    }

    //return to ajax
    //var_dump($target_record);
    //echo json_encode($target_record);
    //e.g.
    //[{"datetime":"2018-11-29 20:44:21","codcr":"28","tp":"31","tn":"85","nh3n":"98"}]

    //return to getJSON
    $back =$target_record;
    echo json_encode($back);

    $result->free();
    $mysqli->close();
?>
