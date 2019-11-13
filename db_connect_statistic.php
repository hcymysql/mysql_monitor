<?php

    //date_default_timezone_set(PRC);
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

<script language="javascript">
function TestBlack(TagName){
 var obj = document.getElementById(TagName);
 if(obj.style.display=="block"){
  obj.style.display = "none";
 }else{
  obj.style.display = "block";
 }
}
</script>

<script>
function ss(){
var slt=document.getElementById("select");
if(slt.value==""){
        alert("请选择数据库!!!");
        return false;
}
return true;
}
</script>
</head>

<body>
<div class="col-md-10">
<div class="card">
<!--
<div class="card-header bg-light">
   <h1><a href="mysql_status_monitor.php">MySQL 状态监控</a></h1>
</div>
-->      
<div class="card-body">
<div class="table-responsive">

<h3>连接数总和</h3>
<table border='0' width='100%'>
<table style='width:100%;font-size:14px;' class='table table-hover table-condensed'>                                    
<thead>                                   
<tr>                                                                         
<th>连接用户</th>
<th>数量</th>
</tr>
</thead>
<tbody>

<?php
    require 'conn.php';
    $get_info="select user,pwd from mysql_status_info where ip='${ip}' and dbname='${dbname}' and port=${port}";
    $result1 = mysqli_query($con,$get_info);
     list($user,$pwd) = mysqli_fetch_array($result1);
      /*	
      echo $ip."</br>";
      echo $user."</br>";
      echo $pwd."</br>";
      echo $dbname."</br>";
      echo $port."</br>";
      */

     $con2 = mysqli_connect($ip,$user,$pwd,$dbname,$port) or die("数据库链接错误".mysql_error());
     $get_connect_info ='SELECT USER,COUNT(*) FROM `information_schema`.`PROCESSLIST` GROUP BY USER ORDER BY COUNT(*) DESC';
     $result2 = mysqli_query($con2,$get_connect_info);
	while($row = mysqli_fetch_array($result2)) 
     {
		echo "<tr>";
		echo "<td>{$row['0']}</td>";
		echo "<td>{$row['1']}</td>";
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


<div class="col-md-10">
<div class="card">
<!--
<div class="card-header bg-light">
   <h1><a href="mysql_status_monitor.php">MySQL 状态监控</a></h1>
</div>
-->
<div class="card-body">
<div class="table-responsive">

<h3>应用端IP连接数总和</h3>
<table border='0' width='100%'>
<table style='width:100%;font-size:14px;' class='table table-hover table-condensed'>
<thead>
<tr>
<th>连接用户</th>
<th>数据库名</th>
<th>应用端IP</th>
<th>数量</th>
</tr>
</thead>
<tbody>

<?php
     $con3 = mysqli_connect($ip,$user,$pwd,$dbname,$port) or die("数据库链接错误".mysql_error());
     $get_connect_info2 ="SELECT USER,DB,substring_index(HOST,':',1) AS Client_IP,count(1) FROM information_schema.PROCESSLIST GROUP BY USER,DB,substring_index(HOST,':',1) ORDER BY COUNT(1) DESC;";
     $result3 = mysqli_query($con3,$get_connect_info2);
	while($row = mysqli_fetch_array($result3)) 
     {
		echo "<tr>";
		echo "<td>{$row['0']}</td>";
		echo "<td>{$row['1']}</td>";
		echo "<td>{$row['2']}</td>";
  		echo "<td>{$row['3']}</td>";
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



