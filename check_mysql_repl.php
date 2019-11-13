<?php
error_reporting(E_USER_WARNING | E_USER_NOTICE);

     require 'conn.php';
     mysqli_query($con,"truncate mysql_repl_status");
     $result1 = mysqli_query($con,"select ip,dbname,user,pwd,port from mysql_status_info");
	 
	$r=$re=array();
	 
	$sqls= array(
		"SHOW SLAVE STATUS",
		"SHOW GLOBAL VARIABLES WHERE variable_name REGEXP 'server_id$|^read_only'"
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
    //print_r($re);

	$role=empty($re[0])?1:0;
	$is_live=isset($connect_error)?0:1;
	
	if($is_live==0){
	    echo "$ip"."\n";
            echo $connect_error."\n";
	    	unset($connect_error);
	    $sql = "INSERT INTO mysql_repl_status(host,dbname,port,role,is_live) VALUES('{$ip}','{$dbname}','{$port}','{$role}','{$is_live}')";	
	} else {
	    $sql = "INSERT INTO mysql_repl_status(server_id,host,dbname,port,role,is_live,read_only,gtid_mode,Master_Host,Master_Port,Slave_IO_Running,Slave_SQL_Running,Seconds_Behind_Master,Master_Log_File,Relay_Master_Log_File,Read_Master_Log_Pos,Exec_Master_Log_Pos,Last_IO_Error,Last_SQL_Error,create_time) values('{$re[1]['server_id']}','{$ip}','{$dbname}','{$port}','{$role}','{$is_live}','{$re[2]['read_only_value']}','{$re[0]['Using_Gtid']}','{$re[0]['Master_Host']}','{$re[0]['Master_Port']}','{$re[0]['Slave_IO_Running']}','{$re[0]['Slave_SQL_Running']}','{$re[0]['Seconds_Behind_Master']}','{$re[0]['Master_Log_File']}','{$re[0]['Relay_Master_Log_File']}','{$re[0]['Read_Master_Log_Pos']}','{$re[0]['Exec_Master_Log_Pos']}','{$re[0]['Last_IO_Error']}','{$re[0]['Last_SQL_Error']}',now())";
	}
	
        if (mysqli_query($con, $sql)) {
            echo "{$ip}:'{$dbname}':'{$port}:{$pwd}'新记录插入成功\n";
        } else {
            echo "Error: " . $sql . "\n" . mysqli_error($con);
        }
}
?>

