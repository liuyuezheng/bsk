<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    static  $num = 0;

    /**
     * 当客户端连接时触发的事件。
     * @param $client_id
     */
    public static function onConnect($client_id)
    {
//        global $num;
//        Gateway::sendToClient($client_id, json_encode(array(
//            'type'      => 'init',
//            'msg' => $client_id
//        )));
        $resData = [
            'type' => 'init',
            'client_id' => $client_id,
            'msg' => 'connect is success' // 初始化房间信息
        ];
        Gateway::sendToClient($client_id, json_encode($resData));

    }

    /**
     * 有消息时
     * @param int $client_id
     * @param mixed $message
     */
    public static function onMessage($client_id, $message)
    {
        GateWay::sendToAll($client_id,['type'=>'init', 'client_id' => $client_id, 'msg' =>$message]);
        // 客户端传递的是json数据
//        $message_data = json_decode($message, true);
//        if(!$message_data)
//        {
//            return ;
//        }
//        switch($message_data['type']){
//            case "bind":
//                $fromid = $message_data['fromid'];
//                Gateway::bindUid($client_id, $fromid);
//                Gateway::sendToUid($message_data['fromid'],json_encode(['type'=>'bind','msg'=>'绑定成功'])); //返回给发送者
//                return;
//            case "init":
//                Gateway::sendToGroup($message_data['group'], $message_data['message']);
//                return;
//        }
        //其它case 情况
    }
    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id)
    {
        // 向所有人发送
//        $resData = [
//            'type' => 'init',
//            'client_id' => $client_id,
//            'msg' => 'connect is close'
//        ];
        GateWay::sendToAll($client_id,['type'=>'close','client_id' => $client_id, 'msg' =>'closesssss']);
    }
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
//    public static function onConnect($client_id)
//    {
//        // 向当前client_id发送数据
//        Gateway::sendToClient($client_id, "Hello $client_id\r\n");
//        // 向所有人发送
//        Gateway::sendToAll("$client_id login\r\n");
//    }
//
//   /**
//    * 当客户端发来消息时触发
//    * @param int $client_id 连接id
//    * @param mixed $message 具体消息
//    */
//   public static function onMessage($client_id, $message)
//   {
//        // 向所有人发送
//        Gateway::sendToAll("$client_id said $message\r\n");
//   }
//
//   /**
//    * 当用户断开连接时触发
//    * @param int $client_id 连接id
//    */
//   public static function onClose($client_id)
//   {
//       // 向所有人发送
//       GateWay::sendToAll("$client_id logout\r\n");
//   }
}
