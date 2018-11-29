<?php

/*
 * Following code will list all the products
 */

// array for JSON response
$response = array();

// include db connect class
require_once __DIR__ . '/db_connect.php';

// connecting to db
$db = new DB_CONNECT();

// 查询主场进球及主场失球数据
$result = mysql_query("SELECT home_team, sum(score_home) as score_h, sum(score_visiting) as score_v FROM fbscore group by home_team") or die(mysql_error());
$bln = array();
$bln['name'] = 'team name';
$rows['name'] = 'home score';
$rows2['name'] = 'visiting score';

// check for empty result
if (mysql_num_rows($result) > 0) {

    while ($r = mysql_fetch_array($result)) {
        // temp user array
        //$array = $row["score_home"];
        array_push($array, $row[score_home]);
        $bln['data'][] = $r['home_team'];
        $rows['data'][] = $r['score_h'];
        $rows2['data'][] = $r['score_v'];
    }

    $rslt = array();
    array_push($rslt, $bln);
    array_push($rslt, $rows);
    array_push($rslt, $rows2);

    // echoing JSON response
    echo $_GET['callback']. '('. json_encode($rslt, JSON_NUMERIC_CHECK) . ')';
    //print json_encode($rslt, JSON_NUMERIC_CHECK);
} else {
    echo "error!";
}