<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 16:54
 */

namespace app\push\model;


use think\Db;
use think\Model;

class Points extends Model
{
    public function user(){
        return $this->hasMany('User','user_id','user_id');
    }
//计算房间内有多少人
    public function countNum($room_id,$type){
        $data=self::where('room_id',$room_id)->select();
        $num=0;
        if($type==1){
            $num+=count($data);
        }else{
            foreach ($data as $v){
                $arr=explode(",",$v['user_id']);
                $num+=count($arr);
            }
        }
        return $num;
    }
    //用户是否在房间内
    public function ifThere($room_id,$uid,$type){
        $res=self::where('room_id',$room_id)->select();
        if(!$res){
            $data=3;
        }else{
            if(count($res)>=2){
                $str=$res[0]['user_id'].",".$res[1]['user_id'];
                $arr=explode(",",$str);
                if(in_array($uid,$arr)){
                    $data=1;
                }else{
                    $data=0;
                }
            }else{
                $str=$res[0]['user_id'];
                if($type==1){
                    if($uid==(int)$str){
                        $data=1;
                    }else{
                        $data=0;
                    }
                }else{
                    $str=$res[0]['user_id'];
                    $arr=explode(",",$str);
                    if(in_array($uid,$arr)){
                        $data=1;
                    }else{
                        $data=0;
                    }
                }

            }
        }
        return $data;
    }
    //房间增加人
    public function addNum($room_id,$uid,$type){
        if ($type==1){
            $data=self::insertGetId(['room_id'=>$room_id,'user_id'=>$uid,'time'=>time()]);
        }else{
            $res=self::where('room_id',$room_id)->select();
            $arr=explode(",",$res[0]['user_id']);
            $num=count($arr);
            if($num!=2){
                $id=$res[0]['user_id'].','.$uid;
                $data=self::save(['user_id'=>$id],['id'=>$res[0]['id']]);
            }else{
                if(count($res)==2){
                    $arr2=explode(",",$res[1]['user_id']);
                    $num2=count($arr2);
                    if($num2!=2){
                        $id=$res[1]['user_id'].','.$uid;
                        $data=self::save(['user_id'=>$id],['id'=>$res[1]['id']]);
                    }
                }else{
                    $data=self::insertGetId(['room_id'=>$room_id,'user_id'=>$uid,'time'=>time()]);
                }

            }
//               }

        }
        return $data;
    }
    //用户信息
    public function infoList($room_id,$uid,$type){
           $data=[];
        if ($type==1){
            $res=self::with('user')->where('room_id',$room_id)->select();
            $room=Room::where('room_id',$room_id)->find();
            $data=$res;
            $data['home_id']=$room['user_id'];
        }else{
            $arr=self::where('room_id',$room_id)->select();
            $num=count($arr);//有几个人房间
            if($num==1){
                $uids=explode(",",$arr[0]['user_id']);
                foreach ($uids as $v){
                    $user[]=User::where('user_id',$v)->find();
                }
                $data=$arr;
                $data[0]['user']=$user;
            }else{
                $uids=explode(",",$arr[0]['user_id']);
                if(in_array($uid,$uids)){
                    foreach ($uids as $v){
                        $user[]=User::where('user_id',$v)->find();
                    }
                    $uids2=explode(",",$arr[1]['user_id']);
                    foreach ($uids2 as $k){
                        $user2[]=User::where('user_id',$k)->find();
                    }
                    $data=$arr;
                    $data[0]['user']=$user;
                    $data[1]['user']=$user2;
                }else{
                    foreach ($uids as $v){
                        $user[]=User::where('user_id',$v)->find();
                    }
                    $uids2=explode(",",$arr[1]['user_id']);
                    foreach ($uids2 as $k){
                        $user2[]=User::where('user_id',$k)->find();
                    }
                    $data[0]=$arr[1];
                    $data[1]=$arr[0];
                    $data[0]['user']=$user2;
                    $data[1]['user']=$user;
                }

            }
            $room=Room::where('room_id',$room_id)->find();
            $data['home_id']=$room['user_id'];

        }
        return $data;
    }
    //点击挑战
    public function clickChallenge($type,$uid){

        $list=self::where('isend',0)->select();
        foreach ($list as $v){
            $uids=explode(",",$v['user_id']);
            $isnot=in_array($uid,$uids);
            if($isnot){
                $info['room_id']=$v['room_id'];
            }
        }
        if($info){
            $data=$info;
            $data['code']=1;
        }else{

            if($type==1){
                $arr['count']=2;
                $arr['type']=1;
            }else{
                $arr['count']=4;
                $arr['type']=2;
            }
            $arr['user_id']=$uid;
            $arr['time']=time();
//        Points::where('user_id',$uid)->where('isend',0)->find();
            $res['room_id']=Room::insertGetId($arr);
            $res['user_id']=$uid;
            $res['time']=time();
            Points::insertGetId($res);
            $data['room_id']=$res['room_id'];
            $data['code']=0;
        }
        return $data;
    }
    //是否开启游戏
    public function startGames($uid,$room_id,$type){
        $rooms=self::where('room_id',$room_id)->select();
        $room=Room::where('room_id',$room_id)->find();
        if(count($rooms)==2){
            if($room['user_id']==$uid){
                foreach ($rooms as $v){
                    self::save(['p_type'=>1],['id'=>$v['id']]);
                }
                $data=1;

            }else{
                $data=0;
            }
        }else{
            $data=2;
        }
        return $data;
    }
    //答题正确性
    /*room_id房间id
        *user_id用户id
        *correct正确性
        *topic_id题目id
        *answer_id选择id
        * */
    public function correctNess($room_id,$uid,$correct,$topic_id,$answer_id,$type,$pktime){
        $list=[];
        $arr=[];
        $data=[];
//        $arr['user_id']=$uid;
        $arr['topic_id']=$topic_id;
        $arr['answer_id']=$answer_id;
        $arr['createtime']=time();
        $arr['room_id']=$room_id;
        if($correct==1){
            if($type==1){
                $res=self::where('room_id',$room_id)->where('user_id',$uid)->find();
                $list['point']=$res['point']+10;
                $list['pktime']=$pktime;
                $arr['status']=1;
                $arr['user_id']=$res['user_id'];
                self::save($list,['id'=>$res['id']]);
                Relation::insert($arr);
            }else{
                $res=Points::where('room_id',$room_id)->select();
                if(in_array($uid,$res[0]['user_id'])){
                    $list['point']=$res[0]['point']+10;
                    $list['pktime']=$pktime;
                    $arr['user_id']=$res[0]['user_id'];
                    self::save($list,['id'=>$res[0]['id']]);
                }else{
                    $list['point']=$res[1]['point']+10;
                    $list['pktime']=$pktime;
                    $arr['user_id']=$res[1]['user_id'];
                    self::save($list,['id'=>$res[1]['id']]);
                }
                $arr['status']=2;
                $arr['points']=10;
                $data['correct']=1;
                Relation::insert($arr);

            }
        }else{
            if($type==1){
                $res=self::where('room_id',$room_id)->where('user_id',$uid)->find();
                if($res['point']>=10){
                    $list['point']=$res['point']-10;
                }else{
                    $list['point']=$res['point'];
                }
                $list['pktime']=$pktime;
                $arr['status']=1;
                $arr['user_id']=$res['user_id'];
                self::save($list,['id'=>$res['id']]);
                Relation::insert($arr);
            }else{
                $res=Points::where('room_id',$room_id)->select();
                if(in_array($uid,$res[0]['user_id'])){
                    $list['point']=$res[1]['point']+10;
                    $list['pktime']=$pktime;
                    $arr['user_id']=$res[1]['user_id'];
                    self::save($list,['id'=>$res[1]['id']]);
                    self::save(['pktime'=>$pktime],['id'=>$res[0]['id']]);
                }else{
                    $list['point']=$res[0]['point']+10;
                    $list['pktime']=$pktime;
                    $arr['user_id']=$res[0]['user_id'];
                    self::save($list,['id'=>$res[0]['id']]);
                    self::save(['pktime'=>$pktime],['id'=>$res[1]['id']]);
                }
                $arr['status']=2;
                $data['correct']=0;
                Relation::insert($arr);
            }
        }
        return $data;
    }
    //答题完毕
    public function calculate($arr,$type){
        $users=new User();
        if($arr[0]['point']>$arr[1]['point']){
            if($type==1){
                $count=$arr[0]['user'][0]['count'];
                $point=$arr[0]['user'][0]['points'];
                $count2=$arr[1]['user'][0]['count'];
                $user['count']=$count+1;
                if($count2>0){
                    $user2['count']=$count2-1;
                }else{
                    $user2['count']=$count2;
                }
                $user['points']=$point+$arr[0]['point'];
                $users->save($user,['user_id'=>(int)$arr[0]['user_id']]);
                $users->save($user2,['user_id'=>(int)$arr[1]['user_id']]);
            }else{
                foreach ($arr[0]['user'] as $key=>$v){
                    $poin=$v['points']+$arr[0]['point'];
                    $coun=$v['count']+2;
                    User::where('user_id',$v['user_id'])->update(['points'=>$poin,'count'=>$coun]);
//                    $users->save($user1,['user_id'=>$v['user_id']]);
                }
                foreach ($arr[1]['user'] as $k=>$val){
                    if($val['count']>0){
                        $num=$val['count']-2;
                        if($num<0){
                            $nums=0;
                        }else{
                            $nums=$num;
                        }
                    }else{
                        $nums=$val['count'];
                    }
                    User::where('user_id',$val['user_id'])->update(['count'=>$nums]);
                }
            }
            self::save(['p_status'=>1,'isend'=>1],['id'=>$arr[0]['id']]);
            self::save(['p_status'=>0,'isend'=>1],['id'=>$arr[1]['id']]);
        }elseif($arr[0]['point']<$arr[1]['point']){
            if($type==1){
                $count=$arr[1]['user'][0]['count'];
                $point=$arr[1]['user'][0]['points'];
                $count2=$arr[0]['user'][0]['count'];
                $user['count']=$count+1;
                if($count2>0){
                    $user2['count']=$count2-1;
                }else{
                    $user2['count']=$count2;
                }
                $user['points']=$point+$arr[1]['point'];
                $users->save($user,['user_id'=>(int)$arr[1]['user_id']]);
                $users->save($user2,['user_id'=>(int)$arr[0]['user_id']]);
            }else{
                foreach ($arr[0]['user'] as $key2=>$v2){
                    $poin2=$v2['points']+$arr[0]['point'];
                    $coun2=$v2['count']+2;
                    User::where('user_id',$v2['user_id'])->update(['points'=>$poin2,'count'=>$coun2]);
//                    $users->save($user1,['user_id'=>$v['user_id']]);
                }
                foreach ($arr[1]['user'] as $k2=>$val2){
                    if($val2['count']>0){
                        $num=$val2['count']-2;
                        if($num<0){
                            $nums=0;
                        }else{
                            $nums=$num;
                        }
                    }else{
                        $nums=$val2['count'];
                    }
                    User::where('user_id',$val2['user_id'])->update(['count'=>$nums]);
                }
            }
            self::save(['p_status'=>1,'isend'=>1],['id'=>$arr[1]['id']]);
            self::save(['p_status'=>0,'isend'=>1],['id'=>$arr[0]['id']]);
        }else{
            if($type==1){
                if($arr[0]['pktime']>$arr[1]['pktime']){
                    $count=$arr[0]['user'][0]['count'];
                    $point=$arr[0]['user'][0]['points'];
                    $count2=$arr[1]['user'][0]['count'];
                    $user['count']=$count+1;
                    if($count2>0){
                        $user2['count']=$count2-1;
                    }else{
                        $user2['count']=$count2;
                    }
                    $user['points']=$point+$arr[0]['point'];
                    self::save(['p_status'=>1],['id'=>$arr[0]['id']]);
                    self::save(['p_status'=>0],['id'=>$arr[1]['id']]);
                    $users->save($user,['user_id'=>(int)$arr[0]['user_id']]);
                    $users->save($user2,['user_id'=>(int)$arr[1]['user_id']]);
                }elseif($arr[0]['pktime']<$arr[1]['pktime']){
                    $count=$arr[1]['user'][0]['count'];
                    $point=$arr[1]['user'][0]['points'];
                    $count2=$arr[0]['user'][0]['count'];
                    $user['count']=$count+1;
                    if($count2>0){
                        $user2['count']=$count2-1;
                    }else{
                        $user2['count']=$count2;
                    }
                    $user['points']=$point+$arr[1]['point'];
                    self::save(['p_status'=>1,'isend'=>1],['id'=>$arr[1]['id']]);
                    self::save(['p_status'=>0,'isend'=>1],['id'=>$arr[0]['id']]);
                    $users->save($user,['user_id'=>(int)$arr[1]['user_id']]);
                    $users->save($user2,['user_id'=>(int)$arr[0]['user_id']]);
                }else{
                    self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[1]['id']]);
                    self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[0]['id']]);
                }
            }else{
                self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[0]['id']]);
                self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[1]['id']]);
            }

        }
        return $data=1;
//        return $arr;
    }
    //用户得分
    // public function danList($uid,$room_id,$type){
    //    if($type==1){
    //      Points::where('room_id',$room)->where('user_id',$uid)->find()
    //    }else{

    //    }
    // }

}