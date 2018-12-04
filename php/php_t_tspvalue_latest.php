<?php
//前端期望数据为json
header("Content-Type:application/json;charset=utf-8");
//post 请求 请求内容类型为 application/x-www-form-urlencoded 如果是 application/json 则需要另行处理 $_POST 数组不会被填充


//为了保持模拟的数据
session_start();

//调试关闭，正式生产时打开
/*if ($_SESSION['t_tspvalue']) {
    //已生成
} else {*/
    $host = "host=47.98.151.18";
    //$host = "host=101.132.70.173";
    $port = "port=5432";
    $dbname = "dbname=thingsboard";
    $credentials = "user=postgres password=Shenhuan!2018";
    $db = pg_connect("$host $port $dbname $credentials");
    if(!$db){
        echo "Error : Unable to open database\n";
        die('Could not connect to database.');
    }

    //$query_str_t_tspvalue = "SELECT datetime,value_real FROM t_tspvalue where devname = 'TBGW_SHKJ_YC109' limit 1000";
    $query_str_t_tspvalue = "SELECT datetime,value_real FROM t_tspvalue where devname = 'TBGW_SHKJ_YC109' order by datetime desc limit 1";
    //$query_str_t_tspvalue = "SELECT * from t_sms_record";
    $result_t_tspvalue = pg_query($db,$query_str_t_tspvalue);
    if(!$result_t_tspvalue)
    {
        echo pg_last_error($db);
        exit;
    }

    $list = [];
    while($row_t_tspvalue = pg_fetch_row($result_t_tspvalue))
    {
        //var_dump($row_t_tspvalue);
        $list[] = [
            $row_t_tspvalue[0],
            $row_t_tspvalue[1],
        ];


    }

    //echo "var dump list[]";
    //var_dump($list);

    $_SESSION['t_tspvalue'] = $list;

//}

//var_dump($_SESSION['t_tspvalue']);

/*$list_temp = [];
//检索
if (isset($_POST['search']) && !empty($_POST['search'])) {
    foreach ($_SESSION['t_tspvalue'] as $key => $row) {
        if (strpos($row['projectcode'], $_POST['search']) !== false
            || strpos($row['projectname'], $_POST['search']) !== false) {
            $list_temp[] = $_SESSION['t_tspvalue'][$key];
        }
    }
} else {
    $list_temp = $_SESSION['t_tspvalue'];
}
//排序
if (isset($_POST['sort'])) {
    $temp = [];
    foreach ($list_temp as $row) {
        $temp[] = $row[$_POST['sort']];
    }
    //php的多维排序
    array_multisort($temp,
        $_POST['sort'] == 'projectcode' ? SORT_STRING : SORT_NUMERIC,
        $_POST['order'] == 'asc' ? SORT_ASC : SORT_DESC,
        $list_temp
    );
}*/

$list_temp = $_SESSION['t_tspvalue'];

//分页时需要获取记录总数，键值为 total
$result["total"] = count($list_temp);

//var_dump($_SESSION['t_tspvalue']);

//根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
//$result["rows"] = array_slice($list_temp, 0, 10);

echo json_encode($list);

pg_close($db);
?>
