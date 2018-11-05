<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 15:57
 */

namespace app\push\controller;


//use app\push\jssdk\Jssdk;
use app\push\model\Answer;
use app\push\model\Points;
use app\push\model\Question;
use app\push\model\Relation;
use app\push\model\Room;
use app\push\model\Topic;
use app\push\model\Dan;
use app\push\model\User;
use app\push\model\UserToken;
use GatewayWorker\Lib\Gateway;
use think\Controller;
use think\Db;
use think\Request;
use think\worker\Server;

class push extends Controller
{

    public function bind()
    {
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';
       $uid = request()->param('uid');
        $room_id = request()->param('room_id');
        $client_id = request()->param('client_id');
        // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        Gateway::joinGroup($client_id, $room_id);

        // //用户进入房
        $count=Room::where('room_id',$room_id)->find();
        $point=new Points();
        $num=$point->countNum($room_id,$count['type']);//计算房间内有多少人
        //用户是否在房间内
        $res=$point->ifThere($room_id,$uid,$count['type']);
        if($res==0){
            if($num==$count['count']){
                return $this->error('人数已满','');
            }else{
                //房间增加人
                $aa=$point->addNum($room_id,$uid,$count['type']);
                $data=$point->infoList($room_id,$uid,$count['type']);
            }
        }else{
            //查询房间内用户信息
            $data=$point->infoList($room_id,$uid,$count['type']);
        }
        $info=json_encode(['type'=>'message', 'content' =>$data]);
        Gateway::sendToGroup($room_id,$info);
//        $data['url']=curPageURL();
        return $this->success('请求成功','',$data);
    }
    //点击去挑战
    // 个人 type=1   题目量  num=5
    // 团队 type=2   题目量  num=10
    public function challenge(){
        // Gateway::$registerAddress = '47.96.8.108:1238';
       $uid = request()->param('uid');
        $type = request()->param('type');
//        $num = request()->param('num');
        $point=new Points();
        $res=$point->clickChallenge($type,$uid);
        if($res['code']==1){
            $data['room_id']=$res['room_id'];
        }else{
            $data['room_id']=$res['room_id'];
        }
        return $this->success('请求成功','',$data);
    }
    //开启游戏
    public function startGame(){
        Gateway::$registerAddress='127.0.0.1:1238';
        $uid = request()->param('uid');
        $room_id=request()->param('room_id');
        $count=Room::where('room_id',$room_id)->find();
        //是否开启
        $point=new Points();
        $res=$point->startGames($uid,$room_id,$count['type']);
//            return $this->error('还有人未进入房间','');
       if($res==0){
           $info=json_encode(['type'=>'message', 'content' =>0]);
           Gateway::sendToGroup($room_id,$info);
            return $this->error('等待房主开启','');
        }else{
           $info=json_encode(['type'=>'message', 'content' =>1]);
           Gateway::sendToGroup($room_id,$info);
            return $this->success('开启成功，进入答题模式','');
        }
    }

//    //个人答题
//    public function answerQuestions(){
////        Gateway::$registerAddress = '47.96.8.108:1238';
//           $uid = request()->param('uid');
//        $anid=request()->param('answer_id');//选择id
//        $correct=request()->param('type');//正确性
//        $room=request()->param('room_id');//房间id
//        $topic=request()->param('topic_id');//题目id
//        $pktime=request()->param('pktime');//pk时间
//        $where=[];
//        if($room){
//            $where['room_id']= ['=',$room];
//        }
//        if($topic){
//            $where['topic_id']=['=',$topic];
//        }
//        $count=Room::where('room_id',$room)->find();
////        $data=[];
//        $point=new Points();
//        if($count['type']==1){
//            $res=Relation::where($where)->where('user_id',$uid)->find();
//             $data=$point->infoList($room,$uid,$count['type']);
//            if($res){
//                return $this->error('已答题','',$data);
//            }else{
//                $data['correct']=$point->correctNess($room,$uid,$correct,$topic,$anid,$count['type'],$pktime);
//            }
//        }else{
//            $res=Relation::where($where)->find();
//            $data=$point->infoList($room,$uid,$count['type']);
//            if($res){
//                return $this->error('已抢答','',$data);
//            }else{
//                $data['correct']=$point->correctNess($room,$uid,$correct,$topic,$anid,$count['type'],$pktime);
//            }
//        }
//        return $this->success('请求成功','',$data);
//    }
    //用户信息
    public function listInfo(){
        $uid = request()->param('uid');
        $points=new Points();
        $room = request()->param('room_id');
        $count=Room::where('room_id',$room)->find();
        $data=$points->infoList($room,$uid,$count['type']);
        return $this->success('请求成功','',$data['users']);
    }
    //个人答题完毕
     /*uid 用户id
      * room_id 房间id
      * point pk积分
      * pktime pk时间
      * num 答对题目数量
      *
      * */

     public function results(){
         $uid = request()->param('uid');
         $room = request()->param('room_id');
         $point=request()->param('point');
         $pktime=request()->param('pktime');
         $num=request()->param('num');
         $count=Room::where('room_id',$room)->find();
         $points=new Points();
         $data=$points->updatePoint($uid,$room,$point,$pktime,$num,$count['type']);
         return $this->success('请求成功','',$data);
     }
     //结束页
    public function answerEnd(){
//        Gateway::$registerAddress = '47.96.8.108:1238';
        $uid = request()->param('uid');
        $points=new Points();
        $room = request()->param('room_id');
        $client_id = request()->param('client_id');
        $count=Room::where('room_id',$room)->find();
        $arr=$points->infoList($room,$uid,$count['type']);
        $res=$points->calculate($arr['users'],$count['type']);
        $data=$points->infoList($room,$uid,$count['type']);
        return $this->success('请求成功','',$data['users']);
    }
    /*
    //选择项及题目
    num 题目数量
    */
    public function chooseList(){
         $room_id=request()->param('room_id');
         $res=Room::where('room_id',$room_id)->find();
         $tid=explode(",",$res['topic']);
        for($i=0;$i<count($tid);$i++){
//            $str=$str.",".$tmp[$res[$i]];

            $arr=Question::where('question_id',$tid[$i])
                ->find();
            $info['question_id']=$arr['question_id'];
            $info['content']=$arr['content'];
            $info['answer']=json_decode($arr['answer']);
            $info['right']=$arr['right'];
            $data[]=$info;
        }
        return $this->success('请求成功','',$data);
    }
    //分享sign_url
    public function share_wx(){
        vendor('Jssdks.jssdk');
        $jssdk=new \Jssdk("wxbd25e61a0ca3f524","6616135451128d730a8e3082cdc52025");
        $signPackage = $jssdk->GetSignPackage();
        return $this->success('请求成功','',$signPackage);
    }
    public function danWei(){
        $uid = request()->param('uid');
        $user=new User();
        $res=$user->where('user_id',$uid)->find();
        $data['dan']=Dan::select();
        $data['point']=$res['points'];
        return $this->success('请求成功','',$data);
    }
//    protected $socket = 'websocket://127.0.0.1:2346';
//    /**
//     * 收到信息
//     * @param $connection
//     * @param $data
//     */
//    public function onMessage($connection, $data)
//    {
//        $connection->send('我收到你的信息了');
//    }
//
//    /**
//     * 当连接建立时触发的回调函数
//     * @param $connection
//     */
//    public function onConnect($connection)
//    {
//
//    }
//
//    /**
//     * 当连接断开时触发的回调函数
//     * @param $connection
//     */
//    public function onClose($connection)
//    {
//
//    }
//    /**
//     * 当客户端的连接上发生错误时触发
//     * @param $connection
//     * @param $code
//     * @param $msg
//     */
//    public function onError($connection, $code, $msg)
//    {
//        echo "error $code $msg\n";
//    }
//
//    /**
//     * 每个进程启动
//     * @param $worker
//     */
//    public function onWorkerStart($worker)
//    {
//
//    }
}