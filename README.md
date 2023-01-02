# 轻小说版本图书馆主站全量代码仓库

## 部署步骤

1. 启用维护模式：将`/public/.htaccess.mainenance`文件的全部内容覆盖到生产环境的`/public/.htaccess`文件。

2. 从主仓库拉取代码到生产环境或开发环境。

3. 迁移数据库。

4. 在生产环境或开发环境项目根目录创建`.env`环境配置文件，内容形如：

   ```ini
   APP_DEBUG = false
   
   [APP]
   DEFAULT_TIMEZONE = Asia/Shanghai
   
   [DATABASE]
   TYPE = mysql
   HOSTNAME = 127.0.0.1
   DATABASE = lightnovel
   USERNAME = root
   PASSWORD = 
   HOSTPORT = 3306
   CHARSET = utf8
   DEBUG = true
   PREFIX = ln_
   
   [LANG]
   default_lang = zh-cn
   
   [EMAIL]
   PASSWORD = 
   
   [RECAPTCHA]
   SITE_KEY = 
   SECRET_KEY = 
   ```

5. 关闭维护模式，启用生产模式：将`/public/.htaccess.production`文件的全部内容覆盖到生产环境的`/public/.htaccess`文件。如果是在开发环境，还应该将`.env`文件的`APP_DEBUG`配置项设为`true`。

ThinkPHP 6.0
===============

> 运行环境要求PHP7.2+，兼容PHP8.1

[官方应用服务市场](https://market.topthink.com) | [`ThinkAPI`——官方统一API服务](https://docs.topthink.com/think-api)

ThinkPHPV6.0版本由[亿速云](https://www.yisu.com/)独家赞助发布。

## 主要新特性

* 采用`PHP7`强类型（严格模式）
* 支持更多的`PSR`规范
* 原生多应用支持
* 更强大和易用的查询
* 全新的事件系统
* 模型事件和数据库事件统一纳入事件系统
* 模板引擎分离出核心
* 内部功能中间件化
* SESSION/Cookie机制改进
* 对Swoole以及协程支持改进
* 对IDE更加友好
* 统一和精简大量用法

## 安装

~~~
composer create-project topthink/think tp 6.0.*
~~~

如果需要更新框架使用
~~~
composer update topthink/framework
~~~

## 文档

[完全开发手册](https://www.kancloud.cn/manual/thinkphp6_0/content)

## 参与开发

请参阅 [ThinkPHP 核心框架包](https://github.com/top-think/framework)。

## 版权信息

ThinkPHP遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2006-2021 by ThinkPHP (http://thinkphp.cn)

All rights reserved。

ThinkPHP® 商标和著作权所有者为上海顶想信息科技有限公司。

更多细节参阅 [LICENSE.txt](LICENSE.txt)
