<?php
//并发多线程采集监控数据，默认是并发10个子进程，通过参数（$maxChildPro = 10）来设置。

error_reporting(E_USER_WARNING | E_USER_NOTICE);
ini_set('date.timezone','Asia/Shanghai');
require 'conn.php';
include 'mail/mail.php';
include 'weixin/weixin.php';

$result1 = mysqli_query($con,"select ip,dbname,user,pwd,port,monitor,send_mail,send_mail_to_list,send_weixin,send_weixin_to_list,threshold_alarm_threads_running from mysql_status_info order by dbname asc");
	
$r=$re=array();

$sqls=array(
	"SHOW GLOBAL STATUS WHERE VARIABLE_NAME REGEXP 'com_select$|com_insert$|com_update$|com_delete$|Threads_connected|^uptime$|Handler_read_key|Handler_read_rnd_next'",
        "SHOW GLOBAL STATUS WHERE VARIABLE_NAME REGEXP 'com_select$|com_insert$|com_update$|com_delete$|Threads_connected|^uptime$|Handler_read_key|Handler_read_rnd_next'",
        "SHOW GLOBAL VARIABLES WHERE VARIABLE_NAME REGEXP '^max_connections|^version$';",
        "SHOW SLAVE STATUS"
      );

//引入多线程并发采集数据
//最大的子进程数量
$maxChildPro = 10;

//当前的子进程数量
$curChildPro = 0;
 
//当子进程退出时，会触发该函数,当前子进程数-1
function sig_handler($sig)
{
	global $curChildPro;
		switch ($sig) {
			case SIGCHLD:
				$curChildPro--;
				break;
		}
}  

//配合pcntl_signal使用，简单的说，是为了让系统产生时间云，让信号捕捉函数能够捕捉到信号量
declare(ticks = 1);

//注册子进程退出时调用的函数。SIGCHLD：在一个进程终止或者停止时，将SIGCHLD信号发送给其父进程。
pcntl_signal(SIGCHLD, "sig_handler");
	
//-----------------------------------------------------------------------------------------------//	
while( list($ip,$dbname,$user,$pwd,$port,$monitor,$send_mail,$send_mail_to_list,$send_weixin,$send_weixin_to_list,$threshold_alarm_threads_running) = mysqli_fetch_array($result1))
{
    $curChildPro++;

    $pid = pcntl_fork();	
    if ($pid) {
//父进程运行代码,达到上限时父进程阻塞等待任一子进程退出后while循环继续
        if ($curChildPro >= $maxChildPro) {
            pcntl_wait($status);
        }
    } 
else {	
// 子进程运行代码，这里写你的代码		
if($monitor==0 || empty($monitor)){
        echo "\n被监控主机：$ip  【{$dbname}库】未开启监控，跳过不检测。"."\n";
        continue;
}		
$all_links  = array();

foreach ($sqls as $sql) { 
	$link1 = mysqli_init();
        $link1->options(MYSQLI_OPT_CONNECT_TIMEOUT, 60);
	if(! $link1->real_connect($ip,$user,$pwd,$dbname,$port)){
		$connect_error='down';
		break;
	}

	if(preg_match('/GLOBAL STATUS/',$sql)){
		sleep(1);  //等待1秒，相减得到QPS数值
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
            if (is_object($result)){
                mysqli_free_result($result);
	    }
        } else die(sprintf("MySQLi Error: %s", mysqli_error($link)));
        $processed++;
    }
} while ($processed < count($all_links));
	echo "---------------------------"."\n";
        print_r($re);  //调试
	
//报警和入库采集数据	
	$role=!isset(end($re)['Waiting for master to send event'])?1:0;
	echo "角色是:".$role_status=$role==1?'Primary'.PHP_EOL:'Secondary'.PHP_EOL;
	$is_live=isset($connect_error)?0:1;
	
	$QPS_SELECT=end($re)['Com_select'] - reset($re)['Com_select'];
	$QPS_INSERT=end($re)['Com_insert'] - reset($re)['Com_insert'];
	$QPS_UPDATE=end($re)['Com_update'] - reset($re)['Com_update'];
	$QPS_DELETE=end($re)['Com_delete'] - reset($re)['Com_delete'];
	$Handler_read_key=end($re)['Handler_read_key']-reset($re)['Handler_read_key'];
	$Handler_read_rnd_next=end($re)['Handler_read_rnd_next']-reset($re)['Handler_read_rnd_next'];	
	
	echo '每秒查询：'.$QPS_SELECT."\n";
	echo '每秒插入：'.$QPS_INSERT."\n";
	echo '每秒更新：'.$QPS_UPDATE."\n";	
	echo '每秒删除：'.$QPS_DELETE."\n";
	echo '当前连接数：'.end($re)['Threads_connected']."\n";

      //主机存活报警
	if($is_live==0){
	      //echo "$ip"."\n";
            //echo $connect_error."\n";
	      unset($connect_error);

	    //告警---------------------  
	    if($send_mail==0 || empty($send_mail)){
        	echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
	    } else {
	    	$alarm_subject = "【告警】被监控主机：".$ip."  【{$dbname}库】不能连接 ".date("Y-m-d H:i:s");
	    	$alarm_info = "被监控主机：".$ip."  【{$dbname}库】不能连接，请检查!";
	    	$sendmail = new mail($send_mail_to_list,$alarm_subject,$alarm_info);
            $sendmail->execCommand();
	    }
	    if($send_weixin==0 || empty($send_weixin)){
		echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
	    } else {
		$alarm_subject = "【告警】被监控主机：".$ip."  【{$dbname}库】不能连接 ".date("Y-m-d H:i:s");
		$alarm_info = "被监控主机：".$ip."  【{$dbname}库】不能连接，请检查!";
		$sendweixin = new weixin($send_weixin_to_list,$alarm_subject,$alarm_info);
		$sendweixin->execCommand();
	    }
            //-------------------------
	    $sql = "INSERT INTO mysql_status(host,dbname,port,is_live,create_time)  VALUES('{$ip}','{$dbname}','{$port}',{$is_live},now())"; 
	} else {
	    //恢复---------------------
            if($send_mail==0 || empty($send_mail)){
                echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
            } else {
	    	$recover_sql = "SELECT is_live FROM mysql_status_history WHERE HOST='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}' ORDER BY create_time DESC LIMIT 1";
	    	$recover_result = mysqli_query($con, $recover_sql);
	    	$recover_row = mysqli_fetch_assoc($recover_result);
	    }
	    if(!empty($recover_row) && $recover_row['is_live']==0){
		$recover_subject = "【恢复】被监控主机：".$ip."  【{$dbname}库】已恢复 ".date("Y-m-d H:i:s");
		$recover_info = "被监控主机：".$ip."  【{$dbname}库】已恢复";
		$sendmail = new mail($send_mail_to_list,$recover_subject,$recover_info);
		$sendmail->execCommand();
	    }

	    if($send_weixin==0 || empty($send_weixin)){
		echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
	    } else {
		$recover_sql = "SELECT is_live FROM mysql_status_history WHERE HOST='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}' ORDER BY create_time DESC LIMIT 1
";              $recover_result = mysqli_query($con, $recover_sql);
                  $recover_row = mysqli_fetch_assoc($recover_result);
	    }
            if(!empty($recover_row) && $recover_row['is_live']==0){
                $recover_subject = "【恢复】被监控主机：".$ip."  【{$dbname}库】已恢复 ".date("Y-m-d H:i:s");
                $recover_info = "被监控主机：".$ip."  【{$dbname}库】已恢复";
                $sendweixin = new weixin($send_weixin_to_list,$recover_subject,$recover_info);
                $sendweixin->execCommand();
            }
	      //echo $ip." ok"."\n";
            //echo $is_live."\n";
            $sql = "INSERT INTO mysql_status(host,dbname,port,role,is_live,max_connections,threads_connected,qps_select,qps_insert,qps_update,qps_delete,Handler_read_key,Handler_read_rnd_next,runtime,db_version,create_time) VALUES('{$ip}','{$dbname}','{$port}','{$role}',{$is_live},'".end($re)['max_connections']."',".end($re)['Threads_connected'].",$QPS_SELECT,$QPS_INSERT,$QPS_UPDATE,$QPS_DELETE,$Handler_read_key,$Handler_read_rnd_next,".round(end($re)['Uptime']/3600/24,1).",'".end($re)['version']."',now())";  
	}    
	
      //活动连接数报警
      if(!empty($threshold_alarm_threads_running) && end($re)['Threads_connected'] >=$threshold_alarm_threads_running){
	    //告警---------------------  
	    if($send_mail==0 || empty($send_mail)){
        	  echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
	    } else {
	    	    $alarm_subject = "【告警】被监控主机：".$ip."  【{$dbname}库】活动连接数超高，请检查。 ".date("Y-m-d H:i:s");
	    	    $alarm_info = "被监控主机：".$ip."  【{$dbname}库】活动连接数是 ".end($re)['Threads_connected'] ."，高于报警阀值{$threshold_alarm_threads_running}";
	    	    $sendmail = new mail($send_mail_to_list,$alarm_subject,$alarm_info);
                $sendmail->execCommand();
	    }

	    if($send_weixin==0 || empty($send_weixin)){
        	  echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
	    } else {
	    	    $alarm_subject = "【告警】被监控主机：".$ip."  【{$dbname}库】活动连接数超高，请检查。 ".date("Y-m-d H:i:s");
	    	    $alarm_info = "被监控主机：".$ip."  【{$dbname}库】活动连接数是 ".end($re)['Threads_connected'] ."，高于报警阀值{$threshold_alarm_threads_running}";
	    	    $sendweixin = new weixin($send_weixin_to_list,$alarm_subject,$alarm_info);
                $sendweixin->execCommand();
	    }	    
          if(($send_mail==1 || $send_weixin==1)){		
	          $update_connect_status = "UPDATE mysql_status_info SET alarm_threads_running = 1 WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}'";
	          mysqli_query($con, $update_connect_status);
	    }
	}  else {
	    //恢复---------------------
            if($send_mail==0 || empty($send_mail)){
                echo "被监控主机：$ip  【{$dbname}库】关闭邮件监控报警。"."\n";
            } 
            if($send_weixin==0 || empty($send_weixin)){
                echo "被监控主机：$ip  【{$dbname}库】关闭微信监控报警。"."\n";
            }
	      if(($send_mail==1 || $send_weixin==1)){
		    $recover_threads = "SELECT alarm_threads_running FROM mysql_status_info WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}' ";
	    	    $recover_threads = mysqli_query($con, $recover_threads);
	    	    $recover_threads_row = mysqli_fetch_assoc($recover_threads);
	      }
	      if(!empty($recover_threads_row['alarm_threads_running']) && $recover_threads_row['alarm_threads_running'] == 1){
		    $recover_subject = "【恢复】被监控主机：".$ip."  【{$dbname}库】活动连接数已恢复 ".date("Y-m-d H:i:s");
		    $recover_info = "被监控主机：".$ip."  【{$dbname}库】活动连接数已恢复，当前连接数是 ".end($re)['Threads_connected'];
		    if($send_mail==1 ){
			  $sendmail = new mail($send_mail_to_list,$recover_subject,$recover_info);
			  $sendmail->execCommand();
		    }
		    if($send_weixin==1 ){
			  $sendweixin = new weixin($send_weixin_to_list,$recover_subject,$recover_info);
			  $sendweixin->execCommand();
		    }
		
		    $update_connect_status = "UPDATE mysql_status_info SET alarm_threads_running = 0 WHERE IP='{$ip}' AND dbname='{$dbname}' AND PORT='{$port}'";
		    mysqli_query($con, $update_connect_status);		
	    }
	}  
	
//----------------------------------------------------
/*
pcntl_fork 导致 MySQL server has gone away 解决方案
pcntl_fork 前连数据库，就会报 MySQL server has gone away错误。
原因是子进程会继承主进程的数据库连接，当mysql返回数据时，这些子进程都可以通过这个连接读到数据，造成数据错乱。
该操作数据库的地方还是要操作数据库； 要解决这个问题，要在 $pid = pcntl_fork();  前清理掉之前的MySQL连接。
官方文档的解释以及解决方案
http://php.net/manual/en/function.pcntl-fork.php#70721
https://www.cnblogs.com/AllenChou/p/6607182.html
*/
    require 'conn.php'; //需要二次打开MySQL连接，就可以避免MySQL server has gone away错误，导致采集数据入库失败。
    if (mysqli_query($con, $sql)) {
      echo "\n{$ip}:'{$dbname}' 新记录插入成功\n";
	echo "-------------------------------------------------------------\n\n\n";
	mysqli_query($con,"INSERT INTO mysql_status_history(HOST,dbname,PORT,role,is_live,max_connections,threads_connected,qps_select,qps_insert,qps_update,qps_delete,Handler_read_key,Handler_read_rnd_next,runtime,db_version,create_time) SELECT HOST,dbname,PORT,role,is_live,max_connections,threads_connected,qps_select,qps_insert,qps_update,qps_delete,Handler_read_key,Handler_read_rnd_next,runtime,db_version,create_time FROM mysql_status;");
	
	mysqli_query($con,"DELETE FROM mysql_status where host='{$ip}' and dbname='{$dbname}' and port='{$port}' and create_time<DATE_SUB(now(),interval 30 second)");
	mysqli_close($con); 
    } else {
	  mysqli_close($con); 	
	  echo "\n{$ip}:'{$dbname}' 新记录插入失败\n";
        echo "Error: " . $sql . "\n" . mysqli_error($con)."\n";
    }	

	array_splice($re, 0, count($re));
	array_splice($r, 0, count($r));
	sleep(2);
	exit(0);
    }
}

?>

