<?php
error_reporting(E_USER_WARNING | E_USER_NOTICE);

require 'con.php';
//mysqli_query($con,"truncate mysql_status");
$result1 = mysqli_query($con,"select ip,dbname,user,pwd,port from mysql_status_info");

$r=$re=array();

/*
$sqls= array(
	"select * from information_schema.GLOBAL_STATUS where VARIABLE_NAME regexp 'com_select$|com_insert$|com_update$|com_delete$|Threads_connected|\^uptime$'",
	"select * from information_schema.GLOBAL_STATUS where VARIABLE_NAME regexp 'com_select$|com_insert$|com_update$|com_delete$|Threads_connected|\^uptime$'",
	"select * from information_schema.GLOBAL_VARIABLES where VARIABLE_NAME regexp '^max_connections|^version$'",
	"show slave status"
      );
*/

// 兼容MySQL 8.0
$sqls=array(
	"SHOW GLOBAL STATUS WHERE VARIABLE_NAME REGEXP 'com_select$|com_insert$|com_update$|com_delete$|Threads_connected|^uptime$'",
	"SHOW GLOBAL STATUS WHERE VARIABLE_NAME REGEXP 'com_select$|com_insert$|com_update$|com_delete$|Threads_connected|^uptime$'",
	"SHOW GLOBAL VARIABLES WHERE VARIABLE_NAME REGEXP '^max_connections|^version$'",
	"SHOW SLAVE STATUS"
      );

while( list($ip,$dbname,$user,$pwd,$port) = mysqli_fetch_array($result1))
{		
$all_links = array();

foreach ($sqls as $sql) { 
	$link1 = mysqli_init();
        $link1->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
	if(! $link1->real_connect($ip,$user,$pwd,$dbname,$port)){
		$connect_error='down';
		break;
	}

	if(preg_match('/GLOBAL STATUS/',$sql)){ //等待1秒，得到QPS数值
		sleep(1);
	}
	if(! $link1->query($sql, MYSQLI_ASYNC)){
		break;
	}
	$all_links[]=$link1;
}

$processed = 0;
do {
    $links = $errors = $reject = array();
    foreach ($all_links as $link) {
        $links[] = $errors[] = $reject[] = $link;
    }
    if (!mysqli_poll($links, $errors, $reject, 1)) {
        continue;
    }
    foreach ($links as $link) {
        if ($result = $link->reap_async_query()) {
           while($row = $result->fetch_row()){
		$r[$row[0]] = $row[1];
	   }
	    array_push($re,$r);
	    //array_splice($r, 0, count($r));
            if (is_object($result)){
                mysqli_free_result($result);
	    }
        } else die(sprintf("MySQLi Error: %s", mysqli_error($link)));
        $processed++;
    }
} while ($processed < count($all_links));
	echo "---------------------------"."\n";
	$role=empty($re[3]['Waiting for master to send event'])?1:0;
	//unset($re[3]['Waiting for master to send event']);
	echo "角色是:".$role."\n";
	$is_live=isset($connect_error)?0:1;
	print_r($re);
	
	$QPS_SELECT=$re[3]['Com_select'] - $re[0]['Com_select'];
	$QPS_INSERT=$re[3]['Com_insert'] - $re[0]['Com_insert'];
	$QPS_UPDATE=$re[3]['Com_update'] - $re[0]['Com_update'];
	$QPS_DELETE=$re[3]['Com_delete'] - $re[0]['Com_delete'];
	
	
	echo '每秒查询：'.$QPS_SELECT."\n";
	echo '每秒插入：'.$QPS_INSERT."\n";
	echo '每秒更新：'.$QPS_UPDATE."\n";	
	echo '每秒删除：'.$QPS_DELETE."\n";
	echo '当前连接数：'.$re[1]['Threads_connected']."\n";

	//require 'con.php';

	if($is_live==0){
	    echo "$ip"."\n";
            echo $connect_error."\n";
	    unset($connect_error);
	    $sql = "INSERT INTO mysql_status(host,dbname,port,role,is_live,create_time)  VALUES('{$ip}','{$dbname}','{$port}','{$role}',{$is_live},now())"; 
	} else {
	    echo $ip." ok"."\n";
            echo $is_live."\n";
            $sql = "INSERT INTO mysql_status(host,dbname,port,role,is_live,max_connections,threads_connected,qps_select,qps_insert,qps_update,qps_delete,runtime,db_version,create_time) VALUES('{$ip}','{$dbname}','{$port}','{$role}',{$is_live},'{$re[2]['max_connections']}',{$re[1]['Threads_connected']},$QPS_SELECT,$QPS_INSERT,$QPS_UPDATE,$QPS_DELETE,round({$re[1]['Uptime']}/3600/24,1),'{$re[3]['version']}',now())";
	}    

    if (mysqli_query($con, $sql)) {
        echo "{$ip}:'{$dbname}':'{$port}'新记录插入成功\n";
	mysqli_query($con,"insert into mysql_status_history select * from mysql_status");
	mysqli_query($con,"delete from mysql_status where host='{$ip}' and dbname='{$dbname}' and create_time<now()");
    } else {
        echo "Error: " . $sql . "\n" . mysqli_error($con);
    }	

	array_splice($re, 0, count($re));
	array_splice($r, 0, count($r));
}

?>

