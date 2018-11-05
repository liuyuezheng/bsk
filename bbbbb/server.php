<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 17:23
 */
define('APP_PATH', __DIR__ . '/application/');
define('BIND_MODULE','push/push');
// 加载框架引导文件
require __DIR__ . '/thinkphp/start.php';
//d)创建workerman的controller，命名为Worker.php。在application/push/controller，目录不存在自行创建。添加以下内容：
//protected $socket = 'websocket://127.0.0.1:2346'其中127.0.0.1为socket服务器所在的ip地址。此处监听本机的2346端口。