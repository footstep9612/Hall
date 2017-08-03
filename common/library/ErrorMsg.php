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
    const EXIST =100;
    const ERROR_REQUEST_MATHOD = 403;
    const ERROR_PARAM = 1000;

    const NOTNULL_SPU = 1001;
    const WRONG_MCAT = 1002;
    const NOTNULL_NAME = 1003;
    const NOTNULL_SHOWNAME = 1004;
    const WRONG_LANG = 1005;

    //错误信息映射
    private static $message = array(
        'zh' => array(
            '0' => '失败',
            '1' => '成功',
            '100' => '已经存在',
            '1000' => '参数错误',
            '403' => '请求方法有误',

            /**
             * 产品级错误
             */
            '1001' => 'SPU不能为空',
            '1002' => '物料分类有误',
            '1003' => '名称不能为空',
            '1004' => '展示名称',
            '1005' => '语言有误',

            /**
             * 买买提定义
             */
            '-2101' => '非法请求', //BadRequest
            '-2102' => '数据表没有数据', //NoData
            '-2103' => '缺少报价单号',//
            '-2104' => '询价单模板文件不存在'// No template file
        ),
        'en' => array(

        ),
    );

    /**
     * 返回错误信息
     * @param number $code 错误码
     * @param string $msg  自定义错误信息
     * @param string $lang 语言(默认中文)
     */
    public static function getMessage($code = '1', $msg = '',$lang='zh') {
        $msg = $msg ? $msg : (isset(self::$message[$lang][$code]) ? self::$message[$lang][$code] : '');
        return $msg;
    }

}
