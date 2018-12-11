<?php
//前端期望数据为json
header("Content-Type:application/json;charset=utf-8");
//post 请求 请求内容类型为 application/x-www-form-urlencoded 如果是 application/json 则需要另行处理 $_POST 数组不会被填充


//为了保持模拟的数据
session_start();

//调试关闭，正式生产时打开
/*if ($_SESSION['t_project']) {
    //已生成
} else {*/

    $mysqli=@new mysqli('localhost','root','Szwz!2018','zsiotcloud');
    //$mysqli=@new mysqli('localhost','root','pi!123','zsiotcloud');
    if(mysqli_connect_errno()){
        echo "连接数据库失败：".mysqli_connect_error();
        $mysqli=null;
        exit;
    }

    $query='select * from t_ztvalue order by datetime desc limit 20';
    $query_result = $mysqli->query($query);

    //while($row = $result->fetch_array(MYSQLI_BOTH)){
    $list = [];
    while($row = $query_result->fetch_assoc()){
        //var_dump($row);
        $list[] = [
            "devname"=> $row["devname"],
            "datetime"=>$row["datetime"],
            "ftcode"=>$row["ftcode"],
            "unit"=>$row["unit"],
            "stdref"=>$row["stdref"],
            "samptime"=>$row["samptime"],
            "sampvalue"=>$row["sampvalue"],
            "sampident"=>$row["sampident"],
            "devid"=>$row["devid"]
        ];
        //array_push($list[],$row);
        //$target_record=$row;
    }

    /*echo "var dump list[]";
    var_dump($list);*/

    //分页时需要获取记录总数，键值为 total
    $result["total"] = count($list);

    //根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
    $result["rows"] = array_slice($list, 0, 10);

    echo json_encode($result);
    $mysqli->close();
?>
