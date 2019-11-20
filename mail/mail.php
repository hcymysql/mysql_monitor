<?php

class mail{
	private $send_mail_to_list;
	private $alarm_subject;
	private $alarm_info;

	function __construct($send_mail_to_list,$alarm_subject,$alarm_info){
		$this->send_mail_to_list = $send_mail_to_list;
		$this->alarm_subject = $alarm_subject;
		$this->alarm_info = $alarm_info;
	}
	
	function execCommand(){
	echo $this->alarm_subject."\n";
	echo $this->alarm_info."\n";
	system("./mail/sendEmail -f chunyang_he@139.com -t '{$this->send_mail_to_list}' -s smtp.139.com:25 -u '{$this->alarm_subject}' -o message-charset=utf8 -o message-content-type=html -m '报警信息：<br><font color='#FF0000'>{$this->alarm_info}</font>' -xu chunyang_he@139.com -xp '123456' -o tls=no");
	}
}

?>
