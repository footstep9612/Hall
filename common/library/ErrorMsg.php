<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/16
 * Time: 18:50
 */
class ErrorMsg {
    /**
     * 系统级错误
     */
    const FAILED = 0;
    const SUCCESS = 200;
    const EXIST =100;
    const ERROR_REQUEST_MATHOD = 302;

    /**
     * 产品级错误
     */
    const ERROR_PARAM = 1000;
    const NOTNULL_SPU = 1001;
    const NOTNULL_MCAT = 1002;
    const NOTNULL_NAME = 1003;
    const NOTNULL_SHOWNAME = 1004;
    const NOTNULL_LANG = 1005;
    const NOTNULL_SCAT = 1006;
    const WRONG_SPU = 1101;
    const WRONG_MCAT = 1102;
    const WRONG_LANG = 1105;
    const WRONG_SCAT = 1106;
    const WRONG_STATUS = 1107;


    /**
     * 错误信息映射
     */
    private static $message = array(
        'zh' => array(
            '0' => '失败',
            '1' => '成功',
            '100' => '已经存在',
            '302' => '请求方法有误',

            /**
             * 产品级错误
             */
            '1000' => '参数错误',
            '1001' => 'SPU不能为空',
            '1002' => '物料分类不能为空',
            '1003' => '名称不能为空',
            '1004' => '展示名称不能为空',
            '1005' => '语言不能为空',
            '1006' => '展示分类不能为空',
            '1101' => 'spu有误',
            '1102' => '物料分类有误',
            '1105' => '语言有误',
            '1106' => '展示分类有误',
            '1107' => '状态有误',

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
