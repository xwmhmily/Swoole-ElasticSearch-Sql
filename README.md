<hr /># Swoole-ElasticSearch-Sql

## Use Swoole and ElasticSearch-sql to search data on ElasticSearch
搜索一直是个比较麻烦的事情, 直接查 MySQL 性能与IO 又不太好, ElasticSearch 将数据存储，索引，搜索变得简单，但 DSL 和搜索模式也是花样多多，语法不容易熟悉，鉴于 SQL 在数据库方面的强大应用和方便易记，ElasticSearch 推出了 ElasticSearch-sql, 利用 SQL 来查询 ELK 上面的数据。Niceeee.... 问题又来了，如何根据搜索的各种各样的情况组装 SQL 又成了头疼的事了，数据如何导入？ 索引如何创建？文档如何更新与删除？如何搜索与分页？会不会有什么坑呢？经过一番摸索和躺坑，得出了此项目

### 安装
- git clone 至任意目录
- 导入 mini.sql 至一MySQL 数据库
