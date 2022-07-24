<?php

function index($arr1,$arr2,$arr3,$arr4){
    ini_set('date.timezone','Asia/Shanghai');
    /*
    $ip = $_GET['ip'];
    $dbname = $_GET['dbname'];
    $port = $_GET['port'];
    $interval_time = 'DATE_ADD(now(),interval 12 hour)';
    */
   
    $ip = $arr1;
    $dbname = $arr2;
    $port = $arr3;
    $interval_time = $arr4;

    require 'conn.php';
    //$get_info="select create_time,qps_select,qps_insert,qps_update,qps_delete from mysql_status_history where host='${ip}' and dbname='${dbname}' and port=${port} and create_time >=DATE_FORMAT(now(),'%Y-%m-%d')";
    $get_info="select create_time,qps_select,qps_insert,qps_update,qps_delete from mysql_status_history where host='${ip}' and dbname='${dbname}' and port=${port} and  	      create_time >=${interval_time} AND create_time <=NOW()"; 
    $result1 = mysqli_query($con,$get_info);
	//echo $get_info;

  $array= array();
  class Connections{
    public $create_time;
    public $qps_select;
    public $qps_insert;
    public $qps_update;
    public $qps_delete;
  }
  while($row = mysqli_fetch_array($result1,MYSQLI_ASSOC)){
    $cons=new Connections();
    $cons->create_time = $row['create_time'];
    $cons->qps_select = $row['qps_select'];
    $cons->qps_insert = $row['qps_insert'];
    $cons->qps_update = $row['qps_update'];
    $cons->qps_delete = $row['qps_delete'];
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

