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
    public function staff(){
        return $this->hasMany('BeescrmAppPizzaStaff','staff_id','staff_id');
    }
//计算房间内有多少人
    public function countNum($room_id,$type){
        $data=self::where('room_id',$room_id)->select();
        $num=0;
        if($type==1){
            $num+=count($data);
        }else{
            foreach ($data as $v){
                $arr=explode(",",$v['staff_id']);
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
                $str=$res[0]['staff_id'].",".$res[1]['staff_id'];
                $arr=explode(",",$str);
                if(in_array($uid,$arr)){
                    $data=1;
                }else{
                    $data=0;
                }
            }else{
                $str=$res[0]['staff_id'];
                if($type==1){
                    if($uid==(int)$str){
                        $data=1;
                    }else{
                        $data=0;
                    }
                }else{
                    $str=$res[0]['staff_id'];
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
            $data=self::insertGetId(['room_id'=>$room_id,'staff_id'=>$uid,'time'=>time()]);
        }else{
            $res=self::where('room_id',$room_id)->select();
            $arr=explode(",",$res[0]['staff_id']);
            $num=count($arr);
            if($num!=2){
                $id=$res[0]['staff_id'].','.$uid;
                $data=self::save(['staff_id'=>$id],['id'=>$res[0]['id']]);
            }else{
                if(count($res)==2){
                    $arr2=explode(",",$res[1]['staff_id']);
                    $num2=count($arr2);
                    if($num2!=2){
                        $id=$res[1]['staff_id'].','.$uid;
                        $data=self::save(['staff_id'=>$id],['id'=>$res[1]['id']]);
                    }
                }else{
                    $data=self::insertGetId(['room_id'=>$room_id,'staff_id'=>$uid,'time'=>time()]);
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
            $res=self::with('staff')->where('room_id',$room_id)->select();
            $room=Room::where('room_id',$room_id)->find();
            $data['users']=$res;
            $data['home_id']=$room['staff_id'];
        }else{
            $arr=self::where('room_id',$room_id)->select();
            $num=count($arr);//有几个人房间
            if($num==1){
                $uids=explode(",",$arr[0]['staff_id']);
                foreach ($uids as $v){
                    $user[]=BeescrmAppPizzaStaff::where('staff_id',$v)->find();
                }
                $data['users']=$arr;
                $data['users'][0]['staff']=$user;
            }else{
                $uids=explode(",",$arr[0]['staff_id']);
                if(in_array($uid,$uids)){
                    foreach ($uids as $v){
                        $user[]=BeescrmAppPizzaStaff::where('staff_id',$v)->find();
                    }
                    $uids2=explode(",",$arr[1]['staff_id']);
                    foreach ($uids2 as $k){
                        $user2[]=BeescrmAppPizzaStaff::where('staff_id',$k)->find();
                    }
                    $data['users']=$arr;
                    $data['users'][0]['staff']=$user;
                    $data['users'][1]['staff']=$user2;
                }else{
                    foreach ($uids as $v){
                        $user[]=BeescrmAppPizzaStaff::where('staff_id',$v)->find();
                    }
                    $uids2=explode(",",$arr[1]['staff_id']);
                    foreach ($uids2 as $k){
                        $user2[]=BeescrmAppPizzaStaff::where('staff_id',$k)->find();
                    }
                    $data['users'][0]=$arr[1];
                    $data['users'][1]=$arr[0];
                    $data['users'][0]['staff']=$user2;
                    $data['users'][1]['staff']=$user;
                }

            }
            $room=Room::where('room_id',$room_id)->find();
            $data['home_id']=$room['staff_id'];

        }
        return $data;
    }
    //在线答题人数
    public  function  onlineAn(){
        $list=self::where('isend',0)->select();
        $num=[];
        $num['num']=0;
        $num['nums']=0;
        if($list){
            foreach ($list as $v){
                $uids=explode(",",$v['staff_id']);
                if(count($uids)>1){
                    $num['nums']+=count($uids);
                }else{
                    $num['num']+=count($uids);
                }

            }
        }else{
            $num['num']+=0;
            $num['nums']+=0;
        }

        return $num;
    }
    //点击挑战
    public function clickChallenge($type,$uid){

//        Room::where()
        $list=self::where('isend',0)->select();
//        $num=0;
        $info=[];
        $arr=[];
        $data=[];
        foreach ($list as $v){
            $uids=explode(",",$v['staff_id']);
//            $num+=count($uids);
            $isnot=in_array(strval($uid),$uids);
            if($isnot){
                $info['room_id']=$v['room_id'];
            }else{
                $info['code']=1;
            }
        }
        if(isset($info['room_id'])){
            $data=$info;
//            $data['code']=1;
        }else{
            if($type==1){
                $arr['counts']=2;
                $arr['type']=1;
                $num=5;
            }else{
                $arr['counts']=4;
                $arr['type']=2;
                $num=10;
            }
//            $num=5;
            $tid=Question::where('type',0)->max('question_id');
            $tmp = range(1,$tid);
            $ran=array_rand($tmp,$num);
            $str=$tmp[$ran[0]];
            for($i=1;$i<$num;$i++){
                $str=$str.",".$tmp[$ran[$i]];
//            $data[]=Topic::with('answer')
//                ->where('topic_id',$tmp[$res[$i]])
//                ->find();
            }
            $arr['topic']=$str;
            $arr['staff_id']=$uid;
            $arr['time']=time();
//        Points::where('user_id',$uid)->where('isend',0)->find();
            $res['room_id']=Room::insertGetId($arr);
            $res['staff_id']=$uid;
            $res['time']=time();
            self::insertGetId($res);
            $data['room_id']=$res['room_id'];
            $data['code']=0;
        }
        return $data;
    }
    //是否开启游戏
    public function startGames($uid,$room_id){
//        $rooms=self::where('room_id',$room_id)->select();
        $room=Room::where('room_id',$room_id)->find();
        if($room['p_type']==1){
            $data=1;
        }else{
            if($room['staff_id']==$uid){
                Room::where('room_id',$room_id)->update(['p_type'=>1]);
                $data=2;
            }else{
                $data=0;
            }
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
//    public function correctNess($room_id,$uid,$correct,$topic_id,$answer_id,$type,$pktime){
//        $list=[];
//        $arr=[];
//        $data=[];
////        $arr['user_id']=$uid;
//        $arr['topic_id']=$topic_id;
//        $arr['answer_id']=$answer_id;
//        $arr['createtime']=time();
//        $arr['room_id']=$room_id;
//        if($correct==1){
//            if($type==1){
//                $res=self::where('room_id',$room_id)->where('user_id',$uid)->find();
//                $list['point']=$res['point']+10;
//                $list['pktime']=$pktime;
//                $arr['status']=1;
//                $arr['user_id']=$res['user_id'];
//                self::save($list,['id'=>$res['id']]);
//                Relation::insert($arr);
//            }else{
//                $res=Points::where('room_id',$room_id)->select();
//                if(in_array($uid,$res[0]['user_id'])){
//                    $list['point']=$res[0]['point']+10;
//                    $list['pktime']=$pktime;
//                    $arr['user_id']=$res[0]['user_id'];
//                    self::save($list,['id'=>$res[0]['id']]);
//                }else{
//                    $list['point']=$res[1]['point']+10;
//                    $list['pktime']=$pktime;
//                    $arr['user_id']=$res[1]['user_id'];
//                    self::save($list,['id'=>$res[1]['id']]);
//                }
//                $arr['status']=2;
//                $arr['points']=10;
//                $data['correct']=1;
//                Relation::insert($arr);
//
//            }
//        }else{
//            if($type==1){
//                $res=self::where('room_id',$room_id)->where('user_id',$uid)->find();
//                if($res['point']>=10){
//                    $list['point']=$res['point']-10;
//                }else{
//                    $list['point']=$res['point'];
//                }
//                $list['pktime']=$pktime;
//                $arr['status']=1;
//                $arr['user_id']=$res['user_id'];
//                self::save($list,['id'=>$res['id']]);
//                Relation::insert($arr);
//            }else{
//                $res=Points::where('room_id',$room_id)->select();
//                if(in_array($uid,$res[0]['user_id'])){
//                    $list['point']=$res[1]['point']+10;
//                    $list['pktime']=$pktime;
//                    $arr['user_id']=$res[1]['user_id'];
//                    self::save($list,['id'=>$res[1]['id']]);
//                    self::save(['pktime'=>$pktime],['id'=>$res[0]['id']]);
//                }else{
//                    $list['point']=$res[0]['point']+10;
//                    $list['pktime']=$pktime;
//                    $arr['user_id']=$res[0]['user_id'];
//                    self::save($list,['id'=>$res[0]['id']]);
//                    self::save(['pktime'=>$pktime],['id'=>$res[1]['id']]);
//                }
//                $arr['status']=2;
//                $data['correct']=0;
//                Relation::insert($arr);
//            }
//        }
//        return $data;
//    }
    //答题完毕，更新数据
    /*uid 用户id
      * room_id 房间id
      * point pk积分
      * pktime pk时间
      * num 答对题目数量
      *
      * */
    public function updatePoint($uid,$room_id,$point,$pktime,$num,$type){
        if($type==1){
            $res=self::where('room_id',$room_id)->where('staff_id',$uid)->find();
            $arr['point']=$res['point']+$point;
            $arr['pktime']=$res['pktime']+$pktime;
            $arr['p_num']=$res['p_num']+$num;
            $arr['isend']=1;
            $data=self::save($arr,['id'=>$res['id']]);
        }else{
            $res=self::where('room_id',$room_id)->select();
            foreach ($res as $k=>$v){
                if(in_array($uid,explode(",",$v['staff_id']))){
                    $arr['point']=$v['point']+$point;
                    $arr['pktime']=$v['pktime']+$pktime;
                    $arr['p_num']=$v['p_num']+$num;
                    $arr['isend']=1;
                    $data=self::save($arr,['id'=>$v['id']]);
                }
            }
        }

        return $data;
    }

    //结果页
    public function calculate($arr,$type){
//        $user=new User();
        if($arr[0]['point']>$arr[1]['point']){
            foreach ($arr[0]['staff'] as $k=>$v){
//                $points=$v['points']+$arr[0]['point'];
                if($type==1){
                    $info['personal_score']=$v['personal_score']+$arr[0]['point'];
                    $info['pizza_num']=$v['pizza_num']+1;
                }else{
                    $info['group_score']=$v['group_score']+$arr[0]['point'];
                    $info['pizza_num']=$v['pizza_num']+2;
                }
                BeescrmAppPizzaStaff::where('staff_id',$v['staff_id'])->update($info);
//                $data=$user->save(['points'=>$points,'count'=>$count],['user_id'=>$v['user_id']]);
            }
            foreach ($arr[1]['staff'] as $key=>$val){
                if($type==1){
                    if($val['pizza_num']>0){
                        $count2=$val['pizza_num']-1;
                    }else{
                        $count2=$val['pizza_num'];
                    }
                }else{
                    if($val['pizza_num']>0){
                        $count2=$val['pizza_num']-2;
                    }else{
                        $count2=$val['pizza_num'];
                    }
                }
                BeescrmAppPizzaStaff::where('staff_id',$val['staff_id'])->update(['pizza_num'=>$count2]);
//                $data=$user->save(['count'=>$count2],['user_id'=>$val['user_id']]);
            }
            self::save(['p_status'=>1],['id'=>$arr[0]['id']]);
            self::save(['p_status'=>0],['id'=>$arr[1]['id']]);

        }elseif($arr[0]['point']<$arr[1]['point']){
            foreach ($arr[1]['staff'] as $k=>$v){
//                $points=$v['points']+$arr[1]['point'];
                if($type==1){
                    $info['personal_score']=$v['personal_score']+$arr[1]['point'];
                    $info['pizza_num']=$v['pizza_num']+1;
                }else{
                    $info['group_score']=$v['group_score']+$arr[1]['point'];
                    $info['pizza_num']=$v['pizza_num']+2;
                }
                BeescrmAppPizzaStaff::where('staff_id',$v['staff_id'])->update($info);
//                $data=$user->save(['points'=>$points,'count'=>$count],['user_id'=>$v['user_id']]);
            }
            foreach ($arr[0]['staff'] as $key=>$val){
                if($type==1){
                    if($val['pizza_num']>0){
                        $count2=$val['pizza_num']-1;
                    }else{
                        $count2=$val['pizza_num'];
                    }
                }else{
                    if($val['pizza_num']>0){
                        $count2=$val['pizza_num']-2;
                    }else{
                        $count2=$val['pizza_num'];
                    }
                }
                BeescrmAppPizzaStaff::where('staff_id',$val['staff_id'])->update(['pizza_num'=>$count2]);
//                $data=$user->save(['count'=>$count2],['user_id'=>$val['user_id']]);
            }
            self::save(['p_status'=>0],['id'=>$arr[0]['id']]);
            self::save(['p_status'=>1],['id'=>$arr[1]['id']]);
        }else{
            if($arr[0]['pktime']<$arr[1]['pktime']){
                foreach ($arr[0]['staff'] as $k=>$v){
//                    $points=$v['points']+$arr[0]['point'];
                    if($type==1){
                        $info['pizza_num']=$v['pizza_num']+1;
                        $info['personal_score']=$v['personal_score']+$arr[0]['point'];
                    }else{
                        $info['pizza_num']=$v['pizza_num']+2;
                        $info['group_score']=$v['group_score']+$arr[0]['point'];
                    }
                    BeescrmAppPizzaStaff::where('staff_id',$v['staff_id'])->update($info);
//                    $data=$user->save(['points'=>$points,'count'=>$count],['user_id'=>$v['user_id']]);personal_score        group_score
                }
                foreach ($arr[1]['staff'] as $key=>$val){
                    if($type==1){
                        if($val['pizza_num']>0){
                            $count2=$val['pizza_num']-1;
                        }else{
                            $count2=$val['pizza_num'];
                        }
                    }else{
                        if($val['pizza_num']>0){
                            $count2=$val['pizza_num']-2;
                        }else{
                            $count2=$val['pizza_num'];
                        }
                    }
                    BeescrmAppPizzaStaff::where('staff_id',$val['staff_id'])->update(['pizza_num'=>$count2]);
//                    $data=$user->save(['count'=>$count2],['user_id'=>$val['user_id']]);
                }
                self::save(['p_status'=>1],['id'=>$arr[0]['id']]);
                self::save(['p_status'=>0],['id'=>$arr[1]['id']]);
            }elseif($arr[0]['pktime']>$arr[1]['pktime']){
                foreach ($arr[1]['staff'] as $k=>$v){
//                    $points=$v['points']+$arr[1]['point'];personal_score        group_score
                    if($type==1){
                        $info['personal_score']=$v['personal_score']+$arr[1]['point'];
                        $info['pizza_num']=$v['pizza_num']+1;
                    }else{
                        $info['group_score']=$v['group_score']+$arr[1]['point'];
                        $info['pizza_num']=$v['pizza_num']+2;
                    }
                    BeescrmAppPizzaStaff::where('staff_id',$v['staff_id'])->update($info);
//                    $data=$user->save(['points'=>$points,'count'=>$count],['user_id'=>$v['user_id']]);
                }
                foreach ($arr[0]['staff'] as $key=>$val){
                    if($type==1){
                        if($val['pizza_num']>0){
                            $count2=$val['pizza_num']-1;
                        }else{
                            $count2=$val['count'];
                        }
                    }else{
                        if($val['pizza_num']>0){
                            $count2=$val['pizza_num']-2;
                        }else{
                            $count2=$val['pizza_num'];
                        }
                    }
                    BeescrmAppPizzaStaff::where('staff_id',$val['staff_id'])->update(['pizza_num'=>$count2]);
//                    $data=$user->save(['count'=>$count2],['user_id'=>$val['user_id']]);
                }
                self::save(['p_status'=>0],['id'=>$arr[0]['id']]);
                self::save(['p_status'=>1],['id'=>$arr[1]['id']]);
            }else{
              self::save(['p_status'=>2],['id'=>$arr[0]['id']]);
              self::save(['p_status'=>2],['id'=>$arr[1]['id']]);
            }
        }

        return $res=1;
//        $users=new User();
//        if($arr[0]['point']>$arr[1]['point']){
//            if($type==1){
//                $count=$arr[0]['user'][0]['count'];
//                $point=$arr[0]['user'][0]['points'];
//                $count2=$arr[1]['user'][0]['count'];
//                $user['count']=$count+1;
//                if($count2>0){
//                    $user2['count']=$count2-1;
//                }else{
//                    $user2['count']=$count2;
//                }
//                $user['points']=$point+$arr[0]['point'];
//                $users->save($user,['user_id'=>(int)$arr[0]['user_id']]);
//                $users->save($user2,['user_id'=>(int)$arr[1]['user_id']]);
//            }else{
//                foreach ($arr[0]['user'] as $key=>$v){
//                    $poin=$v['points']+$arr[0]['point'];
//                    $coun=$v['count']+2;
//                    User::where('user_id',$v['user_id'])->update(['points'=>$poin,'count'=>$coun]);
////                    $users->save($user1,['user_id'=>$v['user_id']]);
//                }
//                foreach ($arr[1]['user'] as $k=>$val){
//                    if($val['count']>0){
//                        $num=$val['count']-2;
//                        if($num<0){
//                            $nums=0;
//                        }else{
//                            $nums=$num;
//                        }
//                    }else{
//                        $nums=$val['count'];
//                    }
//                    User::where('user_id',$val['user_id'])->update(['count'=>$nums]);
//                }
//            }
//            self::save(['p_status'=>1,'isend'=>1],['id'=>$arr[0]['id']]);
//            self::save(['p_status'=>0,'isend'=>1],['id'=>$arr[1]['id']]);
//        }elseif($arr[0]['point']<$arr[1]['point']){
//            if($type==1){
//                $count=$arr[1]['user'][0]['count'];
//                $point=$arr[1]['user'][0]['points'];
//                $count2=$arr[0]['user'][0]['count'];
//                $user['count']=$count+1;
//                if($count2>0){
//                    $user2['count']=$count2-1;
//                }else{
//                    $user2['count']=$count2;
//                }
//                $user['points']=$point+$arr[1]['point'];
//                $users->save($user,['user_id'=>(int)$arr[1]['user_id']]);
//                $users->save($user2,['user_id'=>(int)$arr[0]['user_id']]);
//            }else{
//                foreach ($arr[0]['user'] as $key2=>$v2){
//                    $poin2=$v2['points']+$arr[0]['point'];
//                    $coun2=$v2['count']+2;
//                    User::where('user_id',$v2['user_id'])->update(['points'=>$poin2,'count'=>$coun2]);
////                    $users->save($user1,['user_id'=>$v['user_id']]);
//                }
//                foreach ($arr[1]['user'] as $k2=>$val2){
//                    if($val2['count']>0){
//                        $num=$val2['count']-2;
//                        if($num<0){
//                            $nums=0;
//                        }else{
//                            $nums=$num;
//                        }
//                    }else{
//                        $nums=$val2['count'];
//                    }
//                    User::where('user_id',$val2['user_id'])->update(['count'=>$nums]);
//                }
//            }
//            self::save(['p_status'=>1,'isend'=>1],['id'=>$arr[1]['id']]);
//            self::save(['p_status'=>0,'isend'=>1],['id'=>$arr[0]['id']]);
//        }else{
//            if($type==1){
//                if($arr[0]['pktime']>$arr[1]['pktime']){
//                    $count=$arr[0]['user'][0]['count'];
//                    $point=$arr[0]['user'][0]['points'];
//                    $count2=$arr[1]['user'][0]['count'];
//                    $user['count']=$count+1;
//                    if($count2>0){
//                        $user2['count']=$count2-1;
//                    }else{
//                        $user2['count']=$count2;
//                    }
//                    $user['points']=$point+$arr[0]['point'];
//                    self::save(['p_status'=>1],['id'=>$arr[0]['id']]);
//                    self::save(['p_status'=>0],['id'=>$arr[1]['id']]);
//                    $users->save($user,['user_id'=>(int)$arr[0]['user_id']]);
//                    $users->save($user2,['user_id'=>(int)$arr[1]['user_id']]);
//                }elseif($arr[0]['pktime']<$arr[1]['pktime']){
//                    $count=$arr[1]['user'][0]['count'];
//                    $point=$arr[1]['user'][0]['points'];
//                    $count2=$arr[0]['user'][0]['count'];
//                    $user['count']=$count+1;
//                    if($count2>0){
//                        $user2['count']=$count2-1;
//                    }else{
//                        $user2['count']=$count2;
//                    }
//                    $user['points']=$point+$arr[1]['point'];
//                    self::save(['p_status'=>1,'isend'=>1],['id'=>$arr[1]['id']]);
//                    self::save(['p_status'=>0,'isend'=>1],['id'=>$arr[0]['id']]);
//                    $users->save($user,['user_id'=>(int)$arr[1]['user_id']]);
//                    $users->save($user2,['user_id'=>(int)$arr[0]['user_id']]);
//                }else{
//                    self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[1]['id']]);
//                    self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[0]['id']]);
//                }
//            }else{
//                self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[0]['id']]);
//                self::save(['p_status'=>2,'isend'=>1],['id'=>$arr[1]['id']]);
//            }
//
//        }
//        return $data=1;
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