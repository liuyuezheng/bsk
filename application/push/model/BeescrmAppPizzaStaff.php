<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/10
 * Time: 16:09
 */

namespace app\push\model;


use think\Model;

class BeescrmAppPizzaStaff extends Model
{
       public function dan(){
           return $this->hasMany('BeescrmPizzaDan','dan_id','dan_id');
       }
}