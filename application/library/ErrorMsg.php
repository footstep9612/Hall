<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/16
 * Time: 18:50
 */
class ErrorMsg{
    //状态code
    const FAILED = 0;
    const SUCCESS = 1;

    //错误信息映射
    private static $message = array(
        '0' => '失败',
        '1' => '成功',
    );

    //返回错误信息
    public static function getMessage($code='1',$msg=''){
        $msg = $msg ? $msg : (isset(self::$message[$code]) ? self::$message[$code] : '');
        return $msg;
    }
}