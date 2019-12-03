<?php

//二维数组转换为一维数组
function convertarray($arr) {
    	static $res_arr = array();
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            convertarray($val);
        }
        else{
	    if($key == 'Handler_read_rnd_next'){
		$res_arr[$key][]= $val;
	    } else {
            	$res_arr[$key] = $val;
	    }
        }
    }
	return $res_arr;

}


?>
