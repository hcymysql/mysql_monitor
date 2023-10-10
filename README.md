# MySQL Monitor面向研发人员图形可视化监控工具
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/image/mysql_monitor_chatgpt.png)

### 2022-01-03 增加sys schema性能诊断报告 --- 重新拉取table_statistic.php文件覆盖即可
1）统计业务库里执行次数最频繁的前10条SQL语句 

2）统计库里访问次数最多的前10张表

### 2022-02-05 提供podman镜像
获取镜像启动容器参见地址：https://blog.51cto.com/hcymysql/4984088

# 简介：
目前常用开源监控工具有nagios，zabbix，grafana，但这些是面向专业DBA使用的，而对于业务研发人员来说，没有专业的MySQL理论知识，并且上述监控工具均为纯英文界面，交互不直观，那么多的监控指标，你知道有哪些是研发最关心的吗？

所以每次都是DBA通知研发，系统哪块出了问题，这样的效率其实是低下的，我是希望把监控这块东西定制化，做成开发一眼就能看懂的指标项，纯中文页面，清爽直观，简约而不简单，出了问题报警信息直接第一时间推送给研发，效率会大大提升，同时也减少了DBA作为中间人传话的作用（传达室大爷角色）。

参考了天兔Lepus的UI风格，目前采集了数据库连接数（具体连接了哪些应用程序IP，账号统计）、QPS/TPS、索引使用率统计，同步复制状态/延迟监控。

采用远程连接方式获取数据，所以无需要在数据库服务器端部署相关agent或计划任务，可实现微信和邮件报警。

1、MySQL状态监控
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/MySQL%E7%8A%B6%E6%80%81%E7%9B%91%E6%8E%A7.png)

2、点击活动连接数，可以查看具体的连接数统计信息
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E8%BF%9E%E6%8E%A5%E6%95%B0%E8%AF%A6%E6%83%85.png)

3、点击图表，可以查看历史曲线图
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E5%8E%86%E5%8F%B2%E6%9B%B2%E7%BA%BF%E5%9B%BE.png)

4、主从复制状态监控

![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/MySQL%E4%B8%BB%E4%BB%8E%E5%A4%8D%E5%88%B6%E7%9B%91%E6%8E%A7.png)

5、微信报警

![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E5%BE%AE%E4%BF%A1%E6%8A%A5%E8%AD%A6.png)

6、邮件报警

![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E9%82%AE%E4%BB%B6%E5%91%8A%E8%AD%A6.png)

7、在MySQL 状态监控栏目下，点击数据库名，可以查看具体的表大小统计信息以及主键自增键值统计

![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/image/table_info.png)

注：sql_mode模式要去掉only_full_group_by，否则报错
      
    ERROR 1055 (42000): Expression #2 of SELECT list is not in GROUP BY clause and contains nonaggregated column 't.ENGINE' which is not functionally dependent on columns 
    in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by

```mysql> SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));```

一、环境搭建
# yum install httpd php php-mysqlnd php-json -y
# yum install python-simplejson -y
# service httpd start

# 注：必须依赖php-mysqlnd驱动扩展，系统自带的php-mysql要卸载掉，可参考下面的文章进行yum安装
# Linux上安装php-mysqlnd扩展实例
http://www.php.cn/php-weizijiaocheng-387963.html


把https://github.com/hcymysql/mysql_monitor/archive/master.zip安装包解压缩到
/var/www/html/目录下

# cd /var/www/html/mysql_monitor/
# chmod 755 ./mail/sendEmail 
# chmod 755 ./weixin/wechat.py
（注：邮件和微信报警调用的第三方工具，所以这里要赋予可执行权限755）

二、MySQL Monitor监控工具搭建

1、导入MySQL Monitor监控工具表结构（sql_db库）
# cd  /var/www/html/mysql_monitor/
# mysql  -uroot  -p123456  <  mysql_monitor_schema.sql

## 注：mysql_status_history表引擎可以更改为rocksdb引擎，支持数据压缩，这对于减小存储空间以及增快IO效率都有直接的帮助。
https://www.percona.com/blog/2018/04/30/a-look-at-myrocks-performance/

2、录入被监控主机的信息

mysql>insert  into 
`mysql_status_info`(`id`,`ip`,`dbname`,`user`,`pwd`,`port`,`monitor`,`send_mail`,`sen
d_mail_to_list`,`send_weixin`,`send_weixin_to_list`,`alarm_threads_running`,`thresh
old_alarm_threads_running`,`alarm_repl_status`,`threshold_warning_repl_delay`) 
values 
(1,'127.0.0.1','sql_db','admin','hechunyang',3306,1,1,'chunyang_he@139.com,chu
nyang_he@126.com',1,'hechunyang',NULL,NULL,NULL,NULL);

注，以下字段可以按照需求变更：

ip字段含义：输入被监控MySQL的IP地址

dbname字段含义：输入被监控MySQL的数据库名

user字段含义：输入被监控MySQL的用户名（最好给ALL管理员权限）

pwd字段含义：输入被监控MySQL的密码

port字段含义：输入被监控MySQL的端口号

monitor字段含义：0为关闭监控（也不采集数据，直接跳过）;1为开启监控（采集数据）

send_mail字段含义：0为关闭邮件报警;1为开启邮件报警

send_mail_to_list字段含义：邮件人列表

send_weixin字段含义：0为关闭微信报警;1为开启微信报警

send_weixin_to_list字段含义：微信公众号

threshold_alarm_threads_running字段含义：设置连接数阀值（单位个）

threshold_warning_repl_delay字段含义：设置主从复制延迟阀值（单位秒）


3、修改conn.php配置文件

# vim /var/www/html/mysql_monitor/conn.php

$con = mysqli_connect("127.0.0.1","admin","hechunyang","sql_db","3306") or die("数据库链接错误".mysql_error());

改成你的MySQL Monitor监控工具表结构（sql_db库）连接信息



4、修改邮件报警信息

# cd /var/www/html/mysql_monitor/mail/
# vim mail.php

system("./mail/sendEmail -f chunyang_he@139.com -t '{$this->send_mail_to_list}' -s 
smtp.139.com:25 -u '{$this->alarm_subject}' -o message-charset=utf8 -o message-content-type=html -m '报警信息：<br><font 
color='#FF0000'>{$this->alarm_info}</font>' -xu chunyang_he@139.com -xp 
'123456' -o tls=no");

改成你的发件人地址，账号密码，里面的变量不用修改。


5、修改微信报警信息

# cd /var/www/html/mysql_monitor/weixin/
# vim wechat.py
微信企业号设置移步
https://github.com/X-Mars/Zabbix-Alert-WeChat/blob/master/README.md 看此教程配置。

6、定时任务每分钟抓取一次

# crontab -l
*/1 * * * * cd /var/www/html/mysql_monitor/; /usr/bin/php 
/var/www/html/mysql_monitor/check_mysql_repl.php > /dev/null 2 >&1

*/1 * * * * cd /var/www/html/mysql_monitor/; /usr/bin/php 
/var/www/html/mysql_monitor/check_mysql_status.php > /dev/null 2 >&1

# check_mysql_status.php（用来采集被监控端MySQL状态信息和触发报警——单进程）
# parallel_check_mysql_status.php （2022-01-05 新增并发多进程采集被监控端MySQL状态信息和触发报警，默认并发10个进程，建议使用）
# check_mysql_repl.php（用来采集被监控端MySQL主从复制信息和触发报警）


7、更改页面自动刷新频率

# vim mysql_status_monitor.php
# vim mysql_repl_monitor.php

http-equiv="refresh" content="600"

默认页面每600秒自动刷新一次。


8、页面访问

http://yourIP/mysql_monitor/mysql_status_monitor.php

http://yourIP/mysql_monitor/mysql_repl_monitor.php

加一个超链接，可方便地接入你们的自动化运维平台里。

