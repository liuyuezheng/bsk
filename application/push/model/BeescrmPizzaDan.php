<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/17
 * Time: 11:03
 */

namespace app\push\model;


use think\Model;

class BeescrmPizzaDan extends Model
{
//判断用户段位
    public function iddan($pizza_num){
        $res=self::where('min_count','<=',$pizza_num)->where('max_count','>=',$pizza_num)->find();
        if($res){
            $data=$res['dan_id'];
        }else{
            $data=0;
        }
        return $data;
    }
}