# mysql_monitor
简介：
目前常用开源监控工具有nagios，zabbix，grafana，但这些是面向专业DBA使用的，而对于业务研发人员来说，没有专业的MySQL理论知识，并且上述监控工具均为纯英文界面，交互不直观，那么多的监控指标，你知道有哪些是研发最关心的吗？

所以每次都是DBA通知研发，系统哪块出了问题，这样的效率其实是低下的，我是希望把监控这块东西定制化，做成开发一眼就能看懂的指标项，纯中文页面，清爽直观，简约而不简单，出了问题报警信息直接第一时间推送给研发，效率会大大提升，同时也减少了DBA作为中间人传话的作用（传达室大爷角色）。

参考了天兔Lepus的UI风格，目前采集了数据库连接数（具体连接了哪些应用程序IP，账号统计）、QPS/TPS、索引使用率统计，同步复制状态/延迟监控。可实现微信和邮件报警。

1、MySQL状态监控
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/MySQL%E7%8A%B6%E6%80%81%E7%9B%91%E6%8E%A7.png)

2、点击活动连接数，可以查看具体的连接数统计信息
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E8%BF%9E%E6%8E%A5%E6%95%B0%E8%AF%A6%E6%83%85.png)

3、点击图表，可以查看历史曲线图
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E5%8E%86%E5%8F%B2%E6%9B%B2%E7%BA%BF%E5%9B%BE.png)

4、主从状态监控
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/MySQL%E4%B8%BB%E4%BB%8E%E5%A4%8D%E5%88%B6%E7%9B%91%E6%8E%A7.png)

5、微信报警
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/%E5%BE%AE%E4%BF%A1%E6%8A%A5%E8%AD%A6.png)



# 环境搭建

yum install python-simplejson -y

chmod -R 755  ./mail/sendEmail
chmod -R 755  ./weixin/wechat.py

# check_mysql_status.php（用来采集被监控端MySQL状态信息）
# check_mysql_repl.php（用来采集被监控端MySQL主从复制信息）

定时任务每分钟抓取一次

*/1 * * * * cd /var/www/html/mysql_monitor/; /usr/bin/php /var/www/html/mysql_monitor/check_mysql_repl.php > /dev/null 2 >&1

*/1 * * * * cd /var/www/html/mysql_monitor/; /usr/bin/php /var/www/html/mysql_monitor/check_mysql_status.php > /dev/null 2 >&1
