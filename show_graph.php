<?php

    $ip = $_GET['ip'];
    $dbname = $_GET['dbname'];
    $port = $_GET['port'];

?>


<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>图形展示</title>    
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/echarts.common.min.js"></script>
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/shine.js"></script>
</head>
<body style="height: 100%; margin: 0">

<!-- 定义查询时间间隔 -->
<div class="card">
<div class="card-body">

<table>
<tr>
<td>
<form action="" method="post">
<input type="hidden" name="1_hour" value="DATE_SUB(now(),interval 1 hour)">
<button type="submit" name="button" class="btn btn-primary">1 小时</button>
</form>
</td>

<td>
<form action="" method="post">
<input type="hidden" name="3_hour" value="DATE_SUB(now(),interval 3 hour)">
<button type="submit" name="button" class="btn btn-success">3 小时</button>
</form> 
</td>

<td>
<form action="" method="post">
<input type="hidden" name="6_hour" value="DATE_SUB(now(),interval 6 hour)">
<button type="submit" name="button" class="btn btn-info">6 小时</button>
</form> 
</td>

<td>
<form action="" method="post">
<input type="hidden" name="12_hour" value="DATE_SUB(now(),interval 12 hour)">
<button type="submit" name="button" class="btn btn-warning">12 小时</button>
</form> 
</td>

<td>
<form action="" method="post">
<input type="hidden" name="1_day" value="DATE_SUB(now(),interval 24 hour)">
<button type="submit" name="button" class="btn btn-danger">1 天</button>
</form> 
</td>

<td>
<form action="" method="post">
<input type="hidden" name="2_day" value="DATE_SUB(now(),interval 48 hour)">
<button type="submit" name="button" class="btn btn-secondary">2 天</button>
</form> 
</td>

</tr>
</table>

</div>
</div>

<?php

if(!empty($_POST['1_hour'])){
    $interval_time = $_POST['1_hour'];
}

if(!empty($_POST['3_hour'])){
    $interval_time = $_POST['3_hour'];
}

if(!empty($_POST['6_hour'])){
    $interval_time = $_POST['6_hour'];
}

if(!empty($_POST['12_hour'])){
    $interval_time = $_POST['12_hour'];
}

if(!empty($_POST['1_day'])){
    $interval_time = $_POST['1_day'];
}

if(!empty($_POST['2_day'])){
    $interval_time = $_POST['2_day'];
}

//echo '$interval_time变量值是: ' .$interval_time .'<br>';

?>
<!-- 结束----------------------------------------->


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

