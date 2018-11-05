<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/14
 * Time: 10:31
 */

namespace app\push\model;


use think\Model;

class Topic extends Model
{
    public function answer()
    {
        return $this->hasMany('Answer','topic_id','topic_id');
    }
}