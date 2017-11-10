<?php

Trait MailHelper
{

    /**
     * 发送短信
     * @param $data
     * @return string
     * @author 买买提
     */
    public static function sendSms($data)
    {
        $url = Yaf_Application::app()->getConfig()->smsUrl;
        return  Curl::postJson($url, json_encode($data));
    }

}