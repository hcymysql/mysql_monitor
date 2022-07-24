<?php

function index($arr1,$arr2,$arr3,$arr4){
    ini_set('date.timezone','Asia/Shanghai');
    /*
    $ip = $_GET['ip'];
    $dbname = $_GET['dbname'];
    $port = $_GET['port'];
    */
   
    $ip = $arr1;
    $dbname = $arr2;
    $port = $arr3;
    $interval_time = $arr4;

    require 'conn.php';
    //$get_info="select create_time,qps_select,qps_insert,qps_update,qps_delete from mysql_status_history where host='${ip}' and dbname='${dbname}' and port=${port} and create_time >=DATE_FORMAT(now(),'%Y-%m-%d')";
    $get_info="select create_time,Handler_read_key,Handler_read_rnd_next from mysql_status_history where host='${ip}' and dbname='${dbname}' and port=${port} and  
               create_time >=${interval_time} AND create_time <=NOW()"; 
    $result1 = mysqli_query($con,$get_info);
	//echo $get_info;

  $array=array();
  class UsingIndex{
    public $create_time;
    public $Handler_read_key;
    public $Handler_read_rnd_next;
  }
  while($row = mysqli_fetch_array($result1,MYSQLI_ASSOC)){
    $cons=new UsingIndex();
    $cons->create_time = $row['create_time'];
    $cons->Handler_read_key = $row['Handler_read_key'];
    $cons->Handler_read_rnd_next = $row['Handler_read_rnd_next'];
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
    $interval_time = $_GET['interval_time'];

index($ip,$dbname,$port,$interval_time);


?>

