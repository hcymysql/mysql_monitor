<?php

function index($arr1,$arr2,$arr3){
    ini_set('date.timezone','Asia/Shanghai');
    /*
    $ip = $_GET['ip'];
    $dbname = $_GET['dbname'];
    $port = $_GET['port'];
    */
   
    $ip = $arr1;
    $dbname = $arr2;
    $port = $arr3;

    require 'conn.php';
    $get_info="select create_time,threads_connected from mysql_status_history where host='${ip}' and dbname='${dbname}' and port=${port} and create_time >=DATE_FORMAT(now(),'%Y-%m-%d')";
    $result1 = mysqli_query($con,$get_info);
	//echo $get_info;

  $array= array();
  class Connections{
    public $create_time;
    public $threads_connected;
  }
  while($row = mysqli_fetch_array($result1,MYSQL_ASSOC)){
    $cons=new Connections();
    $cons->create_time = $row['create_time'];
    //$user->user_max = $row['user_max'];
    $cons->threads_connected = $row['threads_connected'];
    $array[]=$cons;
  }
  $top_data=json_encode($array);
  // echo "{".'"user"'.":".$data."}";
 echo $top_data;
}

/*$fn = isset($_GET['fn']) ? $_GET['fn'] : 'main';
if (function_exists($fn)) {
  call_user_func($fn);
}
*/

    $ip = $_GET['ip'];
    $dbname = $_GET['dbname'];
    $port = $_GET['port'];

index($ip,$dbname,$port);


?>

