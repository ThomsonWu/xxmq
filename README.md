# ******xxmq******

###### introduce:
xxmq is a plugin which is xiaoxiangxueyuan base service develop for phper to use all kinds of queue sdk.If you have better suggest please contact me :

xxmq 是小象学院基础服务开发的一款服务于php程序员的使用队列sdk的插件 如果有好的建议请联系我：

wuqisheng@xiaoxiangxueyuan.com

Enjoy it!

> ## install & use

1.require you install [composer](https://www.phpcomposer.com/) if you havn`t had it yet!

如果你还没有安装[composer](https://www.phpcomposer.com/) 请先安装

2.you may go to your app root path to execute :

在你的项目根目录执行以下命令：


```
composer require xxmq/xxmq
```

3.after install you can replace your file config.php which is under directory src to your own connect information.

安装完成替换src 目录下的config.php 文件中的sdk 连接信息为你自己的sdk信息。

4.use xxmq in wherever you want to use your queue and enjoy it

在你需要用到队列的文件中使用use关键字声明 xxmq并使用

> ## finish
In fact until now xxmq is only surport aliyun RocketMQ .kafka coming soon.
目前xxmq只支持阿里云RocketMQ 。随后将接入kafka 敬请期待。
