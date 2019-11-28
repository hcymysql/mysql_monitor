<?php

    $ip = $_GET['ip'];
    $dbname = $_GET['dbname'];
    $port = $_GET['port'];

?>


<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>图形展示</title>    
    <script src="js/echarts.common.min.js"></script>
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/shine.js"></script>
</head>
<body style="height: 100%; margin: 0">
    <div id="connect" style="height:400px"></div>
          <?php include 'js/show_connect.php';?> 
     <div id="qps" style="height:400px"></div>
	      <?php include 'js/show_graph_qps.php';?> 
<br><br>
     <div id="index" style="height:400px"></div>
              <?php include 'js/show_graph_index.php';?>
<br><br>
</body>
</html>

