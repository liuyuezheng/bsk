<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 16:43
 */

namespace app\push\model;

use app\library\ApiException;
use think\Cache;
use think\Db;
use think\Exception;
use think\Request;

class UserToken
{
    public function getToken($username,$password)
    {
        //检查该用户是否存在
        $user = Db::table('user')->where('username','=',$username)
            ->where('user_id','=',$password)
            ->find();
        if (!$user)
        {
            throw new ApiException(['msg' =>'账户或id错误']);
        }
        return $this->grantToken($user);
    }

    private function grantToken($user)
    {
        $cachedValue = $this->prepareCachedValue($user);
        $token = $this->saveToCache($cachedValue);
        return $token;
    }

    private function saveToCache($cachedValue){
        //32个字符
        $randChars = getRandChar(32);
        //用三组加密
        $timestamp = time();
        $key = md5($randChars.$timestamp);

        $value = json_encode($cachedValue);

        $request = cache($key, $value);
        if (!$request) {
            throw new ApiException([
                'msg' => '服务器缓存异常'
            ]);
        }
        return $key;
    }

    private function prepareCachedValue($user){
        $cachedValue['uid'] = $user['user_id'];
        return $cachedValue;
    }

    public static function generateToken(){
        //32个字符
        $randChars = getRandChar(32);
        //用三组加密
        $timestamp = time();
        return md5($randChars.$timestamp);

    }

    public static function getCurrentTokenVar($key){
        $token = Request::instance()
            ->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new ApiException(['msg' => '登录过期']);
        }
        else{
            if (!is_array($vars)) {
                $vars = json_decode($vars,true);
            }
            if (array_key_exists($key,$vars)){
                return $vars[$key];
            }
            else{
                throw new ApiException(['msg' =>'尝试获取得Token变量并不存在']);
            }
        }
    }

    public static function getCurrentUid(){
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }
}