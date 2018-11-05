<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 15:57
 */

namespace app\push\controller;


use app\push\jssdk\Jssdk;
use app\push\model\Answer;
use app\push\model\Points;
use app\push\model\Relation;
use app\push\model\Room;
use app\push\model\Topic;
use think\Controller;
use think\Db;
use think\Request;
use think\worker\Server;

class push extends Controller
{

    public function bind()
    {
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1.1236';
        $uid=UserToken::getCurrentUid();
        $uid = request()->param('uid');
        $room_id = request()->param('room_id');
        $client_id = request()->param('client_id');
        // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        Gateway::joinGroup($client_id, $room_id);
        //用户进入房
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
//        $data['url']=curPageURL();
        return $this->success('请求成功','',$data);
    }
    //用户信息
    public function peopleList(){
        $points=new Points();
        $uid = request()->param('uid');
        $room_id = request()->param('room_id');
        $count=Room::where('room_id',$room_id)->find();
        $data=$points->infoList($room_id,$uid,$count['type']);
        return $this->success('请求成功','',$data);
    }
    //点击去挑战
    // 个人 type=1
    // 团队 type=2
    public function challenge(){
        // Gateway::$registerAddress = '127.0.0.1:1238';
       $uid = request()->param('uid');
        $type = request()->param('type');
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
        // Gateway::$registerAddress='127.0.0.1:1238';
        $uid = request()->param('uid');
        $room_id=request()->param('room_id');
        $count=Room::where('room_id',$room_id)->find();
        //是否开启
        $point=new Points();
        $res=$point->startGames($uid,$room_id,$count['type']);
        if($res==2){
            return $this->error('还有人未进入房间','');
        }elseif($res==0){
            return $this->error('等待房主开启','');
        }else{
            return $this->success('开启成功，进入答题模式','');
        }
    }

    //个人答题
    public function answerQuestions(){
        // Gateway::$registerAddress = '127.0.0.1:1238';
       $uid = request()->param('uid');
        $anid=request()->param('answer_id');//选择id
        $correct=request()->param('type');//正确性
        $room=request()->param('room_id');//房间id
        $topic=request()->param('topic_id');//题目id
        $pktime=request()->param('pktime');//pk时间
        $where=[];
        if($room){
            $where['room_id']= ['=',$room];
        }
        if($topic){
            $where['topic_id']=['=',$topic];
        }
        $count=Room::where('room_id',$room)->find();
//        $data=[];
        $point=new Points();
        if($count['type']==1){
            $res=Relation::where($where)->where('user_id',$uid)->find();
            if($res){
                return $this->error('已答题','');
            }else{
                $info=$point->correctNess($room,$uid,$correct,$topic,$anid,$count['type'],$pktime);
            }
        }else{
            $res=Relation::where($where)->find();
            if($res){
                return $this->error('已抢答','');
            }else{
                $info=$point->correctNess($room,$uid,$correct,$topic,$anid,$count['type'],$pktime);
            }
        }
        $points=new Points();
        $data=$points->infoList($room,$uid,$count['type']);
        $data['info']=$info['correct'];
        return $this->success('请求成功','',$data);
    }
    //答题完毕
    public function answerEnd(){
        // Gateway::$registerAddress = '127.0.0.1:1238';
        $uid = request()->param('uid');
        $points=new Points();
        $room = request()->param('room_id');
        $client_id = request()->param('client_id');
        $count=Room::where('room_id',$room)->find();
        $arr=$points->infoList($room,$uid,$count['type']);
        $res=$points->calculate($arr,$count['type']);
        $data=$points->infoList($room,$uid,$count['type']);
        return $this->success('请求成功','',$data);
    }
    /*
    //选择项及题目
    num 题目数量
    */
    public function chooseList($page=5,$size=1){
        $num=request()->param('num');
        $tid=Topic::max('topic_id');
        $tmp = range(1,$tid);
        $res=array_rand($tmp,$num);
        for($i=0;$i<$num;$i++){
            $data[]=Topic::with('answer')
                ->where('topic_id',$tmp[$res[$i]])
                ->find();
        }
        return $this->success('请求成功','',$data);
    }
    //分享sign_url
    public function share_wx(){
        $sign_url = request()->param('sign_url');
        $appid='';
        $appsecret='';
        $jssdk=new Jssdk($appid,$appsecret,$sign_url);
        $signPackage=$jssdk->getSignPackage();
        return $this->success('请求成功','',$signPackage);
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