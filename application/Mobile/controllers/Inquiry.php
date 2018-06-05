<?php

/**
 * User: zhangyuliang
 * desc: 询价单控制器
 * Date: 2017/6/27
 * Time: 15:30
 */
class InquiryController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
        error_reporting(E_ALL);
    }

//返回询价单流水号
    public function getInquiryNoAction() {
        $data['serial_no'] = InquirySerialNo::getInquirySerialNo();
        if (!empty($data)) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('生成流水号错误!');
            $this->jsonReturn();
        }
    }

//添加询价单
    public function addAction() {
        $data = $this->getPut();
        $inquiry = new InquiryModel();
//        $flag = $this->_sendEmail($data['country_bn'], $data);
//        var_dump($flag);
//        die;
        if ($inquiry->checkSerialNo($data['serial_no'])) {
            $data['buyer_id'] = !empty($this->user['buyer_id']) ? $this->user['buyer_id'] : 0;
            $data['inquirer'] = !empty($this->user['user_name']) ? $this->user['user_name'] : '';
            $data['inquirer_email'] = !empty($this->user['user_name']) ? $this->user['email'] : '';
            $results = $inquiry->addInquiry($data);


            if (!$results) {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            } else {

                $this->_sendEmail($data['country_bn'], $data);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($data['serial_no']);
            }
        } else {
            jsonReturn('', MSG::MSG_FAILED, '已存在');
        }
    }

    private function _getemail($country_bn) {
        if (CONFBDP === 'local' || CONFBDP === 'dev' || CONFBDP === 'beta') {
            switch ($country_bn) {
                case 'Thailand':
                    ['email' => 'zhangren@keruigroup.com', 'email1' => 'wangjibin@keruigroup.com', 'name' => '张仁', 'key' => ''];
                case 'Singapore':
                    return ['email' => 'lvxiao@keruigroup.com', 'email1' => 'wangjibin@keruigroup.com', 'name' => '吕潇', 'key' => ''];
                case 'Indonesia':
                    return ['email' => 'liujunfei@keruigroup.com', 'email1' => 'wangjibin@keruigroup.com', 'name' => '刘俊飞', 'key' => ''];
                case 'India':
                    return ['email' => 'jianghongwei@keruigroup.com', 'email1' => 'wangjibin@keruigroup.com', 'name' => '姜红伟', 'key' => ''];
                case 'Myanmar':
                    return ['email' => 'zhongyg@keruigroup.com', 'email1' => 'wangjibin@keruigroup.com', 'name' => '钟银桂', 'key' => ''];
                default :
                    return ['email' => 'wangjibin@keruigroup.com', 'email1' => '', 'name' => '王继宾', 'key' => ''];
            }
        } else {
            switch ($country_bn) {
                case 'Thailand':
                    ['email' => 'thailand@erui.com', 'email1' => 'sales@erui.com', 'name' => 'thailand@erui.com', 'key' => ''];
                case 'Singapore':
                    return ['email' => 'singappre@erui.com', 'email1' => 'sales@erui.com', 'name' => 'singappre@erui.com', 'key' => ''];
                case 'Indonesia':
                    return ['email' => 'hulz@erui.com', 'email1' => 'sales@erui.com', 'name' => '胡立忠', 'key' => ''];

                case 'India':
                    return ['email' => 'yicl@keruigroup.com', 'email1' => 'sales@erui.com', 'name' => '衣春霖', 'key' => ''];
                case 'Myanmar':
                    return ['email' => 'zhangwei07@keruigroup.com', 'email1' => 'sales@erui.com', 'name' => '张伟', 'key' => ''];
                default :
                    return ['email' => 'sales@erui.com', 'email1' => '', 'name' => 'sales@erui.com', 'key' => ''];
            }
        }
    }

// 发送邮件
    private function _sendEmail($country_bn, $email_arr) {

        $data = $this->_getemail($country_bn);
        if (!empty($data['email'])) {
            $arr['email'] = $data['email'];
        } else {
            return false;
        }

        if (!empty($data['email1'])) {
            $arr['email1'] = $data['email1'];
        } else {
            $arr['email1'] = '';
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            return false;
        }
        $config_obj = Yaf_Registry::get("config");
        $config_shop = $config_obj->shop->toArray();
        $email_arr['url'] = $config_shop['url'];
        $email_arr['fastDFSUrl'] = $config_obj->fastDFSUrl;
        $email_arr['name'] = $arr['name'];
        $email_arr['inquiry_time'] = date('Y-m-d');
        if ($email_arr['inquiry_type'] == 2) {
            /* 快速找货 */
            $body = $this->getView()->render('inquiry' . DIRECTORY_SEPARATOR . 'find_email_en.html', $email_arr);
            if (!empty($email_arr['files_attach'])) {
                $Attachment = $this->zipAttachment($email_arr['files_attach'], $config_obj->fastDFSUrl);
            }
            $res = $this->send_Mail($arr['email'], $arr['email1'], 'You have search information from the Erui M station', $body, $email_arr['name'], $Attachment);

            unlink($Attachment);
        } else {
            $body = $this->getView()->render('inquiry' . DIRECTORY_SEPARATOR . 'inquiry_email_en.html', $email_arr);
            $res = $this->send_Mail($arr['email'], $arr['email1'], 'You have new inquiry information from the Erui M station', $body, $email_arr['name']);
        }
        if ($res['code'] == 1) {
            return true;
        } else {

            return false;
        }
    }

    function zipAttachment($attachs, $fastDFSUrl) {
        $date = uniqid('Attachment', true);
        $tmpDir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        rmdir($tmpDir);
        $dirName = $tmpDir . $date;
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }

        foreach ($attachs as $attach) {
            $data = file_get_contents($fastDFSUrl . '/' . $attach['attach_url']);
            $attach_name = pathinfo($attach['attach_name'], PATHINFO_FILENAME);
            $attach_extension = pathinfo($attach['attach_url'], PATHINFO_EXTENSION);
            file_put_contents($dirName . DS . $attach_name . '.' . $attach_extension, $data);
        }
        ZipHelper::zipDir($dirName, $dirName . '.zip');
        ZipHelper::removeDir($dirName);
        return $dirName . '.zip';
    }

    function send_Mail($to, $cc, $title, $body, $name = null, $Attachment = null) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP(); // 使用SMTP
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->mail->toArray();
        try {
            $mail->CharSet = "UTF-8"; //设定邮件编码
            $mail->Host = $config_db['host']; // SMTP server
            $mail->SMTPDebug = 1;                     // 启用SMTP调试 1 = errors  2 =  messages
            $mail->SMTPAuth = true;                  // 服务器需要验证
            $mail->Port = $config_db['port'];    //默认端口
            $mail->Username = $config_db['username']; //SMTP服务器的用户帐号
            $mail->Password = $config_db['password'];        //SMTP服务器的用户密码
            $mail->AddAddress($to, $name); //收件人如果多人发送循环执行AddAddress()方法即可 还有一个方法时清除收件人邮箱ClearAddresses()

            if (!empty($cc)) {
                $mail->AddCC($cc);
            }
            $mail->SetFrom($config_db['setfrom'], 'ERUI'); //发件人的邮箱
            if (!empty($Attachment)) {
                $mail->AddAttachment($Attachment);      // 添加附件,如果有多个附件则重复执行该方法
            }
            $mail->Subject = $title;
            //以下是邮件内容
            $mail->Body = $body;
            $mail->IsHTML(true);

            //$body = file_get_contents('tpl.html'); //获取html网页内容
            //$mail->MsgHTML(str_replace('\\','',$body));
            $mail->Send();
            return ['code' => 1];
        } catch (phpmailerException $e) {
            return ['code' => -1, 'msg' => $e->errorMessage()];
        } catch (Exception $e) {
            return ['code' => -1, 'msg' => $e->errorMessage()];
        }
    }

//询价单列表
    public function getListAction() {
        $inquiry = new InquiryModel();
        $item = new InquiryItemModel();
        $where = $this->getPut();
        $where['buyer_id'] = $this->user['buyer_id'];
        $results = $inquiry->getlist($where);

        foreach ($results['data'] as $key => $val) {
            $test['inquiry_id'] = $val['id'];
            $results['data'][$key]['quantity'] = $item->getSkusCount($test);    //sku数量总和
//$results['data'][$key]['quantity'] = $item->getCount($test);      //sku下商品件数数量总和
        }

        $this->jsonReturn($results);
    }

//询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $where = $this->getPut();

        $results = $inquiry->getInfo($where);

        if (isset($results['data'])) {
            $data = $results['data'];
            $this->_setAgent($data);
            $results['data'] = $data;
        }

        $this->jsonReturn($results);
    }

//询单联系人信息
    public function getContactInfoAction() {
        $inquiry = new InquiryContactModel();
        $where = $this->getPut();

        $results = $inquiry->getInfo($where);

        if (!$results) {
            $buyer_account_model = new BuyerAccountModel();
            $data['buyer_id'] = $this->user['buyer_id'];
            $account_info = $buyer_account_model->getinfo($data);
            if ($account_info) {
                $arr['name'] = $account_info['show_name'] ? $account_info['show_name'] : $account_info['user_name'];
                $arr['phone'] = $account_info['official_phone'];
                $arr['email'] = $account_info['email'] ? $account_info['email'] : $account_info['official_email'];
                $arr['country_bn'] = $account_info['country_bn'];
                $arr['city_bn'] = $account_info['city'];
                $arr['addr'] = $account_info['address'];
                $arr['company'] = $account_info['name'];
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        } else {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($results);
        }
    }

//附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = $this->getPut();

        $results = $attach->getlist($where);
//var_dump($data);die;
        $this->jsonReturn($results);
    }

//明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where = $this->getPut();
        $results = $Item->getlist($where);

        $this->jsonReturn($results);
    }

    /* id转换为姓名
     * @author  zhongyg
     */

    private function _setAgent(&$arr) {

        if ($arr && $arr['agent_id']) {
            $buyer_model = new EmployeeModel();
            $agent_ids = $arr['agent_id'];

            $usernames = $buyer_model->getUserNamesByUserids($agent_ids, false);
            if ($arr['agent_id'] && isset($usernames[$arr['agent_id']])) {
                $arr['agent'] = $usernames[$arr['agent_id']]['name'];
                $arr['agent_email'] = strval($usernames[$arr['agent_id']]['email']);
            } else {
                $arr['agent'] = '';
                $arr['agent_email'] = '';
            }
        } else {
            $arr['agent'] = '';
            $arr['agent_email'] = '';
        }
    }

}
