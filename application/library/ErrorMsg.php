<?php

/**
 * 错误信息        注：为了错误信息统一管理，已停止此错误信息维护，jsonReturn()方法已改为调取MSG中的信息了。
 * User: linkai
 * Date: 2017/6/13
 * Time: 18:50
 */
class ErrorMsg {

    //状态code
    const FAILED = 0;
    const SUCCESS = 1;

    //错误信息映射
    private static $message = array(
        self::FAILED => '失败',
        self::SUCCESS => '成功',
        /**
         * 买买提定义
         */
        '-2101' => '非法请求', //BadRequest
        '-2102' => '数据表没有数据', //NoData
        '-2103' => '缺少报价单号'//
    );

    //返回错误信息
    public static function getMessage($code = '1', $msg = '') {
        $msg = $msg ? $msg : (isset(self::$message[$code]) ? self::$message[$code] : '');
        return $msg;
    }

}
