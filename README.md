# snowman
> small mvc for grab  stock data and analysis ,simple using elastic as database and redis for caching data,using bootstrap theme and baidu Echart for analytic data display

#### 说明

------

snoman是用来做stock数据采集及分析的mvc框架，采用ElasticSearch做数据库存储，Redis做数据缓存，集成了简单的Router，View , Elastic 查询，Redis查询，Command脚本，Bootstroop主题，Echart图标绘制,datePicker 等功能，此版本为基础版本，后续功能将会逐步完善

> 本项目效果截图在exmple下面

#### 项目搭建

------

##### 安装方法

首先你需要安装elasticsearh和Redis等服务，执行git clone 完成项目初始化

```php
git clone git@github.com:martin20140408/snowman.git .
```

##### ElasticSearch/Redis安装

下载 [ElasticSearch](https://www.elastic.co/products/elasticsearch) 客户端,解压到本地目录后在bin点击elasticsearch.bat启动服务

下载 [Redis](http://redis.io/) 客户端，执行redis-server.exe启动服务

[可选]使用[nssm](http://nssm.cc/usage)为Elasticsearch和Redis注册系统服务

##### 数据采集

```php
php command.php capture #index stock
php command.php capture/getDailyData  #index daily data
```

##### 数据导入

```php
php command.php import/loadFromCsv #bulk load data from csv
php command.php import #load csv data from specific csv file
```

##### 数据导出

```php
php command.php export/saveToCsv #save remote data to local csv files
php command.php export  type=stock #import elasticsearch data to csv files
```

##### 数据删除

```php
php command.php delete #delete data by elasticsearch condition
```



##### ElasticHelper查询

> 支持mysql风格和Elastic风格两种查询方式

```php
Helper::getEs()
  ->select('COUNT(mid),sex,mid')
  ->term(["sex"=>1],1)
  ->terms(["mid"=>[34,35,33]])
  ->wildcard(["language"=>"*zh*"])
  ->sort("id desc ,mid asc")
  ->aggs("mid")
  ->limit(10);
等价于
  Helper::getEs()
  ->select('COUNT(mid),sex,mid')
  ->where(["sex"=>1],1)
  ->in(["mid"=>[34,35,33]])
  ->like(["language"=>"*zh*"])
  ->order("id desc ,mid asc")
  ->group("mid")
  ->limit(10);
//like mysql 
//select COUNT(mid),sex,mid from xx where sex != 1 and mid in ('34','35','33') and language like "%zh%" group by mid order bu id desc ,mid asc limit 0,10
```

