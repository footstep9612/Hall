<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/16
 * Time: 18:50
 */
class Error{
    const SUCCESS = 0;

    private $message = array(
        'SUCCESS' => '成功',
    );
    public static function getMessage($code){
        return 'error';
    }
}