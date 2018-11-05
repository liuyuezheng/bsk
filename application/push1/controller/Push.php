<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/12
 * Time: 15:57
 */

namespace app\push\controller;


use app\push\model\Answer;
use app\push\model\Points;
use app\push\model\Relation;
use app\push\model\Room;
use app\push\model\Topic;
use app\push\model\User;
use app\push\model\UserToken;
use GatewayWorker\Lib\Gateway;
use think\Controller;
use think\Db;
use think\Request;
use think\worker\Server;

class push extends Base
{

    public function bind()
    {
        // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
        Gateway::$registerAddress = '127.0.0.1:1238';
//        $uid = \app\push\model\UserToken::getCurrentUid();;
        $uid=UserToken::getCurrentUid();
//        $room_id= $_SESSION['room_id'];
        $room_id = request()->param('room_id');
        $client_id = request()->param('client_id');
        // client_id与uid绑定
        Gateway::bindUid($client_id, $uid);
        Gateway::joinGroup($client_id, $room_id);
        //用户进入房间
         $res=Points::where('room_id',$room_id)
        ->where('user_id',$uid)
        ->find();
        if(!$res){
            $list['user_id']=$uid;
            $list['room_id']=$room_id;
            $list['time']=time();
            $data=Points::insertGetId($list);
        }else{
            $num=Points::where('room_id',$room_id)->count('room_id');
            if($num==2 ||$num==4){
                return $this->error('人数已满','');
            }
        }
        return $this->success('请求成功','',$data);
//        return json($data);
    }
    //点击去挑战 个人
    public function challenge(){
        Gateway::$registerAddress = '127.0.0.1:1238';
        $uid=UserToken::getCurrentUid();

        $arr['time']=time();
        $arr['count']=2;
        $arr['type']=1;
        $res['room_id']=Room::insertGetId($arr);
        $res['user_id']=$uid;
        $res['time']=time();
//        $_SESSION['uid']=$uid;
        $_SESSION['room_id']=$res['room_id'];
        Points::insertGetId($res);
        $data=User::where('user_id',$uid)->find();
        $data['room_id']=$res['room_id'];
        return $this->success('请求成功','',$data);
    }
    //开启游戏
    public function startGame(){
        Gateway::$registerAddress='127.0.0.1:1238';
        $uid=UserToken::getCurrentUid();
        $room=request()->param('room_id');
        $where=[];
        $where1=[];
        if($room){
            $where['room_id']= ['=',$room];
            $where1['room_id']= ['=',$room];
        }
        if($uid){
            $where['user_id']=['=',$uid];
            $where1['user_id']=['=',$uid];
        }
        $where['p_type']=1;
        $res=Points::where($where)->find();
        if($res){
            return $this->error('已开','');
        }else{
            $points=new Points();
            $points->save(['p_type'=>1],$where1);
        }
        $count=Points::where('room_id',$room)->count();
        if($count==2 ||$count==4){
            return $this->success('成功','');
        }else{
            return $this->error('还有人未开启','');
        }
    }

    //个人答题
    public function answerQuestions(){
        Gateway::$registerAddress = '127.0.0.1:1238';
        $uid=1;
        $anid=request()->param('answer_id');//选择id
        $type=request()->param('type');//正确性
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
        $where['user_id']=$uid;
        $arr['user_id']=$uid;
        $arr['topic_id']=$topic;
        $arr['answer_id']=$anid;
        $arr['status']=1;
        $arr['createtime']=time();
        $arr['room_id']=$room;
        $num=Points::where('room_id',$room)
            ->where('user_id',$uid)
            ->find();
        $res=Relation::where($where)->find();
        $data=[];
        if($res){
            return $this->error('已答题','',$data);
        }else{
//            $pan=Topic::where('topic_id',$topic)->find();
//            if($pan['answer_id']==$anid){
            if($type==1){
//             if(!$num){
//                 $list['user_id']=$uid;
//                 $list['point']=10;
//                 $list['room_id']=$room;
//                 $list['pktime']=$pktime;
//                 Points::save($list);
////               Points::insert(['user_id'=>$uid,'point'=>10,'room_id'=>$room,'pktime'=>$pktime]);
//             }else{
                 $list['point']=$num['point']+10;
                 $list['pktime']=$pktime;
                 Points::save($list,['id'=>$num['id']]);
//               Points::where('id',$num['id'])->update(['point'=>$num['point']+10,'pktime'=>$pktime]);
//             }
             $point=10;
             $arr['points']=$point;
             Relation::insert($arr);
             return $this->success('答题正确','');
            }else{
//                if(!$num){
//                    Points::insert(['user_id'=>$uid,'room_id'=>$room,'pktime'=>$pktime]);
//                }else{
                    if($num['point']==0){
                        $list['pktime']=$pktime;
                    }else{
                        $list['pktime']=$pktime;
                        $list['point']=$num['point']-10;
//                        $p_point=$num['point']-10;
                    }
                    Points::save($list,['id'=>$num['id']]);
//                    Points::where('id',$num['id'])->update(['point'=>$num['point']-10,'pktime'=>$pktime]);
//                }
//                  return $this->success('答题错误','',$list);
                $point=0;
                $arr['points']=$point;
                Relation::insert($arr);
                return $this->error('答题错误','');
            }

        }

//        $data=[];
//        if($res){
//            return $this->error('抢答失败','',$data);
//        }else{
//            $pan=Topic::where('')->find();
//            if($pan['answer_id']==$anid){
//             if(!$num){
//                 Points::insert(['user_id'=>$uid,'point'=>10,'room_id'=>$room]);
//             }else{
//                 Points::where('id',$num['id'])->update(['point'=>$num['point']+10]);
//             }
//            }else{
//                if(!$num){
//                    Points::insert(['user_id'=>$uid,'room_id'=>$room]);
//                }else{
//                    Points::where('id',$num['id'])->update(['point'=>$num['point']-10]);
//                }
//            }
//            return $this->success('抢答成功','',$data);
//        }
    }
    //个人答题完毕
    public function answerEnd(){
        Gateway::$registerAddress = '127.0.0.1:1238';
        $room=request()->param('room_id');//房间id
        $data=Points::alias('p')
            ->join('user u','u.user_id=p.user_id')
            ->where('p.room_id',$room)
            ->select();
//        $data['room']
        $points=new Points();
        $user=new User();
        if($data[0]['point']>$data[1]['point']){
          $arr['count']=$data[0]['count']+1;
          $arr['points']=$data[0]['points']+$data[0]['point'];
          if($data[1]['count']==0){
              $arr1['count']=0;
          }else{
              $arr1['count']=$data[1]['count']-1;
          }
          $points->save(['p_status'=>1],['id'=>$data[0]['id']]);
          $points->save(['p_status'=>0],['id'=>$data[1]['id']]);
          $user->save($arr,['user_id'=>$data[0]['user_id']]);
          $user->save($arr1,['user_id'=>$data[1]['user_id']]);
        }elseif($data[0]['point']==$data[1]['point']){
            if($data[0]['pktime']>$data[1]['pktime']){
                $arr['count']=$data[0]['count']+1;
                $arr['points']=$data[0]['points']+$data[0]['point'];
                if($data[1]['count']==0){
                    $arr1['count']=0;
                }else{
                    $arr1['count']=$data[1]['count']-1;
                }
                $points->save(['p_status'=>1],['id'=>$data[0]['id']]);
                $points->save(['p_status'=>0],['id'=>$data[1]['id']]);
                $user->save($arr,['user_id'=>$data[0]['user_id']]);
                $user->save($arr1,['user_id'=>$data[1]['user_id']]);
            }else{
                $arr['count']=$data[1]['count']+1;
                $arr['points']=$data[1]['points']+$data[1]['point'];
                if($data[0]['count']==0){
                    $arr1['count']=0;
                }else{
                    $arr1['count']=$data[0]['count']-1;
                }
                $points->save(['p_status'=>1],['id'=>$data[1]['id']]);
                $points->save(['p_status'=>0],['id'=>$data[0]['id']]);
                $user->save($arr,['user_id'=>$data[1]['user_id']]);
                $user->save($arr1,['user_id'=>$data[0]['user_id']]);
            }
            $points->save(['p_status'=>2],['id'=>$data[0]['id']]);
            $points->save(['p_status'=>2],['id'=>$data[1]['id']]);
        }else{
            $arr['count']=$data[1]['count']+1;
            $arr['points']=$data[1]['points']+$data[1]['point'];
            if($data[0]['count']==0){
                $arr1['count']=0;
            }else{
                $arr1['count']=$data[0]['count']-1;
            }
            $points->save(['p_status'=>1],['id'=>$data[1]['id']]);
            $points->save(['p_status'=>0],['id'=>$data[0]['id']]);
            $user->save($arr,['user_id'=>$data[1]['user_id']]);
            $user->save($arr1,['user_id'=>$data[0]['user_id']]);
        }
        $data2=Points::alias('p')
            ->join('user u','u.user_id=p.user_id')
            ->where('p.room_id',$room)
            ->select();
        return $this->success('请求成功','',$data2);
    }
    /*
    //选择项及题目
    num 题目数量
    */
    public function chooseList($page=5,$size=1){

//        Gateway::$registerAddress = '127.0.0.1:1238';
        $num=request()->param('num');
//        $num=5;
        $tid=Topic::max('topic_id');
        $tmp = range(1,$tid);
        $res=array_rand($tmp,$num);
//        $tmp = range(1,30);
//        $res=array_rand($tmp,10);
//        $data=[];
        for($i=0;$i<$num;$i++){
            $data[]=Topic::with('answer')
                ->where('topic_id',$tmp[$res[$i]])
                ->find();
        }

        return $this->success('请求成功','',$data);

//        $toid=rand(1,$tid);
//        $res=Topic::where('topic_id',$toid)->select();
//        var_dump($res);
//        return json($tmp);

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