<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/13
 * Time: 16:39
 */

namespace app\push\controller;

use app\api\model\Project_admin;
use app\api\model\Projects;

use app\push\model\UserToken;
use think\Controller;

class Token extends Controller
{
    public function token()
    {
        $username = input('param.username/s','');
        $password = input('param.id/d','');
        $ut = new UserToken();
        $token = $ut->getToken($username,$password);

        $data['token'] = $token;
        return $this->success('登录成功','',$data);
    }

}