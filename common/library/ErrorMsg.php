<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/16
 * Time: 18:50
 */
class ErrorMsg {

    //状态code
    const FAILED = 0;
    const SUCCESS = 1;
    const ERROR_PARAM = 1000;

    //错误信息映射
    private static $message = array(
        '0' => '失败',
        '1' => '成功',

        '1000' => '参数错误',

        /**
         * 买买提定义
         */
        '-2101' => '非法请求', //BadRequest
        '-2102' => '数据表没有数据', //NoData
        '-2103' => '缺少报价单号',//
        '-2104' => '询价单模板文件不存在'// No template file
    );

    //返回错误信息
    public static function getMessage($code = '1', $msg = '') {
        $msg = $msg ? $msg : (isset(self::$message[$code]) ? self::$message[$code] : '');
        return $msg;
    }

}
