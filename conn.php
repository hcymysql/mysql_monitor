<?php         
//https://github.com/hcymysql/mysql_monitor

     $con = mysqli_connect("10.10.159.31","admin","hechunyang","sql_db","3306") or die("数据库链接错误".mysql_error());
     mysqli_query($con,"set names utf8");  
?> 
