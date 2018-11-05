<?php
/**
 * Created by PhpStorm.
 * User: #ZhangQiang
 * Date: 2018/8/23
 * Time: 14:43
 */

namespace app\library;


use Exception;
use think\exception\Handle;

class ExceptionHandle extends Handle
{
    public function render(Exception $e)
    {
        // 在生产环境下返回code信息
        if (!\think\Config::get('app_debug'))
        {
            $statuscode = $code = 500;
            $msg = 'An error occurred';
            // 验证异常
            if ($e instanceof \think\exception\ValidateException)
            {
                $code = 0;
                $statuscode = 404;
                $msg = $e->getError();
            }
            // Http异常
            if ($e instanceof \think\exception\HttpException)
            {
                $statuscode = $code = $e->getStatusCode();
            }
            //Api异常
            if ($e instanceof ApiException)
            {
                $code = 0;
                $statuscode = 404;
                $msg = $e->msg;
            }
            return json(['code' => $code, 'msg' => $msg, 'time' => time(), 'data' => null], $statuscode);
        }
        else{
            //Api异常
            if ($e instanceof ApiException)
            {
                $code = 0;
                $statuscode = 404;
                $msg = $e->msg;
                return json(['code' => $code, 'msg' => $msg, 'time' => time(), 'data' => null], $statuscode);
            }

        }

        //其它此交由系统处理
        return parent::render($e);
    }
}