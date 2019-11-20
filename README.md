# mysql_monitor
MySQL状态监控图形展示

通过简单的配置，可在页面监控到MySQL连接数，QPS，主从状态等信息。

![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/MySQL%E7%8A%B6%E6%80%81%E7%9B%91%E6%8E%A7.png)
![image](https://raw.githubusercontent.com/hcymysql/mysql_monitor/master/MySQL%E4%B8%BB%E4%BB%8E%E5%A4%8D%E5%88%B6%E7%9B%91%E6%8E%A7.png)

# 环境搭建

yum install python-simplejson -y


# check_mysql_status.php（用来采集被监控端MySQL状态信息）
# check_mysql_repl.php（用来采集被监控端MySQL主从复制信息）

定时任务每分钟抓取一次

*/1 * * * * cd /var/www/html/mysql_monitor/; /usr/bin/php /var/www/html/mysql_monitor/check_mysql_repl.php > /dev/null 2 >&1

*/1 * * * * cd /var/www/html/mysql_monitor/; /usr/bin/php /var/www/html/mysql_monitor/check_mysql_status.php > /dev/null 2 >&1
