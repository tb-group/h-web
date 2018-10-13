<?php
header("Content-type:text/html;charset=utf-8");

ignore_user_abort();
set_time_limit(0);
date_default_timezone_set('PRC');

$host = "host=47.98.151.18";
$port = "port=5432";
$dbname = "dbname=thingsboard";
$credentials = "user=postgres password=Shenhuan!2018";
$db = pg_connect("$host $port $dbname $credentials");
if(!$db){
    echo "Error : Unable to open database\n";
    die('Could not connect to database.');
}

$query_str_t_project = "SELECT * FROM t_project";
$result_t_project = pg_query($db,$query_str_t_project);
if(!$result_t_project)
{
    echo pg_last_error($db);
    exit;
}
echo "hello world\n";
while($row_t_project = pg_fetch_row($result_t_project))
{
	   //var_dump($row_t_project);
}
pg_close($db);
?>
