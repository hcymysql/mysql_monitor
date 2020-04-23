<?php

    date_default_timezone_set(PRC);
    ini_set('date.timezone','Asia/Shanghai');
    /*
    session_start();

    //检测是否登录，若没登录则转向登录界面
    if(!isset($_SESSION['userid'])){
        header("Location:../index.html");
        exit("你还没登录呢。");
    }*/
  
	$ip = $_GET['ip'];
	$dbname = $_GET['dbname'];
	$port = $_GET['port'];

?>

<!doctype html>
<html class="x-admin-sm">
<head>
    <meta http-equiv="Content-Type"  content="text/html;  charset=UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="refresh" content="60" />
    <title>MySQL 连接数详情</title>

<style type="text/css">
a:link { text-decoration: none;color: #3366FF}
a:active { text-decoration:blink;color: green}
a:hover { text-decoration:underline;color: #6600FF}
a:visited { text-decoration: none;color: green}
</style>

    <script type="text/javascript" src="xadmin/js/jquery-3.3.1.min.js"></script>
    <script src="xadmin/lib/layui/layui.js" charset="utf-8"></script>
    <script type="text/javascript" src="xadmin/js/xadmin.js"></script>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/simple-line-icons/css/simple-line-icons.css">
    <link rel="stylesheet" href="./css/font-awesome/css/fontawesome-all.min.css">
    <link rel="stylesheet" href="./css/styles.css">
</head>

<body>

<div class="col-md-10">
<div class="card">
<div class="card-body">
<div class="table-responsive">

<?php
    require 'conn.php';
    $get_info="select user,pwd from mysql_status_info where ip='${ip}' and dbname='${dbname}' and port=${port}";
    $result1 = mysqli_query($con,$get_info);
     list($user,$pwd) = mysqli_fetch_array($result1);
?>

<h3>统计<?php echo $dbname;?>库里每个表的大小</h3>
<table border='0' width='100%'>
<table style='width:100%;font-size:14px;' class='table table-hover table-condensed'>
<thead>
<tr>
<th>表名</th>
<th>存储引擎</th>
<th>数据大小(GB)</th>
<th>索引大小(GB)</th>
<th>总计(GB)</th>
</tr>
</thead>
<tbody>

<?php
     $con_table_info = mysqli_connect($ip,$user,$pwd,$dbname,$port) or die("数据库链接错误". PHP_EOL .mysqli_connect_error());
	mysqli_query($con_table_info,"set sql_mode=''");  
	
     $get_table_info ="SELECT TABLE_NAME,ENGINE,DATA_LENGTH/1024/1024/1024,INDEX_LENGTH/1024/1024/1024,SUM(DATA_LENGTH+INDEX_LENGTH)/1024/1024/1024 AS TOTAL_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$dbname}' GROUP BY TABLE_NAME ORDER BY TOTAL_LENGTH DESC";
	 
     $result_table_info = mysqli_query($con_table_info,$get_table_info);
	while($row = mysqli_fetch_array($result_table_info)) 
     {
		echo "<tr>";
		echo "<td>{$row['0']}</td>";
		echo "<td>{$row['1']}</td>";
		echo "<td>".round($row['2'],3)."</td>";
  		echo "<td>".round($row['3'],3)."</td>";
		echo "<td>".round($row['4'],3)."</td>";
	      echo "</tr>";
      }
	 //end while
echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
?>



