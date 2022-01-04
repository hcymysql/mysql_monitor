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
    <title>MySQL 信息统计</title>

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

<div class="col-md-05">
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
<th>主键自增字段</th>
<th>主键字段属性</th>
<th>主键自增当前值</th>
<th>主键自增值剩余</th>
</tr>
</thead>

<tbody>
<?php
     $con_table_info = mysqli_connect($ip,$user,$pwd,$dbname,$port) or die("数据库链接错误". PHP_EOL .mysqli_connect_error());
	mysqli_query($con_table_info,"set sql_mode=''");  
	
     #$get_table_info ="SELECT TABLE_NAME,ENGINE,DATA_LENGTH/1024/1024/1024,INDEX_LENGTH/1024/1024/1024,SUM(DATA_LENGTH+INDEX_LENGTH)/1024/1024/1024 AS TOTAL_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$dbname}' GROUP BY TABLE_NAME ORDER BY TOTAL_LENGTH DESC";
	 
	$get_table_info ="SELECT t.TABLE_NAME as TABLE_NAME,t.ENGINE as ENGINE,t.DATA_LENGTH/1024/1024/1024 as DATA_LENGTH,t.INDEX_LENGTH/1024/1024/1024 as INDEX_LENGTH,SUM(t.DATA_LENGTH+t.INDEX_LENGTH)/1024/1024/1024 AS TOTAL_LENGTH,c.column_name,c.data_type,c.COLUMN_TYPE,t.AUTO_INCREMENT,locate('unsigned',c.COLUMN_TYPE) = 0 AS IS_SIGNED 
    FROM information_schema.TABLES t JOIN
    (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$dbname}' AND extra='auto_increment'
    ) c ON t.table_name=c.table_name GROUP BY TABLE_NAME ORDER BY TOTAL_LENGTH DESC,AUTO_INCREMENT DESC LIMIT 10"; 
	 
     $result_table_info = mysqli_query($con_table_info,$get_table_info);
	while($row = mysqli_fetch_array($result_table_info)) 
     {
		echo "<tr>";
		echo "<td>{$row['TABLE_NAME']}</td>";
		echo "<td>{$row['ENGINE']}</td>";
		echo "<td>".round($row['DATA_LENGTH'],3)."</td>";
  		echo "<td>".round($row['INDEX_LENGTH'],3)."</td>";
		echo "<td>".round($row['TOTAL_LENGTH'],3)."</td>";
		echo "<td>{$row['COLUMN_NAME']}</td>";
		echo "<td>{$row['COLUMN_TYPE']}</td>";
		echo "<td>{$row['AUTO_INCREMENT']}</td>";
		
	      if($row['DATA_TYPE'] == 'int'){
			if ($row['IS_SIGNED'] == 0){
				echo "<td>". (4294967295-$row['AUTO_INCREMENT']) ."</td>";
			}
			if ($row['IS_SIGNED'] == 1){
				echo "<td>". (2147483647-$row['AUTO_INCREMENT']) ."</td>";
			}			
		}
	
	      if($row['DATA_TYPE'] == 'bigint'){
			if ($row['IS_SIGNED'] == 0){
				echo "<td>". number_format((18446744073709551615-$row['AUTO_INCREMENT']),0, '', '') ."</td>";
			}
			if ($row['IS_SIGNED'] == 1){
				echo "<td>". (9223372036854775807-$row['AUTO_INCREMENT']) ."</td>";
			}			
		}
		
	      echo "</tr>";
      }
	 //end while
echo "</tbody>";
echo "</table>";
?>

<!----------------------------------------------------------------------------------------->
<br>
<hr style="height:1px;border:none;border-top:1px dashed #0066CC;" />
<h3>统计<?php echo $dbname;?>库里执行次数最频繁的前10条SQL语句</h3>
<table border='0' width='100%'>
<table style='width:100%;font-size:14px;' class='table table-hover table-condensed'>
<thead>
<tr>
<th>执行语句</th>
<th>数据库名</th>
<th>最近执行时间</th>
<th>SQL执行总次数</th>
</tr>
</thead>

<tbody>
<?php         
	$con_top10 = mysqli_connect($ip,$user,$pwd,$dbname,$port) or die("数据库链接错误". PHP_EOL .mysqli_connect_error());	
	$check_performance_schema=mysqli_fetch_row(mysqli_query($con_top10,"select @@performance_schema"));
	if($check_performance_schema[0]==0){
		echo "<font size='3' color='#DC143C'>performance_schema参数未开启。</font>"."<br>";
		echo "<font size='3' color='#DC143C'>在my.cnf配置文件里添加performance_schema=1，并重启mysqld进程生效。</font>"."<br>";
		die;
	}
	
	$version=mysqli_fetch_row(mysqli_query($con_top10,"select version()"));
	if(preg_match("/5.7|8.0|10.6/",$version[0])){
            //echo "MySQL的版本是$version[0]"."<br>";
		mysqli_query($con_top10,"SET @sys.statement_truncate_len = 1024");
		$Top_10_info=mysqli_query($con_top10,"select query,db,last_seen,exec_count from sys.statement_analysis order by exec_count desc, last_seen desc limit 10");
		while($row_Top10 = mysqli_fetch_array($Top_10_info)) 
		{
			echo "<tr>";
			echo "<td>{$row_Top10['query']}</td>";
			echo "<td>{$row_Top10['db']}</td>";
			echo "<td>{$row_Top10['last_seen']}</td>";
			echo "<td>{$row_Top10['exec_count']}</td>";
			echo "</tr>";
                } 
	//end while		
        }else{
		$Top_10_info=mysqli_query($con_top10,"SELECT DIGEST_TEXT,SCHEMA_NAME,LAST_SEEN,COUNT_STAR FROM performance_schema.events_statements_summary_by_digest ORDER BY COUNT_STAR DESC");	
		while($row_Top10 = mysqli_fetch_array($Top_10_info)) 
		{
			echo "<tr>";
			echo "<td>{$row_Top10['DIGEST_TEXT']}</td>";
			echo "<td>{$row_Top10['SCHEMA_NAME']}</td>";
			echo "<td>{$row_Top10['LAST_SEEN']}</td>";
			echo "<td>{$row_Top10['COUNT_STAR']}</td>";
			echo "</tr>";
                } 
		//echo "<font size='3' color='#DC143C'>检测到当前数据库版本 $version[0] 不支持sys库特性！</font>"."<br>";
	}
echo "</tbody>";
echo "</table>";
?> 
<!----------------------------------------------------------------------------------------->
<br>
<hr style="height:1px;border:none;border-top:1px dashed #0066CC;" />
<h3>统计<?php echo $dbname;?>库里访问次数最多的前10张表</h3>
<table border='0' width='100%'>
<table style='width:100%;font-size:14px;' class='table table-hover table-condensed'>
<thead>
<tr>
<th>表文件名</th>
<th>总共读取次数</th>
<th>总共读取数据量</th>
<th>总共写入次数</th>
<th>总共写入数据量</th>
<th>总共读写数据量</th>
</tr>
</thead>

<tbody>
<?php         
	$con_top10 = mysqli_connect($ip,$user,$pwd,$dbname,$port) or die("数据库链接错误". PHP_EOL .mysqli_connect_error());	
	if($check_performance_schema[0]==0){
		echo "<font size='3' color='#DC143C'>performance_schema参数未开启。</font>"."<br>";
		echo "<font size='3' color='#DC143C'>在my.cnf配置文件里添加performance_schema=1，并重启mysqld进程生效。</font>"."<br>";
		die;
	}
	
	$version=mysqli_fetch_row(mysqli_query($con_top10,"select version()"));
	if(preg_match("/5.7|8.0|10.6/",$version[0])){
            //echo "MySQL的版本是$version[0]"."<br>";
	      //mysqli_query($con_top10,"SET @sys.statement_truncate_len = 1024");
		$Top_10_ioinfo=mysqli_query($con_top10,"select file,count_read,total_read,count_write,total_written,total from sys.io_global_by_file_by_bytes limit 10");
		while($row_Top10io = mysqli_fetch_array($Top_10_ioinfo)) 
		{
			echo "<tr>";
			echo "<td>{$row_Top10io['file']}</td>";
			echo "<td>{$row_Top10io['count_read']}</td>";
			echo "<td>{$row_Top10io['total_read']}</td>";
			echo "<td>{$row_Top10io['count_write']}</td>";
			echo "<td>{$row_Top10io['total_written']}</td>";
			echo "<td>{$row_Top10io['total']}</td>";
			echo "</tr>";
                } 
	//end while		
        }else{
		$Top_10_ioinfo=mysqli_query($con_top10,"select substring_index(FILE_NAME,'/',-2) AS file,COUNT_READ AS count_read,concat(round(SUM_NUMBER_OF_BYTES_READ/1024/1024/1024,2), ' GB') AS total_read,COUNT_WRITE AS count_write,concat(round(SUM_NUMBER_OF_BYTES_WRITE/1024/1024/1024,2), ' GB') AS total_written,concat(round((SUM_NUMBER_OF_BYTES_READ+SUM_NUMBER_OF_BYTES_WRITE)/1024/1024/1024,2), ' GB') AS total from performance_schema.file_summary_by_instance ORDER BY  (COUNT_READ+COUNT_WRITE) DESC limit 10");	
		while($row_Top10io = mysqli_fetch_array($Top_10_ioinfo)) 
		{
			echo "<tr>";
			echo "<td>{$row_Top10io['file']}</td>";
			echo "<td>{$row_Top10io['count_read']}</td>";
			echo "<td>{$row_Top10io['total_read']}</td>";
			echo "<td>{$row_Top10io['count_write']}</td>";
			echo "<td>{$row_Top10io['total_written']}</td>";
			echo "<td>{$row_Top10io['total']}</td>";
			echo "</tr>";
                } 
		//echo "<font size='3' color='#DC143C'>检测到当前数据库版本 $version[0] 不支持sys库特性！</font>"."<br>";
	}
echo "</tbody>";
echo "</table>";
?> 
</div>
</div>
</div>
</div>




