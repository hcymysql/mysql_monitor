<?php
//header("Content-type:text/html;charset=utf-8;");

class weixin{
	private $send_weixin_to_list;
	private $alarm_subject;
	private $alarm_info;

	function __construct($send_weixin_to_list,$alarm_subject,$alarm_info){
		$this->send_weixin_to_list = $send_weixin_to_list;
		$this->alarm_subject = $alarm_subject;
		$this->alarm_info = $alarm_info;
	}
	
	function execCommand(){
	echo $this->alarm_subject."\n";
	echo $this->alarm_info."\n";
	system("/usr/bin/python ./weixin/wechat.py '{$this->send_weixin_to_list}' '{$this->alarm_subject}' '【内容】：\n{$this->alarm_info}'");
	}
}

?>
