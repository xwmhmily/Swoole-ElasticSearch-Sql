## Use Swoole and ElasticSearch-sql to search data on ElasticSearch
搜索一直是个比较麻烦的事情, 直接查 MySQL 性能与IO 又不太好, ElasticSearch 将数据存储，索引，搜索变得简单，但 DSL 和搜索模式也是花样多多，语法不容易熟悉，鉴于 SQL 在数据库方面的强大应用和方便易记，ElasticSearch 推出了 ElasticSearch-sql, 利用 SQL 来查询 ELK 上面的数据。Niceeee.... 问题又来了，如何根据搜索的各种各样的情况组装 SQL 又成了头疼的事了，数据如何导入？ 索引如何创建？文档如何更新与删除？如何搜索与分页？会不会有什么坑呢？又如何与业务结合呢？经过一番摸索和躺坑(⊙v⊙)，得出了此项目

### ElasticSearch-sql 官方文档
https://www.elastic.co/cn/what-is/elasticsearch-sql

### MiniHttp (Base on Swoole) 文档
https://github.com/xwmhmily/miniHttp

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
- 启动成功使用 sh socket.sh status 将看到以下进程在默默的等候命令了
```
Swoole-ElasticSearch-Sql_master
Swoole-ElasticSearch-Sql_manager
Swoole-ElasticSearch-Sql_task
Swoole-ElasticSearch-Sql_worker
```
- cd 至 shell 目录, 执行 sh process.sh 开启后端服务进程
- 启动成功再次使用 sh socket.sh status 将看到多了两个进程
```
Swoole-ElasticSearch-Sql_Insert
Swoole-ElasticSearch-Sql_Remove
```
他们是干嘛的？(⊙v⊙)嗯，用于与 ES 交互，创建索引，索引文档，删除文档，读取队列等

### 创建索引
- curl "http://127.0.0.1:8888/product/createIndex"

### 导入数据至 ES
- curl "http://127.0.0.1:8888/product/indexAll"

### 验证
- 访问 Kibana, 检查索引和数据是否OK，若有误，请根据 log 下的日志修复再重试

### 搜索名称带【美邦】，标签带【时尚】和【格子】，价格在 1 和 12550 之间的商品
- cd 至 client 目录, 执行 php http.php
- 或者 CLI 下 curl "http://127.0.0.1:8888/search?keyword=%E7%BE%8E%E9%82%A6&tags=%E6%97%B6%E5%B0%9A,%E6%A0%BC%E5%AD%90&price=1,12550&sort=4"
> 日志中生成的 SQL 是酱紫的：
```
SELECT * FROM products WHERE (name LIKE '%美邦%') AND (tags LIKE '%时尚%' AND tags LIKE '%格子%') AND (price >= 1 AND price <= 12550) ORDER BY price DESC
```
### 分页
- 有点不巧, ElasticSearch-sql 出于安全与性能考虑，只支持 SQL Limit, 而不支持 offset，但分页又是必须的，咋整呢？( ⊙o⊙ ) 莫方, 官方给了另一个方案：使用游标。当搜索返回的数据有 cursor 时, 我们取回来，传给客户端，客户端分页时，带上这个 cursor，我们请求 ES 时再带过去，即可，像酱紫
```
http://127.0.0.1:8888/search?keyword=%E7%BE%8E%E9%82%A6&tags=%E6%97%B6%E5%B0%9A,%E6%A0%BC%E5%AD%90&pricex=1,12550&sort=4&cursor=49itAwFaAXNARFhGMVpYSjVRVzVrUm1WMFkyZ0JBQUFBQUFBV3BrOFdjekZuTkdodFN6UlNNRWRRUW1STlJEZElXVWQzVVE9Pf////8PEgFmBWJyYW5kAQVicmFuZAEEdGV4dAAAAAFmCGJyYW5kX2lkAQhicmFuZF9pZAEEbG9uZwAAAAFmDGJyYW5kX3BpbnlpbgEMYnJhbmRfcGlueWluAQR0ZXh0AAAAAWYIYnJhbmRfcHkBCGJyYW5kX3B5AQR0ZXh0AAAAAWYIY2F0ZWdvcnkBCGNhdGVnb3J5AQR0ZXh0AAAAAWYLY2F0ZWdvcnlfaWQBC2NhdGVnb3J5X2lkAQRsb25nAAAAAWYPY2F0ZWdvcnlfcGlueWluAQ9jYXRlZ29yeV9waW55aW4BBHRleHQAAAABZgtjYXRlZ29yeV9weQELY2F0ZWdvcnlfcHkBBHRleHQAAAABZgpkYXRlX2FkZGVkAQpkYXRlX2FkZGVkAQR0ZXh0AAAAAWYFZXNfaWQBBWVzX2lkAQR0ZXh0AAAAAWYCaWQBAmlkAQR0ZXh0AAAAAWYEbmFtZQEEbmFtZQEEdGV4dAAAAAFmBXByaWNlAQVwcmljZQEFZmxvYXQAAAABZgpwcm9kdWN0X2lkAQpwcm9kdWN0X2lkAQRsb25nAAAAAWYFc2FsZXMBBXNhbGVzAQR0ZXh0AAAAAWYKc29ydF9vcmRlcgEKc29ydF9vcmRlcgEEdGV4dAAAAAFmBnN0YXR1cwEGc3RhdHVzAQR0ZXh0AAAAAWYEdGFncwEEdGFncwEEdGV4dAAAAAP//wM=
```

### 拼音搜索，指定价格不高于 520
```
curl "http://127.0.0.1:8888/search?keyword=meibang&price=0,520&sort=3"
```
生成的 SQL 是酱紫的
```
SELECT * FROM products WHERE (brand_py LIKE '%meibang%' OR brand_pinyin LIKE '%meibang%' OR category_pinyin LIKE '%meibang%' OR category_py LIKE '%meibang%') AND (price <= 520) ORDER BY price ASC
```

### 只在某个分类下搜索, 指定 category_id, 且价格不低于 100
```
curl "http://127.0.0.1:8888/search?keyword=meibang&price=100,0&sort=3&category_id=1"
```
生成的 SQL
```
SELECT * FROM products WHERE category_id = '1' AND (brand_py LIKE '%meibang%' OR brand_pinyin LIKE '%meibang%' OR category_pinyin LIKE '%meibang%' OR category_py LIKE '%meibang%') AND (price >= 100) ORDER BY date_added DESC
```

### 只在某个品牌下搜索, 指定 brand_id
```
curl "http://127.0.0.1:8888/search?keyword=卫衣&price=0,0&sort=1&brand_id=1"
```
生成的 SQL
```
SELECT * FROM products WHERE brand_id = '1' AND (name LIKE '%卫衣%') ORDER BY date_added DESC
```

### 只搜索标签带【夏季】的商品，无关键字，无分类，无品牌，无价格要求
```
curl "http://127.0.0.1:8888/search?keyword=&price=0,0&sort=1&tags=%E5%A4%8F%E5%AD%A3"
```
生成的 SQL
```
SELECT * FROM products WHERE (tags LIKE '%夏季%') ORDER BY date_added DESC
```

### 新增一商品，如何同步
- 商城中新增或上架后，curl "http://127.0.0.1:8888/product/create?product_id=" + $product_id

### 修改一商品，如何同步
- 商城中修改后，curl "http://127.0.0.1:8888/product/update?product_id=" + $product_id

### 删除一商品，如何同步
- 商城中修改后，curl "http://127.0.0.1:8888/product/remove?product_id=" + $product_id

### 重新索引所有商品
```
curl "http://127.0.0.1:8888/product/removeAll"
curl "http://127.0.0.1:8888/product/indexAll"
```

### 重建索引及重新索引所有商品
```
curl "http://127.0.0.1:8888/product/rebuild"
```

### 心跳检测
- Crond 定时运行 shell/socket.sh heartbeat 检测 API
- Crond 定时运行 shell/process.sh 检测后端进程

### 啥，还想要语音搜索？
- 接个 ASR + 分词不就妥妥的吗

### 交个朋友还是请个猪脚饭
(⊙v⊙)嗯，最近吃土啃纸，如果该项目对你有些帮助，交个朋友还是请个猪脚饭的请扫码<br />
![image](https://github.com/xwmhmily/Swoole-ElasticSearch-Sql/blob/master/qr.jpg)