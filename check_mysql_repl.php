<?php
error_reporting(E_USER_WARNING | E_USER_NOTICE);
header("Content-type:text/html;charset=utf-8;");
ini_set('date.timezone','Asia/Shanghai');

require 'conn.php';
include 'convert_array.php';
include 'mail/mail.php';
include 'weixin/weixin.php';
	 
     //mysqli_query($con,"truncate mysql_repl_status");
     $result1 = mysqli_query($con,"select ip,dbname,user,pwd,port,monitor,send_mail,send_mail_to_list,send_weixin,send_weixin_to_list,alarm_repl_status,threshold_warning_repl_delay from mysql_status_info");
	 
	$r=$re=array();
	 
	$sqls= array(
		"SHOW SLAVE STATUS",
		"SHOW GLOBAL VARIABLES WHERE variable_name REGEXP 'server_id$|^read_only'"
	 );

while( list($ip,$dbname,$user,$pwd,$port,$monitor,$send_mail,$send_mail_to_list,$send_weixin,$send_weixin_to_list,$alarm_repl_status,$threshold_warning_repl_delay) = mysqli_fetch_array($result1))
{		
if($monitor==0 || empty($monitor)){
	echo "\n被监控主机：$ip  【{$dbname}库】未开启监控，跳过不检测。"."\n";
	continue;
}		
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
           while($row = $result->fetch_assoc()){
		$r[$row['Variable_name']] = $row['Value'];
		unset($row['Variable_name'],$row['Value']);
		array_push($re,$row);
	   }
	    array_push($re,$r); //组成一个大的二维数组
            if (is_object($result)){
                mysqli_free_result($result);
	    }
        } else die(sprintf("MySQLi Error: %s", mysqli_error($link)));
        $processed++;
    }
} while ($processed < count($all_links));
	//echo "---------------------------"."\n";
        //print_r(convertarray($re)); //调试
	$re=convertarray($re); //将二维数组转换为一维数组，方便取出数据库状态值
	
	//1为Primary，0为Secondary
	$role=!isset($re['Slave_IO_State'])?1:0;
	$gtid=$re['Auto_Position']==1?'ON':'OFF';
	echo "角色是:".$role_status=$role==1?'Primary'.PHP_EOL:'Secondary'.PHP_EOL;
	$is_live=isset($connect_error)?0:1;
	//echo $is_live."\n";
	$Last_IO_Error=preg_replace('/\'/', '', $re['Last_IO_Error']);
	$Last_SQL_Error=preg_replace('/\'/', '', $re['Last_SQL_Error']);
	
	if($is_live==0){
	    echo "$ip"."\n";
            echo $connect_error."\n";
	    unset($connect_error);
	    $sql = "INSERT INTO mysql_repl_status(host,dbname,port,role,is_live) VALUES('{$ip}','{$dbname}','{$port}','{$role}','{$is_live}')";	
	}  else {
		
		//同步状态监控
	     if($re['Slave_IO_Running']=='No' || $re['Slave_SQL_Running']=='No' ){
			 echo "【报错】主从同步复制Slave_IO_Running状态是：{$re['Slave_IO_Running']}; Slave_SQL_Running状态是：{$re['Slave_SQL_Running']}\n";
	    //告警---------------------  
	    if($send_mail==0 || empty($send_mail)){
			echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
	    } else {
	    	$alarm_subject = "【报错】被监控主机：".$ip."  【{$dbname}库】主从同步复制异常 ".date("Y-m-d H:i:s");
	    	$alarm_info = "被监控主机：".$ip."   【{$dbname}库】主从同步复制Slave_IO_Running状态是：{$re['Slave_IO_Running']}; Slave_SQL_Running状态是：{$re['Slave_SQL_Running']}";
	    	$sendmail = new mail($send_mail_to_list,$alarm_subject,$alarm_info);
                $sendmail->execCommand();
	    }
	    if($send_weixin==0 || empty($send_weixin)){
		echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
	    } else {
		$alarm_subject = "【报错】被监控主机：".$ip."  【{$dbname}库】主从同步复制异常 ".date("Y-m-d H:i:s");
		$alarm_info = "被监控主机：".$ip."   【{$dbname}库】主从同步复制Slave_IO_Running状态是：{$re['Slave_IO_Running']}; Slave_SQL_Running状态是：{$re['Slave_SQL_Running']}";
		$sendweixin = new weixin($send_weixin_to_list,$alarm_subject,$alarm_info);
		$sendweixin->execCommand();
	    }
	    if(($send_mail==1 || $send_weixin==1)){		
	          $update_slave_status = "UPDATE mysql_status_info SET alarm_repl_status = 1 WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}'";
	          mysqli_query($con, $update_slave_status);
	    }		
	    }  else  {     
	    //恢复---------------------
            if($send_mail==0 || empty($send_mail)){
                echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
            } 
            if($send_weixin==0 || empty($send_weixin)){
                echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
            }
	      if(($send_mail==1 || $send_weixin==1)){
		    $recover_repl_status_sql = "SELECT alarm_repl_status FROM mysql_status_info WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}' ";
	    	    $recover_repl_status = mysqli_query($con, $recover_repl_status_sql);
	    	    $recover_repl_status_row = mysqli_fetch_assoc($recover_repl_status);
	      }			
	    if(!empty($recover_repl_status_row['alarm_repl_status']) && $recover_repl_status_row['alarm_repl_status'] == 1 ){
		    $recover_subject = "【恢复】被监控主机：".$ip."  【{$dbname}库】主从同步复制已恢复 ".date("Y-m-d H:i:s");
		    $recover_info = "主从同步复制Slave_IO_Running状态是：{$re['Slave_IO_Running']}; Slave_SQL_Running状态是：{$re['Slave_SQL_Running']}";
		    if($send_mail==1 ){
			  $sendmail = new mail($send_mail_to_list,$recover_subject,$recover_info);
			  $sendmail->execCommand();
		    }
		    if($send_weixin==1 ){
			  $sendweixin = new weixin($send_weixin_to_list,$recover_subject,$recover_info);
			  $sendweixin->execCommand();
		    }
		
		    $update_repl_status_sql = "UPDATE mysql_status_info SET alarm_repl_status = 0 WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}'";
		    mysqli_query($con, $update_repl_status_sql);	
	    }
	   }  // end else Slave_IO_Running and Slave_SQL_Running
	   
	   
	   //同步延迟监控
	   if(!empty($threshold_warning_repl_delay) && $re['Seconds_Behind_Master']>=$threshold_warning_repl_delay){
	   echo "【告警】 【{$dbname}库】主从同步延迟{$re['Seconds_Behind_Master']}秒\n";
	    //告警---------------------  
	    if($send_mail==0 || empty($send_mail)){
			echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
	    } else {
	    	$alarm_subject = "【告警】被监控主机：".$ip."  【{$dbname}库】主从同步延迟 ".date("Y-m-d H:i:s");
	    	$alarm_info = "被监控主机：".$ip."   【{$dbname}库】主从同步延迟{$re['Seconds_Behind_Master']}秒";
	    	$sendmail = new mail($send_mail_to_list,$alarm_subject,$alarm_info);
                $sendmail->execCommand();
	    }
	    if($send_weixin==0 || empty($send_weixin)){
		echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
	    } else {
		$alarm_subject = "【告警】被监控主机：".$ip."  【{$dbname}库】主从同步延迟 ".date("Y-m-d H:i:s");
		$alarm_info = "被监控主机：".$ip."   【{$dbname}库】主从同步延迟{$re['Seconds_Behind_Master']}秒";
		$sendweixin = new weixin($send_weixin_to_list,$alarm_subject,$alarm_info);
		$sendweixin->execCommand();
	    }
	    if(($send_mail==1 || $send_weixin==1)){		
	          $update_slave_status = "UPDATE mysql_status_info SET alarm_repl_status = 3 WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}'";
	          mysqli_query($con, $update_slave_status);
	    } 
	   } 
	   else {
	    //恢复---------------------
            if($send_mail==0 || empty($send_mail)){
                echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
            } 
            if($send_weixin==0 || empty($send_weixin)){
                echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
            }
	      if(($send_mail==1 || $send_weixin==1)){
		    $recover_repl_status_sql = "SELECT alarm_repl_status FROM mysql_status_info WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}' ";
	    	    $recover_repl_status = mysqli_query($con, $recover_repl_status_sql);
	    	    $recover_repl_status_row = mysqli_fetch_assoc($recover_repl_status);
	      }			
	    if(!empty($recover_repl_status_row['alarm_repl_status']) && $recover_repl_status_row['alarm_repl_status'] == 3 ){
		    $recover_subject = "【恢复】被监控主机：".$ip."  【{$dbname}库】主从同步延迟已恢复 ".date("Y-m-d H:i:s");
		    $recover_info = "主从同步延迟{$re['Seconds_Behind_Master']}秒";
		    if($send_mail==1 ){
			  $sendmail = new mail($send_mail_to_list,$recover_subject,$recover_info);
			  $sendmail->execCommand();
		    }
		    if($send_weixin==1 ){
			  $sendweixin = new weixin($send_weixin_to_list,$recover_subject,$recover_info);
			  $sendweixin->execCommand();
		    }
		
		    $update_repl_status_sql = "UPDATE mysql_status_info SET alarm_repl_status = 2 WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}'";
		    mysqli_query($con, $update_repl_status_sql);	
	    }		
	   } // end else Seconds_Behind_Master
	} // end else is_live
//---------------------------------------------------------------------------
	     $sql = "INSERT INTO mysql_repl_status(server_id,host,dbname,port,role,is_live,read_only,gtid_mode,Master_Host,Master_Port,Slave_IO_Running,Slave_SQL_Running,Seconds_Behind_Master,Master_Log_File,Relay_Master_Log_File,Read_Master_Log_Pos,Exec_Master_Log_Pos,Last_IO_Error,Last_SQL_Error,create_time) values('{$re['server_id']}','{$ip}','{$dbname}','{$port}','{$role}','{$is_live}','{$re['read_only']}','{$gtid}','{$re['Master_Host']}','{$re['Master_Port']}','{$re['Slave_IO_Running']}','{$re['Slave_SQL_Running']}','{$re['Seconds_Behind_Master']}','{$re['Master_Log_File']}','{$re['Relay_Master_Log_File']}','{$re['Read_Master_Log_Pos']}','{$re['Exec_Master_Log_Pos']}','{$Last_IO_Error}','{$Last_SQL_Error}',now())";
		
        if (mysqli_query($con, $sql)) {
            echo "{$ip}:'{$dbname}'新记录插入成功\n";
	      echo "---------------------------\n\n";
		mysqli_query($con,"delete from mysql_repl_status where host='{$ip}' and dbname='{$dbname}' and port='{$port}' and create_time<DATE_SUB(now(),interval 30 second)");
        } else {
	      echo "{$ip}:'{$dbname}'新记录插入失败\n";
            echo "Error: " . $sql . "\n" . mysqli_error($con);
        }
	
//清空每个IP数据库状态值	
array_splice($re, 0, count($re));	
array_splice($r, 0, count($r));

} //end while
?>

