<hr /># Swoole-ElasticSearch-Sql

## Use Swoole and ElasticSearch-sql to search data on ElasticSearch
搜索一直是个比较麻烦的事情, 直接查 MySQL 性能与IO 又不太好, ElasticSearch 将数据存储，索引，搜索变得简单，但 DSL 和搜索模式也是花样多多，语法不容易熟悉，鉴于 SQL 在数据库方面的强大应用和方便易记，ElasticSearch 推出了 ElasticSearch-sql, 利用 SQL 来查询 ELK 上面的数据。Niceeee.... 问题又来了，如何根据搜索的各种各样的情况组装 SQL 又成了头疼的事了，数据如何导入？ 索引如何创建？文档如何更新与删除？如何搜索与分页？会不会有什么坑呢？经过一番摸索和躺坑，得出了此项目

### 安装
- 安装 PHP 7+, Swoole 4+ (否则使用不了协程)
- git clone 至任意目录
- 导入 mini.sql 至一MySQL 数据库
- 确认安装了 Redis, PDO 等扩展
- 确认安装了 Composer
- 安装好 ELK 三套件, 或者云厂商直接按小时购买一个，省时省力省运维，付费即可用

### 配置
- 设置 Env.php 中的 ENV 为 'DEV'
- 配置 conf/DEV.php 中的 MySQL, Redis, ElasticSearch 参数
- cd 至 library 目录下，运行 composer install 安装 ElasticSearch 官方的客户 PHP SDK

### 启动
- cd 至 shell 目录, 执行 sh socket.sh restart 开启 API 服务
- cd 至 shell 目录, 执行 sh process.sh 开启后端服务进程

### 创建索引
- curl "http://127.0.0.1:8888/product/createIndex"

### 导入数据至 ES
- curl "http://127.0.0.1:8888/product/indexAll"

### 验证
- 访问 Kibana, 检查索引和数据是否OK，若有误，请根据 log 下的日志修复再重试

### 搜索
- cd 至 client 目录, 执行 php http.php
