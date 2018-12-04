<?php

require 'php/vendor/autoload.php';

use Medoo\Medoo;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use Firebase\JWT\JWT;

$app = new Slim\App();

$database = new Medoo([
    'database_type' => 'pgsql',
    'database_name' => 'thingsboard',
    'server' => '127.0.0.1',
    'port' => '5432',
    'username' => 'postgres',
    'password' => 'Shenhuan!2018'
]);

function fetchdata($database){
    return $database->select("t_project",["projectcode", "projectname","district", 
                                          "street", "longitude", "latitude",
                                          "contractors","prjmanager","telephone",
                                          "address"]);
}

function get_table_columns($database, $table_name) {
    $sql = "select column_name from information_schema.columns where table_schema='public' and table_name='";
    $sql = $sql . $table_name . "'";
    $data = $database->query($sql)->fetchAll();
    return array_column($data, 'column_name');
}

function get_table_data($database, $table_name, $param) {
    $limit = 100;
    $offset = 0;
    if(isset($param)) {
        if(isset($param['limit'])) {
            $limit = $param['limit'];
        }
        if(isset($param['offset'])) {
            $offset = $param['offset'];            
        }
    }

    $data = $database->select($table_name, "*" ,['LIMIT' => [$offset, $limit],]);

    return $data;
}

$telemetry_items = array('devname','last_tspvalue','last_pm25value','last_tempvalue','last_humidvalue', 'last_noisevalue','last_windspeed','last_winddirection');
$attrib_items = array('projectcode','projectname','district','street','longitude','latitude','contractors','prjmanager','telephone', 'address');

function fetch_devices_last_telemetry($database, $devnames) {
    global $telemetry_items;
    $data = $database->select("t_cu_upload",$telemetry_items ,["devname"=>$devnames]);
    return $data;
}

function fetch_t_projects($database, $prjcodes) {
    global $attrib_items;
    $attributes = $database->select("t_project",$attrib_items ,["projectcode"=>$prjcodes]);
    return $attributes;
}

function fetch_user_devices($database, $user_id) {
    $foreign_key = $database->select("app_user",["foreign_user_id"],["user_id"=>$user_id]);
    $customer = $database->select("tb_user",["customer_id", "authority", "tenant_id"],["id"=>$foreign_key[0]]);

    $result = array();
    if(empty($customer)) {
        echo $foreign_key;
        return $result;
    }

    if ($customer[0]['authority'] == "TENANT_ADMIN") {
       $devices = $database->select("device",["id", "name"],["AND" => ["tenant_id"=>$customer[0]['tenant_id'], "name[~]"=>"YC"]]);
    } else if($customer[0]['authority'] == "CUSTOMER_USER") {
       $devices = $database->select("device",["id", "name"],["AND" => ["customer_id"=>$customer[0]['customer_id'], "name[~]"=>"YC"]]);
    }
    return $devices;
}

function fetch_tb_user_device_info($database, $user_id){
    $devices = fetch_user_devices($database, $user_id);
    $entity_ids = array_column($devices,'id');

    $tbgw_inventories = $database->select("t_tbgw_inventory",["devname","entity_id","projectcode"],["AND"=>["entity_id"=>$entity_ids, "projectcode[!]"=>null]]);
    $devnames   = array_column($tbgw_inventories, 'devname');
    $entity_ids = array_column($tbgw_inventories, 'entity_id');
    $prjcodes   = array_column($tbgw_inventories, 'projectcode');

	$attributes = fetch_t_projects($database, $prjcodes);
    $attrib_prjs = array_column($attributes, 'projectcode');

    $telemetry = fetch_devices_last_telemetry($database, $devnames);
    $telemetry_devnames = array_column($telemetry, 'devname');

    global $attrib_items;
    global $telemetry_items;
    foreach($tbgw_inventories as $tbgw) {
       $entity_id  = $tbgw['entity_id'];
       $prjcode    = $tbgw['projectcode'];
	   $devname    = $tbgw['devname'];
       $telemetry_key = array_search($devname, $telemetry_devnames);
       $attrib_key = array_search($prjcode, $attrib_prjs);

       if(FALSE !== $attrib_key) {
          $result[$entity_id] = array();
          foreach($attrib_items as $item) {
             $result[$entity_id][$item] = $attributes[$attrib_key][$item];
          }

          if(FALSE !== $telemetry_key) {
             foreach($telemetry_items as $item) {
                $result[$entity_id][$item] = $telemetry[$telemetry_key][$item];
             }
          }
          if(empty($result[$entity_id]['devname'])) { $result[$entity_id]['devname'] = $devname; }
       }
    }
   
    foreach ($result as $key=>$value) {
       if (!array_key_exists('latitude', $value) || !array_key_exists('longitude', $value) || empty($value['latitude'])|| empty($value['longitude']))
          unset($result[$key]);
    }

    return array_values($result);
}

function get_device_attribute_value(&$result,$entity_id, $key, $item) {
    $attrib = array('imageUrl','address','district',
                 'superintendent','scp','contractors','telephone','latitude','longitude');

    if(in_array($key, $attrib)){
       if(!empty($item['str_v'])) {
          $result[$entity_id][$key] = $item['str_v'];
       }else if(!empty($item['dbl_v'])) {
          $result[$entity_id][$key] = $item['dbl_v'];
       }
    }
}

function fetch_device_telemetry_timeseries($database, $devname, $param) {
    $end  = time();
    $begin = $end - 60*30; 
    if(isset($param)) {
        if(isset($param['end'])) {
            $end = $param['end'];
            $begin = $end - 60*30;
        }
        if(isset($param['begin']) && $param['being'] < $end) {
            $begin = $param['begin'];            
        }
        if(isset($param['before'])) {
            $end = $param['before'];
            $begin = $end - 60*30;
            if(isset($param['length'])) {
               $begin = $end - $param['length'];
            }
        }
    }

    $data = $database->select("t_tspvalue", ["devname", "value_real", "datetime"], 
            ["AND" =>["devname" =>$devname, "datetime[<>]" => [date('Y-m-d H:i:s',$begin), date('Y-m-d H:i:s',$end)]],"ORDER" => ["datetime" => "ASC"]]);

    return $data;
}

function fetch_user($database){
    return $database->select("app_user", "*");
}

function get_users_by_name($database, $name){
    return $database->select("app_user", "*", ["user_name"=>$name]);
}

function get_token_by_userid($database, $userid){
    return $database->select("app_login_token", "*", ["AND" => ["user_id"=>$userid, "date_expiration[>]"=>time()]]);
}

function set_token_by_userid($database, $userid, $token, $date_created, $date_expiration){
    return $database->insert("app_login_token", ["user_id"=>$userid, "token"=>$token,
                                                 "date_created"=>$date_created,
                                                 "date_expiration"=>$date_expiration ]);
}

// Authenticate route.
$app->post('/authenticate', function (Request $request, Response $response) use ($database) {
    $data = $request->getParsedBody();
    //$result = '[{"user_login":"tenant", "user_pwd":"tenant"}]'; //file_get_contents('./users.json');
    //$users = fetch_user($database);//json_decode($result, true);
    $login = $data['user_login'];
    $password = $data['user_password'];
    $users = get_users_by_name($database, $login);
    foreach ($users as $key => $user) {
        if ($user['user_name'] == $login && $user['password'] == $password) {
            $current_user = $user;
        }
    }

    if (!isset($current_user)) {
        echo json_encode($users);
    } else {
        // Find a corresponding token.
        //$sql = "SELECT * FROM tokens
        //    WHERE user_id = :user_id AND date_expiration >" . time();
        $token_from_db = get_token_by_userid($database,$current_user['user_id']);
        if (count($current_user) != 0 && count($token_from_db) != 0 ) {
           echo json_encode([
                "token"      => $token_from_db[0]['token'],
                "user_id" => $current_user['user_id']
                ]);
        }
        // Create a new token if a user is found but not a token corresponding to whom.
        else if (count($current_user) != 0 && !$token_from_db) {
            $key = "your_secret_key";
            $payload = array(
                "iss"     => "http://www.tb.com",
                "iat"     => time(),
                "exp"     => time() + (3600 * 24 * 15),
                "context" => [
                    "user" => [
                        "user_login" => $current_user['user_name'],
                        "user_id"    => $current_user['user_id']
                    ]
                ]
            );
            try {
                $jwt = JWT::encode($payload, $key);
            } catch (Exception $e) {
                echo json_encode($e);
            }
            //$sql = "INSERT INTO tokens (user_id, value, date_created, date_expiration)
            //    VALUES (:user_id, :value, :date_created, :date_expiration)";
          
            $pdo = set_token_by_userid($database, $current_user['user_id'], $jwt,
                                           $payload['iat'], $payload['exp']);
            if($pdo->rowCount() == 0) {
                echo json_encode([
                    "token"      => $jwt,
                    "id" => $current_user['user_id']
                ]);
            } else {
                echo json_encode($pdo);
                echo '{"error":{"text":' . $database->error() . '}}';
            }
        }
    }
});

$mw1 = function ($request, $response, $next) {
    $jwt = $request->getHeaders();
    $key = "your_secret_key";
    $token = isset($jwt['HTTP_AUTHORIZATION'][0]) ? $jwt['HTTP_AUTHORIZATION'][0] : '';    
    //echo json_encode($jwt);
    $res['result'] = 'success';
    $valid  = false;

    if (empty($token)) {
       $res['result'] = 'no jwt token';
    } else {
       try {
           $decoded = JWT::decode($token, $key, array('HS256'));
           if ($decoded->exp < time()) {
              $res['result'] = 'token expire';
           } else {
              $_SESSION['user_name'] = $decoded->context->user->user_login;
              $_SESSION['user_id'] = $decoded->context->user->user_id;
              $valid = true;
           }
       } catch (UnexpectedValueException $e) {
           $res['result'] = 'token decoded error';
           echo ($e);
       }
    }

    if ($valid) {
        $response = $next($request, $response);
    }else {
        echo json_encode($res);
        $response = $response->withStatus(405);
    }
    return $response;
};

// The route to get a secured data.
$app->get('/restricted', function (Request $request, Response $response) {
    $jwt = $request->getHeaders();
    $key = "your_secret_key";
    try {
        $decoded = JWT::decode($jwt['HTTP_AUTHORIZATION'][0], $key, array('HS256'));
    } catch (UnexpectedValueException $e) {
        echo $e->getMessage();
    }
    if (isset($decoded)) {
        $sql = "SELECT * FROM tokens WHERE user_id = :user_id";
        try {
            $db = $this->db;
            $stmt = $db->prepare($sql);
            $stmt->bindParam("user_id", $decoded->context->user->user_id);
            $stmt->execute();
            $user_from_db = $stmt->fetchObject();
            $db = null;
            if (isset($user_from_db->user_id)) {
                echo json_encode([
                    "response" => "This is your secure resource !"
                ]);
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    }
});

$app->get('/aa', function ($request, $response, $args) use ($database) {
    $devics = fetch_tb_user_device_info($database, $_SESSION['user_id']);
    echo json_encode($devics);
})->add($mw1);

$app->get('/hello/{name}', function ($request, $response, $args) use ($database) {
    $data = $request->getQueryParams();
    echo json_encode($data);
    return $response->write("Hello, " );
});

$app->get('/table_columns/{table_name}', function ($request, $response, $args) use ($database) {
    $table_name = $args['table_name'];
    $data = get_table_columns($database, $table_name);
    echo json_encode($data);
});

$app->get('/table_data/{table_name}', function ($request, $response, $args) use ($database) {
    $table_name = $args['table_name'];
    $param = $request->getQueryParams();
    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
    $data = get_table_data($database, $table_name, $param);
    $result['total'] = count($data);
    $result['rows'] = $data;
    return $response->write(json_encode($result));
});

$app->get('/telemetry/{devname}', function ($request, $response, $args) use ($database) {
    $devname = $args['devname'];
    $param = $request->getQueryParams();
    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
    $data = fetch_device_telemetry_timeseries($database, $devname, $param);
    return $response->write(json_encode($data));
});

$app->get('/device/{entity_id}', function ($request, $response, $args) use ($database) {
    $param = $request->getQueryParams();
    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
    $data = 'not support'; //fetch_device_telemetry($database, $param);
    return $response->write(json_encode($data));
});

$app->post('/devices/last_telemetry', function ($request, $response, $args) use ($database) {
    $devices = $request->getParsedBody();
    $response = $response->withStatus(200)->withHeader('Content-type', 'application/json');
    $data = fetch_devices_last_telemetry($database, $devices);
    return $response->write(json_encode($data));
});

$app->get('/getProjectInfo', function ($request, $response, $args)use ($database) {
	//$data = fetchdata($database);
        $data = fetch_tb_user_device_info($database, $_SESSION['user_id']);
	echo json_encode($data);
})->add($mw1);

$app->run();
