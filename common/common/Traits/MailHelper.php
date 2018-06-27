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

    public static function sendEmail($to, $title, $body, $receiver)
    {

        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->mail->toArray();

        try{
            $mail->CharSet = "UTF-8"; //设定邮件编码
            $mail->Host = $config_db['host']; // SMTP server
            $mail->SMTPDebug = 1;                     // 启用SMTP调试 1 = errors  2 =  messages
            $mail->SMTPAuth = true;                  // 服务器需要验证
            $mail->Port = $config_db['port'];    //默认端口
            $mail->Username = $config_db['username']; //SMTP服务器的用户帐号
            $mail->Password = $config_db['password'];        //SMTP服务器的用户密码
            $mail->AddAddress($to, $receiver); //收件人如果多人发送循环执行AddAddress()方法即可 还有一个方法时清除收件人邮箱ClearAddresses()
            $mail->SetFrom($config_db['setfrom'], 'ERUI'); //发件人的邮箱

            $mail->Subject = $title;

            $mail->Body = $body;
            $mail->IsHTML(true);

            $mail->Send();

            return ['code' => 1];

        } catch (phpmailerException $e) {
            return ['code' => -1, 'msg' => $e->errorMessage()];
        }



    }

}