<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class BuyerController extends PublicController {

    public function __init() {
        parent::init();
    }
    //获取用户的角色
    public function getUserRole(){
        $config = \Yaf_Application::app()->getConfig();
        $ssoServer=$config['ssoServer'];
        $token=$_COOKIE['eruitoken'];
        $opt = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-Type: application/json\r\n" .
                    "Cookie: ".$_COOKIE."\r\n",
                'content' =>json_encode(array('token'=>$token))

            )
        );
        $context = stream_context_create($opt);
        $json = file_get_contents($ssoServer,false,$context);
        $info=json_decode($json,true);

        $arr['role']=$info['role_no'];
        if(!empty($info['country_bn'])){
            $countryArr=[];
            foreach($info['country_bn'] as $k => $v){
                $countryArr[]="'".$v."'";
            }
            $countryStr=implode(',',$countryArr);
        }
        $arr['country']=$countryStr;
        return $arr;
    }
    /*
     * 用户列表
     * */

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['lang'])) {    //en/zh 王帅-客户等级
            $where['lang'] = $data['lang'];
        }
        if (!empty($data['name'])) {    //客户名称=公司名称
            $where['name'] = $data['name'];
        }
        $country_model = new CountryModel();
        if (!empty($data['country_bn'])) {  //国家
            $pieces = explode(",", $data['country_bn']);
            for ($i = 0; $i < count($pieces); $i++) {
                $where['country_bn'] = $where['country_bn'] . "'" . $country_model->escapeString($pieces[$i]) . "',";
            }
            $where['country_bn'] = rtrim($where['country_bn'], ",");
        }
        if (!empty($data['country_name'])) {

            $country_name = trim($data['country_name']);

            $country_bns = $country_model->getBnByName($country_name);

            if ($country_bns) {

                foreach ($country_bns as $country_bn) {
                    $where['country_bns'] = $where['country_bns'] . '\'' . $country_model->escapeString($country_bn) . '\',';
                }
                $where['country_bns'] = rtrim($where['country_bns'], ',');
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = "";
                $datajson['message'] = '数据为空!';
            }
        }
        if (!empty($data['area_bn'])) {
            $where['area_bn'] = $country_model->escapeString($data['area_bn']);
        }
        if (!empty($data['created_by'])) {  //创建人X
            $where['created_by'] = $country_model->escapeString($data['created_by']);
        }
        if (!empty($data['agent_id'])) {    //经办人
            $where['agent_id'] = $country_model->escapeString($data['agent_id']);
        }
        if (!empty($data['buyer_no'])) {       //客户编码
            $where['buyer_no'] = $country_model->escapeString($data['buyer_no']);
        }
        if (!empty($data['buyer_code'])) {  //客户代码
            $where['buyer_code'] = $country_model->escapeString($data['buyer_code']);
        }
        if (!empty($data['official_phone'])) {
            $where['official_phone'] = $country_model->escapeString($data['official_phone']);
        }
        if (!empty($data['status'])) {  //客户状态
            $where['status'] = $country_model->escapeString($data['status']);
        }
        if (!empty($data['employee_name'])) {
            $where['employee_name'] = $country_model->escapeString($data['employee_name']);
        }
        if (!empty($data['user_name'])) {
            $where['user_name'] = $country_model->escapeString($data['user_name']);
        }
        if (!empty($data['source'])) {  //客户来源
            $where['source'] = $country_model->escapeString($data['source']);
        }
        if (!empty($data['checked_at_start'])) {    //审核分配市场经办人时间
            $where['checked_at_start'] = $data['checked_at_start'];
        }
        if (!empty($data['checked_at_end'])) {
            $where['checked_at_end'] = $data['checked_at_end'];
        }
        if (!empty($data['created_at_end'])) {  //客户创建时间end
            $where['created_at_end'] = $data['created_at_end'];
        }
        if (!empty($data['created_at_start'])) {
            $where['created_at_start'] = $data['created_at_start'];
        }
        if (!empty($data['credit_checked_at_start'])) {
            $where['credit_checked_at_start'] = $data['credit_checked_at_start'];
        }
        if (!empty($data['credit_checked_at_end'])) {
            $where['credit_checked_at_end'] = $data['credit_checked_at_end'];
        }
        if (!empty($data['approved_at_start'])) {
            $where['approved_at_start'] = $data['approved_at_start'];
        }
        if (!empty($data['approved_at_end'])) {
            $where['approved_at_end'] = $data['approved_at_end'];
        }
        if (!empty($data['min_percent'])) { //信息完整度小-wangs
            $where['min_percent'] = $data['min_percent'];
        }
        if (!empty($data['max_percent'])) { //信息完整度大
            $where['max_percent'] = $data['max_percent'];
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = $data['pageSize'];
        }
        if (!empty($data['currentPage'])) {
            $where['page'] = ($data['currentPage'] - 1) * $where['num'];
        }
        if (!empty($data['rows'])) {
            $where['num'] = $data['rows'];
        }
        if (!empty($data['page'])) {
            $where['page'] = ($data['page'] - 1) * $where['num'];
        }
        if (!empty($data['credit_checked_name'])) {
            $where['credit_checked_name'] = $country_model->escapeString($data['credit_checked_name']);
        }
        if (!empty($data['line_of_credit_min'])) {
            $where['line_of_credit_min'] = $country_model->escapeString($data['line_of_credit_min']);
        }
        if (!empty($data['line_of_credit_max'])) {
            $where['line_of_credit_max'] = $country_model->escapeString($data['line_of_credit_max']);
        }
        if (!empty($data['credit_status'])) {
            $where['credit_status'] = $country_model->escapeString($data['credit_status']);
        }
        if (!empty($data['credit_status'])) {
            $where['credit_status'] = $country_model->escapeString($data['credit_status']);
        }
        if (!empty($data['create_information_buyer_name'])) {   //客户档案新建,选择客户名称
            $where['create_information_buyer_name'] = $data['create_information_buyer_name'];
        }
        $model = new BuyerModel();
        $data = $model->getlist($where);
        $this->_setArea($data['data'], 'area');
        $this->_setCountry($data['data'], 'country');
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['count'] = $data['count'];
            $datajson['data'] = $data['data'];
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /**
     * CRM系统优化客户统计列表
     * wangs-buyerListAction- wangs
     */
    public function buyerListAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['admin']=$this->getUserRole();   //=1市场专员
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $ststisInfo = $model->buyerStatisList($data);
        $dataJson = array(
            'code' => 1,
            'message' => '返回数据',
            'count' => intval($ststisInfo['totalCount']),
            'currentPage' => $ststisInfo['currentPage'],
            'data' => $ststisInfo['info']
        );
        $this->jsonReturn($dataJson);
    }
    //crm-客户列表Excel导出-wangs
    public function exportExcelBuyerListAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['admin']=$this->getUserRole();   //=1市场专员
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $info = $model->buyerStatisList($data,true);
        $arr=array(
            'code'=>1,
            'message'=>'success',
            'data'=>$info
        );
        $this->jsonReturn($arr);
    }

    /*
     * 统计各状态会员数量 jhw-wangs
     * */

    public function buyercountAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['admin']=$this->getUserRole();   //=1市场专员
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $cond = $model->getBuyerStatisListCond($data);  //获取条件
        $totalCount=$model->crmGetBuyerTotal($cond); //获取总条数
        $levelCount=$model->crmGetBuyerLevelCount($cond);    //获取各个等级的总数
        $arr=array(
            "total_count"=>$totalCount,
            "level_count"=>$levelCount
        );
        if ($arr) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 客户审核列表 jhw
     * */

    public function buyercheckedlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerCheckedLogModel();

        if (!empty($data['buyer_id'])) {
            $where['buyer_id'] = $data['buyer_id'];
        } else {
            $datajson['code'] = -103;
            $datajson['data'] = "";
            $datajson['message'] = '会员id缺失!';
        }
        $data = $model->getlist($where);
        if ($data) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户详情
     * */

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $model = new BuyerModel();
        $res = $model->info($data);
        if($res['status'] != 'REJECTED'){
            $res['close_info']='';
        }
        $agent=new BuyerAgentModel();
        $agentRes=$agent->getBuyerAgentList($data['id']);
        $countryModel = new CountryModel();
        $marketAreaModel = new MarketAreaModel();
        $res_arr = [$res];
        $this->_setArea($res_arr, 'area');
        $this->_setCountry($res_arr, 'country',$lang);
        if (!empty($res_arr[0])) {
            $res_arr[0]['agent']=$agentRes;
            $datajson['code'] = 1;
            $datajson['data'] = $res_arr[0];
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * Description of 获取营销区域
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setArea(&$arr, $filed) {
        if ($arr) {
            $marketarea_model = new MarketAreaModel();
            $bns = [];
            foreach ($arr as $key => $val) {
                $bns[] = trim($val[$filed . '_bn']);
            }
            $area_names = $marketarea_model->getNamesBybns($bns);
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($area_names[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $area_names[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr, $filed,$lang='zh') {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val[$filed . '_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, $lang);
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($countrynames[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $countrynames[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * 用户详情
     * */

    public function accountinfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerAccountModel();
        $res = $model->info($data);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    //crm - wangs 过滤手机号
    private function validPhone($phone,$sign='-'){
        $phone=trim($phone,' ');
        $phoneCount=strpos($phone,$sign);
        $phone_arr=str_split($phone);
        $numArr=[];
        foreach($phone_arr as $k => $v){
            if(!is_numeric($v) && $k > $phoneCount){
                unset($v);
            }else{
                $numArr[]=$v;
            }
        }
        $phoneStr=implode('',$numArr);
        return $phoneStr;
    }
    private function getCustomerEnHtml($info,$agent){
        $show_name=$info['show_name'];
        $account_email=$info['account_email'];
        $account_pwd=$info['account_pwd'];
        $agent_email=$agent[0]['email'];
        $agent_tel=$agent[0]['mobile'];

        $html=<<<EOF
    <!doctype html>  
    <html>  
    <head>  
    <title>Welcome to use ERUI!</title>  
    <meta charset="utf-8" />  
    </head>  
    <body>  
    <img src="http://www.erui.com/static/en/image/logo.png" alt="Efficient Supply Chain" height="49" width="159" />
      <!-- logo/工具 -->  
      <div style="border: 1px solid black;">  
        <h1>Hello {$show_name}</h1>  
      </div>  
      <!-- 内容 -->  
      <div style="border: 1px solid black;" align="center">  
        <p>Thank you for registering for <a href="http://www.erui.com">www.erui.com</a></p>  
        <p>Your account and password are:</p>  
        <p>Account:{$account_email}</p>  
        <p>Password:<font color="red">{$account_pwd}</font></p>  
        <p>Click this button to activate your account</p>  
        <p>
        <a href="http://www.erui.com/login/Enlogin/login.html">
<input type=button value="Activate and sign in" style="background:red;color: white;"> 
</a>
</p>    
      </div>  
      <!-- 版权标识 -->  
      <div style="border: 1px solid black;" align="center">  
        <p>如果按钮无法点击，请将以下地址复制到浏览器中打开：<a href="http://www.erui.com">www.erui.com</a></p>  
        <p>Contact us if you have any questions</p>  
        <p>E-mail:{$agent_email}</p>  
        <p>Tel:{$agent_tel}</p>  
      </div>  
    </body>  
    </html> 
    
EOF;
        return $html;
    }
    private function getCustomerHtml($info,$agent){
        $show_name=$info['show_name'];
        $account_email=$info['account_email'];
        $account_pwd=$info['account_pwd'];
        $agent_email=$agent[0]['email'];
        $agent_tel=$agent[0]['mobile'];

        $html=<<<EOF
    <!doctype html>  
    <html>  
    <head>  
    <title>欢迎使用 Erui!</title>  
    <meta charset="utf-8" />  
    </head>  
    <body>  
    <img src="http://www.erui.com/static/en/image/logo.png" alt="Efficient Supply Chain" height="49" width="159" />
      <!-- logo/工具 -->  
      <div style="border: 1px solid black;">  
        <h1>Hello {$show_name}</h1>  
      </div>  
      <!-- 内容 -->  
      <div style="border: 1px solid black;" align="center">  
        <p>感谢注册 <a href="http://www.erui.com">www.erui.com</a></p>  
        <p>您的账号密码为:</p>  
        <p>账号:{$account_email}</p>  
        <p>密码:<font color="red">{$account_pwd}</font></p>  
        <p>请点击以下按钮激活账号：</p>  
        <p>
        <a href="http://www.erui.com/login/Enlogin/login.html">
<input type=button value="激活并登陆" style="background:red;color: white;"> 
</a>
</p>    
      </div>  
      <!-- 版权标识 -->  
      <div style="border: 1px solid black;" align="center">  
        <p>如果按钮无法点击，请将以下地址复制到浏览器中打开：<a href="http://www.erui.com">www.erui.com</a></p>  
        <p>您遇到任何问题请联系</p>  
        <p>联系人:{$agent_email}</p>  
        <p>电话:{$agent_tel}</p>  
      </div>  
    </body>  
    </html> 
    
EOF;
        return $html;
    }
    private function getAgentHtml($info,$agent){
        $company_name=$info['company_name'];
        $show_name=$info['show_name'];
        $account_email=$info['account_email'];
        $account_pwd=$info['account_pwd'];
        $agent_email=$agent['email'];
        $agent_tel=$agent['mobile'];
        $agent_name=$agent['name'];

        $html=<<<EOF
    <!doctype html>  
    <html>  
    <head>  
    <title>客户邮箱账号安全</title>  
    <meta charset="utf-8" />  
    </head>  
    <body>  
    <img src="http://www.erui.com/static/en/image/logo.png" alt="Efficient Supply Chain" height="49" width="159" />
      <!-- logo/工具 -->  
      <div style="border: 1px solid black;">  
        <h1>Hello {$agent_name}</h1>  
      </div>  
      <!-- 内容 -->  
      <div style="border: 1px solid black;" align="center">  
        <p>客户:{$company_name} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  负责人: {$show_name}</p>
        <p>账号:{$account_email}</p>  
        <p>密码:<font color="red">{$account_pwd}</font></p>  
        <p>请通知客户激活账号！</div>   
    </body>  
    </html> 
    
EOF;
        return $html;
    }
    private function getAgentEnHtml($info,$agent){
        $company_name=$info['company_name'];
        $show_name=$info['show_name'];
        $account_email=$info['account_email'];
        $account_pwd=$info['account_pwd'];
        $agent_email=$agent['email'];
        $agent_tel=$agent['mobile'];
        $agent_name=$agent['name'];

        $html=<<<EOF
    <!doctype html>  
    <html>  
    <head>  
    <title>Account security of customer mailbox</title>  
    <meta charset="utf-8" />  
    </head>  
    <body>  
    <img src="http://www.erui.com/static/en/image/logo.png" alt="Efficient Supply Chain" height="49" width="159" />
      <!-- logo/工具 -->  
      <div style="border: 1px solid black;">  
        <h1>Hello {$agent_name}</h1>  
      </div>  
      <!-- 内容 -->  
      <div style="border: 1px solid black;" align="center">  
        <p>Account and password for your new customer are as follows</p>
        <p>Customer:{$company_name} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Contact : {$show_name}</p>
        <p>Account :{$account_email}</p>  
        <p>Password :<font color="red">{$account_pwd}</font></p>  
        <p>Please inform the customer of activating his account ！</div>   
    </body>  
    </html> 
    
EOF;
        return $html;
    }
    //crm -发送通知邮件-wangs
    private function postSentEmail($email,$title,$body){
        $url='http://msg.erui.com/api/email/plain/';
        $arr=array(
            "to"=>"['$email']",
            "title"=>$title,
            "content"=>$body,
            "groupSending"=>1,
            "useType"=>'noticeEmail'
        );
        $opt = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-Type: application/json\r\n" .
                    "Cookie: ".$_COOKIE."\r\n",
                'content' =>json_encode($arr)
            )
        );
        $context = stream_context_create($opt);
        $json = file_get_contents($url,false,$context);
        $info=json_decode($json,true);
        return $info['code'];
    }
    public function noticeEmailAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        if(empty($data['buyer_id'])){
            jsonReturn('', 0, L('param_error'));
        }
        $BuyerAccount=new BuyerAccountModel();
        $info=$BuyerAccount->setPwdEmail($data['buyer_id']);    //客户和经办人信息
        if($lang=='zh'){
            $customer=$this->getCustomerHtml($info['customer'],$info['agent_info']);    //发给客户模板
            $code=$this->postSentEmail($info['customer']['account_email'],'欢迎使用 Erui!',$customer); //发送给客户
            $sent=[$code];
            foreach($info['agent_info'] as $k => $v){
                $agent=$this->getAgentHtml($info['customer'],$v);    //发给经办人模板
                $sent[]=$this->postSentEmail($v['email'],'客户邮箱账号安全',$agent); //发送给经办人
            }
        }else{
            $customer=$this->getCustomerEnHtml($info['customer'],$info['agent_info']);    //发给客户模板
            $code=$this->postSentEmail($info['customer']['account_email'],'Welcome to use ERUI !',$customer); //发送给客户
            $sent=[$code];
            foreach($info['agent_info'] as $k => $v){
                $agent=$this->getAgentEnHtml($info['customer'],$v);    //发给经办人模板
                $sent[]=$this->postSentEmail($v['email'],'Account security of customer mailbox',$agent); //发送给经办人
            }
        }
        if(count($sent)>0 && in_array(200,$sent)){
            $valid=[];
            foreach($sent as $k => $v){
                if($v==200){
                    $valid[]=$v;
                }
            }
            if(count($valid)==count($sent)){
                $dataJson['code']=1;
                $dataJson['message']='Success';
            }else{
                $dataJson['code']=1;
                $dataJson['message']='Success:'.count($valid).'/'.count($sent);
            }
        }else{
            $dataJson['code']=0;
            $dataJson['message']='Error';
        }
        $this->jsonReturn($dataJson);
    }
    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();

        $model = new BuyerModel();
        $buyer_account_model = new BuyerAccountModel();
        if (!empty($data['email'])) {   //邮箱
            $data['email']=trim($data['email'],' ');
            if (!isEmail($data['email'])) {
                jsonReturn('', -101, L('create_email'));
            }
            $checkEmail=$buyer_account_model->field('email')->where(array('email'=>$data['email'],'deleted_flag'=>'N'))->find();
            if($checkEmail){
                jsonReturn('', -101, L('email_existed'));
            }
            $buyer_account_data['email'] = $data['email'];
            $arr['official_email'] = $data['email'];
            $buyer_contact_data['email'] = $data['email'];
        } else {
            jsonReturn('', -101, L('empty_email'));
        }

        if (!empty($data['name'])) {    //公司名称
            $data['name']=trim($data['name'],' ');
            $checkcompany = $model->where("name='" . $data['name'] . "' AND deleted_flag='N'")->find();
            if($checkcompany){
                jsonReturn('', -103, L('name_existed'));
            }
            $arr['name'] = $data['name'];
        } else {
            jsonReturn('', -101, L('empty_name'));
        }

        if (!empty($data['buyer_code'])) {  //CRM代码
            $data['buyer_code']=trim($data['buyer_code'],' ');
            $checkcrm = $model->where("buyer_code='" . $data['buyer_code'] . "' AND deleted_flag='N'")->find();
            if ($checkcrm) {
                jsonReturn('', -103, L('crm_existed'));
            }
            $arr['buyer_code'] = $data['buyer_code'];
        }

        if (!empty($data['first_name'])) {  //注册人信息姓名-show_name
            $data['first_name']=trim($data['first_name'],' ');
            $arr['first_name'] = $data['first_name'];
            $buyer_account_data['show_name'] = $data['first_name'];
            $buyer_contact_data['name'] = $data['first_name'];
        }

        if (!empty($data['country_bn'])) {     //国家
            $arr['country_bn'] = $data['country_bn'];
        } else {
            jsonReturn('', -101, L('empty_country'));
        }

        if (!empty($data['mobile'])) {  //电话
            $data['mobile']=$this->validPhone($data['mobile']);
            $arr['official_phone'] = $data['mobile'];
            $buyer_contact_data['phone'] = $data['mobile'];
        }
        if (!empty($data['biz_scope'])) {   //经营范围
            $arr['biz_scope'] = trim($data['biz_scope'],' ');
        }
        if (!empty($data['intent_product'])) {  //意向产品
            $arr['intent_product'] = trim($data['intent_product'],' ');
        }
        if (!empty($data['purchase_amount'])) { //预计年采购额
            $arr['purchase_amount'] = trim($data['purchase_amount'],' ');
        }
        if (!empty($data['is_group_crm'])) {
            $arr['is_group_crm'] = $data['is_group_crm'];   //  向集团crm添加数据标识
        }

        // 生成用户编码
        $buyerData = $model->field('buyer_no')->order('id desc')->find();
        if ($buyerData && substr($buyerData['buyer_no'], 1, 8) == date("Ymd")) {
            $no = substr($buyerData['buyer_no'], 9, 6);
            $no++;
        } else {
            $no = 1;
        }
        $new_num = $no + 1000000;
        $real_num = "C" . date("Ymd") . substr($new_num, 1, 6); //生成用户编码end

        $created_by=$this->user['id'];
        $arr['created_by'] = $created_by; //客户信息
        $arr['buyer_no'] = $real_num;   //客户编码

        $id = $model->create_data($arr);    //添加客户信息ok
        if ($id) {
            $time=date('Y-m-d H:i:s');
            //账号
            $buyer_account_data['buyer_id'] = $id;
            $buyer_account_data['created_by'] = $created_by;
            $buyer_account_data['created_at'] = $time;
            $buyer_account_model->add($buyer_account_data);
            //联系人
            $buyer_contact_data['buyer_id'] = $id;
            $buyer_contact_data['created_by'] = $created_by;
            $buyer_contact_data['created_at'] = $time;
            $buyer_contact_model = new BuyercontactModel();
            $buyer_contact_model->add($buyer_contact_data);
            //新建客户,添加市场经办人,默认创建人-wnags  -start
            if(!empty($data['agent'])){
                $createBuyerAgent = $data['agent'];    //创建客户时,添加市场经办人
                $createBuyerAgentArr=explode(',',$createBuyerAgent);
                foreach($createBuyerAgentArr as $k => $v){
                    $createBuyerAgentArrAdd[$k]['buyer_id']=$id;
                    $createBuyerAgentArrAdd[$k]['agent_id']=$v;
                    $createBuyerAgentArrAdd[$k]['created_by']=$created_by;
                    $createBuyerAgentArrAdd[$k]['created_at']=$time;
                }
                $buyerAgent=new BuyerAgentModel();
                $buyerAgent->addAll($createBuyerAgentArrAdd);
            }   //添加市场经办人end

            $countryModel = new CountryModel();
            $country_name=$countryModel->field('name')->where(['bn' => $data['country_bn'], 'lang' => $lang, 'deleted_flag' => 'N'])->find();

            $datajson['code'] = 1;
            $datajson['id'] = $id;
            $datajson['buyer_no'] = $arr['buyer_no'];
            $datajson['buyer_code'] = $data['buyer_code'];
            $datajson['name'] = $data['name'];
            $datajson['country'] = $country_name['name'];
            $datajson['message'] = L('success');
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = L('error');
        }
        $this->jsonReturn($datajson);
    }

    public function agentlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $array['lang']=isset($data['lang'])?$data['lang']:'zh';
        if (!empty($data['buyer_id'])) {
            $array['buyer_id'] = $data['buyer_id'];
        }
        if (!empty($data['agent_id'])) {
            $array['agent_id'] = $data['agent_id'];
        }
        //国家
        $country_model = new CountryModel();
        if (!empty($data['country_bn'])) {
            $pieces = explode(",", $data['country_bn']);
            for ($i = 0; $i < count($pieces); $i++) {
                $array['country_bn'] = $array['country_bn'] . "'" . $country_model->escapeString($pieces[$i]) . "',";
            }
            $array['country_bn'] = rtrim($array['country_bn'], ",");
        }
        $model = new BuyerAgentModel();
        $res = $model->getlist($array);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }
    public function updateagentAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $array['id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "会员id不能为空"));
        }
        if (!empty($data['user_ids'])) {
            $array['user_ids'] = $data['user_ids'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "负责人id不能为空"));
        }
        $array['created_by'] = $this->user['id'];
        $model = new BuyerAgentModel();
        $inquiry_model = new InquiryModel();
        $user_arr = explode(',', $array['user_ids']);
        if ($user_arr[0]) {
            $condition['buyer_id'] = $array['id'];
            //$condition['agent_id'] = $user_arr[0];
            $inquiry_model->setBuyerAgentInfo($condition);
        }
        $res = $model->create_data($array);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }
    //crm 更新客户市场经办人-王帅
    public function crmUpdateAgentAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        $agent = new BuyerAgentModel();
        $res=$agent->crmUpdateAgent($data);
        $buyer=new BuyerModel();
        $buyer->where(array('id'=>$data['id']))->save(array('status'=>'APPROVED'));
        if($res){
            //授信添加市场经办人--更新状态--klp
            $credit_model = new BuyerCreditModel();
            $credit_model->setAgentId($data);
            $datajson['code'] = 1;
            $datajson['message'] = L('success');
        }else{
            $datajson['code'] = 0;
            $datajson['message'] = '失败或缺少参数';
        }
        $this->jsonReturn($datajson);
    }
    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
            $where_account['buyer_id'] = $data['id'];
//            $where_attach['buyer_id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" =>L('param_error')));    //用户id不能为空
        }
        if (!empty($data['name'])) {    //公司名称
            $data['name']=trim($data['name']);
            $buyer=new BuyerModel();
            $existId=$buyer->field('id')->where(array('name'=>$data['name'],'deleted_flag'=>'N'))->find();
            if(!empty($existId['id']) && $existId['id']!=$data['id']){
                $this->jsonReturn(array("code" => "-101", "message" => L('name_existed')));    //该公司名称已存在
            }
            $arr['name'] = $data['name'];
        }else{
            $this->jsonReturn(array("code" => "-101", "message" =>L('empty_name')));    //公司名称不能为空
        }
        $buyer_account_model = new BuyerAccountModel();
        if (!empty($data['email'])) {   //邮箱
            $data['email']=trim($data['email'],' ');
            $account['email'] = $data['email']; //---------------------账号
            $buyer_id = $buyer_account_model->where(['email' => $data['email'],'deleted_flag'=>'N'])->getField('buyer_id');
            if ($buyer_id > 0 && $buyer_id != $data['id']) {
                $this->jsonReturn(array("code" => "-101", "message" =>L('email_existed')));    //该邮箱已经被其他账号使用
            }
        }else{
            $this->jsonReturn(array("code" => "-101", "message" =>L('empty_email')));    //邮箱不能为空
        }
        if (!empty($data['first_name'])) {  //姓名
            $arr['first_name'] = $data['first_name'];
            $account['first_name'] = $data['first_name'];
            $account['show_name'] = $data['first_name'];
        }
        if (!empty($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (!empty($data['province'])) {
            $arr['province'] = $data['province'];
        }
        if (!empty($data['buyer_code'])) {
            $buyerModel=new BuyerModel();
            $buyer=$buyerModel->field('id')->where(array('buyer_code'=>$data['buyer_code'],'deleted_flag'=>'N'))->find();
            if(!empty($buyer) && $buyer['id']!=$data['id']){
                $this->jsonReturn(array("code" => "-101", "message" => L('crm_existed'))); //"客户代码已存在"
            }
            $arr['buyer_code'] = $data['buyer_code'];   //新增CRM编码，张玉良 2017-9-27
        }else{
            $this->jsonReturn(array("code" => "-101", "message" =>L('empty_crm')));    //code不能为空
        }
        if (!empty($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];   //新增CRM编码，张玉良 2017-9-27
        }
        if (!empty($data['country_bn'])) {  //国家
            $arr['country_bn'] = $data['country_bn'];
        }
        if (!empty($data['biz_scope'])) {   //经营范围
            $arr['biz_scope'] = $data['biz_scope'];
        }
        if (!empty($data['intent_product'])) {  //意向产品
            $arr['intent_product'] = $data['intent_product'];
        }
        if (!empty($data['purchase_amount'])) {     //年采购额
            $arr['purchase_amount'] = $data['purchase_amount'];
        }
        if (!empty($data['close_info'])) {     //关闭客户信息备注
            $arr['close_info'] = $data['close_info'];
        }
        if (!empty($data['mobile'])) {
            $data['mobile']=$this->validPhone($data['mobile']);
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['official_phone'])) {
            $data['mobile']=$this->validPhone($data['official_phone']);
            $arr['official_phone'] = $data['mobile'];
        }
        if (!empty($data['buyer_level'])) {
            $arr['buyer_level'] = $data['buyer_level'];
            $arr['level_at'] = date("Y-m-d H:i:s");
        }
        if (!empty($data['type_remarks'])) {
            $arr['type_remarks'] = $data['type_remarks'];
        }
        if (!empty($data['employee_count'])) {
            $arr['employee_count'] = $data['employee_count'];
        }
        if (!empty($data['reg_capital'])) {
            $arr['reg_capital'] = $data['reg_capital'];
        }
        if (!empty($data['reg_capital_cur'])) {
            $arr['reg_capital_cur'] = $data['reg_capital_cur'];
        }
        if (!empty($data['expiry_at'])) {
            $arr['expiry_at'] = $data['expiry_at'];
        }
        if (!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if (!empty($data['area_bn'])) {
            $arr['area_bn'] = $data['area_bn'];
        }
        if (!empty($data['agent'])) {
            $arr['status'] = 'APPROVED';
        }
        if (!empty($data['status'])) {
            if ($data['status'] == 'REJECTED') {
                $arr['status'] = 'REJECTED';
            }else{
                $arr['status'] = 'APPROVED';
            }
        }
        if (!empty($data['address'])) {
            $arr['address'] = $data['address'];
        }
        $model = new BuyerModel();
        $res = $model->update_data($arr, $where);
        if(!empty($data['agent'])){ //crm更新市场经办人-start--------------
            $agentArr['user_ids']=$data['agent'];
            $agentArr['id']=$data['id'];
            $agentArr['created_by']=$this->user['id'];
            $agent=new BuyerAgentModel($agentArr);
            $agent->crmUpdateAgent($agentArr);
        }   //crm 更新市场经办人end----------------------------------------
//        if (!empty($data['password'])) {
//            $account['password_hash'] = $data['password'];
//            // $buyer_account_model->update_data($arr_account, $where_account);
//        }
//        $buyer_attach_model = new BuyerattachModel();
//        if (!empty($data['attach_url'])) {
//            $where_attach['attach_url'] = $data['attach_url'];
//            $buyer_attach_model->update_data($where_attach);
//        }
        //$model = new UserModel();
        if (!empty($account)) {
            $buyer_account_model->where($where_account)->save($account);
        }
//        if (!empty($data['status']) && $res !== false) {
//            if ($data['status'] == 'APPROVED' || $data['status'] == 'REJECTED') {
//                $info = $buyer_account_model->info($where_account);
//                $info_buyer = $model->info($where);
//
//                if ($info['email']) {
//                    if ($data['status'] == 'APPROVED') {
//                        //审核通过邮件
//                        if ($info_buyer['lang']) {
//                            $body = $this->getView()->render('buyer/approved_' . $info_buyer['lang'] . '.html');
//                            send_Mail($info['email'], 'Erui.com', $body, $arr['name']);
//                        }
//                    }
//                    if ($data['status'] == 'REJECTED') {
//                        //驳回邮件
//                        if ($info_buyer['lang']) {
//                            $body = $this->getView()->render('buyer/rejected_' . $info_buyer['lang'] . '.html');
//                            send_Mail($info['email'], 'Erui.com', $body, $arr['name']);
//                        }
//                    }
//                }
//            }
//        }
        if ($res !== false) {
            $datajson['code'] = 1;
            $datajson['message'] = L('success');
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }
    //点击编辑验证准备客户信息的验证-wangs0-参数buyer_id
    public function clickEditCheckAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data['buyer_id'])){
            $this->jsonReturn(array("code" => 0, "message" => L('param_error')));  //请输入正确参数
        }
        $buyerModel=new BuyerModel();
        $res=$buyerModel->clickEditCheck($data['buyer_id']);
        if($res['company']==1){
            $companyJson['code']=2;
            $companyJson['message']=L('name_existed');  //公司名称已存在
        }else{
            $companyJson['code']=1;
            $companyJson['message']=L('name_ok');   //公司名称正常
        }
        if($res['email']==1){
            $emailJson['code']=2;
            $emailJson['message']=L('email_existed');  //邮箱已存在
        }else{
            $emailJson['code']=1;
            $emailJson['message']=L('email_ok');   //邮箱正常
        }
        $arr['company']=$companyJson;
        $arr['email']=$emailJson;
        $this->jsonReturn($arr);
    }
    public function getRoleAction() {
        if ($this->user['id']) {
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        } else {
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }

    /**
     * 客户档案信息管理，创建客户档案-->基本信息
     * wangs
     */
    public function createBuyerInfoAction() {


        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $res = $model->createBuyerBaseInfo($data);          //创建基本信息
        if ($res !== true && $res !== false) {
            $valid = array(
                'code' => 0,
                'message' => $res,
            );
            $this->jsonReturn($valid);
        } elseif ($res === false) {
            $valid = array(
                'code' => 0,
                'message' =>L('error') ,
            );
            $this->jsonReturn($valid);
        }
        $valid = array(
            'code' => 1,
            'message' => L('success')
        );
        $this->jsonReturn($valid);
    }

    /**
     * 客户管理：客户基本信息展示详情
     * wangs
     */
    public function showBuyerInfoAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['lang'] = $this->getLang();
        $model = new BuyerModel();
        $buerInfo = $model->showBuyerBaseInfo($data);
        if (empty($buerInfo)) {
            $dataJson = array(
                'code' => 1,
                'message' => '返回数据',
                'data' => $buerInfo
            );
            $this->jsonReturn($dataJson);
        }
        //获取客户账号
        $account = new BuyerAccountModel();
        $accountInfo = $account->getBuyerAccount($data['buyer_id']);
        $buerInfo['buyer_account'] = $accountInfo['email'];
        //获取服务经理经办人，调用市场经办人方法
        $agent = new BuyerAgentModel();
        $agentInfo = $agent->buyerMarketAgent($data);
        $buerInfo['market_agent_name'] = $agentInfo['info'][0]['name']; //没有数据则为空
        $buerInfo['market_agent_mobile'] = $agentInfo['info'][0]['mobile'];
        //获取财务报表
        $attach = new BuyerattachModel();

        $finance = $attach->showBuyerExistAttach('FINANCE', $data['buyer_id'], $data['created_by']);
        if (!empty($finance)) {
            $buerInfo['finance_attach'] = $finance;
        } else {
            $buerInfo['finance_attach'] = array();
        }
        //公司人员组织架构
        $org_chart = $attach->showBuyerExistAttach('ORGCHART', $data['buyer_id'], $data['created_by']);
        if (!empty($org_chart)) {
            $buerInfo['org_chart'] = $org_chart;
        } else {
            $buerInfo['org_chart'] = array();
        }
        //分析报告
//        $org_chart = $attach->showBuyerExistAttach('REPORT',$data['buyer_id'],$data['created_by']);
//        if(!empty($org_chart)){
//            $buerInfo['report_attach'] = $org_chart;
//        }else{
//            $buerInfo['report_attach'] = array();
//        }
//
        $arr['base_info'] = $buerInfo;
        //获取客户联系人
        $contact = new BuyercontactModel();
        $contactInfo = $contact->showBuyerExistContact($data['buyer_id'], $data['created_by']);
        if (empty($contactInfo)) {    //联系人为空
            $contactInfo = [array(
            'name' => null, //联系人姓名
            'title' => null, //联系人职位
            'role' => null, //角色
            'phone' => null, //联系人电话
            'email' => null, //联系人邮箱
            'hobby' => null, //爱好
            'address' => null, //详细地址
            'experience' => null, //经历
            'social_relations' => null, //社会关系
            'key_concern' => null, //决策主要关注点
            'attitude_kerui' => null, //对科瑞的态度
            'social_habits' => null, //常去社交场所
            'relatives_family' => null, //家庭亲戚相关信息
            )];
        }
        $arr['contact'] = $contactInfo;
        $dataJson = array(
            'code' => 1,
            'message' => '返回数据',
            'data' => $arr
        );
        $this->jsonReturn($dataJson);
    }

    /**
     * 客户管理-附件下载
     * wangs
     */
    public function attachDownloadAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerattachModel();
        $attach = $model->attachDownload($data);
        if ($attach == false) {
            $dataJson = array(
                'code' => 0,
                'message' => '请输入正确信息'
            );
        } else {
            $dataJson = array(
                'code' => 1,
                'message' => '数据下载',
                'data' => $attach
            );
        }
        $this->jsonReturn($dataJson);
    }

    /**
     * 客户管理-客户档案--4页签统计展示
     * wangs
     */
    public function showBuyerStatisAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        //客户信用评价
        $model = new BuyerModel();
        $ststisInfo = $model->showBuyerStatis($data);
        if ($ststisInfo === false) {
            $dataJson = array(
                'code' => 0,
                'message' => '请求缺少规定参数'
            );
            $this->jsonReturn($dataJson);
        }
        //拜访记录
        $visit = new BuyerVisitModel();
        $visitInfo = $visit->singleVisitInfo($data['buyer_id']);
        //客户需求反馈
//        $reply = new BuyerVisitReplyModel();
        $demandInfo = $visit->singleVisitDemandInfo($data['buyer_id']);
        //客户与kr/er业务量
        $order = new OrderModel();
        $orderInfo = $order->statisOrder($data['buyer_id']);
        $inquiry = new InquiryModel();
        $inquiryInfo = $inquiry->statisInquiry($data['buyer_id']);
        //整合数据
        $arr['credit'] = $ststisInfo;
        $arr['visit'] = $visitInfo;
        $arr['demand'] = $demandInfo;
        $arr['order']['count'] = $orderInfo['count'];
        $arr['order']['account'] = $orderInfo['account'];
        $arr['order']['range'] = array('min'=>$orderInfo['min'],'max'=>$orderInfo['max']);
        $arr['inquiry'] = $inquiryInfo;
        $dataJson = array(
            'code' => 1,
            'message' => '返回数据',
            'data' => $arr
        );
        $this->jsonReturn($dataJson);
    }

    /**
     * 添加客户验证输入CRM代码信息
     * wangs
     */
    public function checkBuyerCrmAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $info = $model->checkBuyerCrm($data);
        if (!empty($info)) {
            $dataJson = array(
                'code' => 0,
                'message' => L('crm_existed')
            );
            
            $this->jsonReturn($dataJson);
        }
        //test-start
        else{
            $dataJson = array(
                'code'=>2,
                'message'=>L('Normal_customer') //正常录入客户信息流程
            );
            $this->jsonReturn($dataJson);
        }
        //test-end

        //验证集团CRM存在,则展示数据 生产-start
//        $group = $this->groupCrmCode($data['buyer_code']);
//        if (!empty($group)) {
//            $dataJson = array(
//                'code' => 1,
//                'message' => L('Group_crm'), //集团CRM客户信息
//                'data' => $group
//            );
//        } else {
//            $dataJson = array(
//                'code' => 2,
//                'message' => L('Normal_customer') //正常录入客户信息流程
//            );
//        }
//        $this->jsonReturn($dataJson); //生产-end
    }

    /**
     * @param $code
     * 调用集团crm接口
     * 王帅
     */
    public function groupCrmCode($code) {
        //通过code验证并获取客户信息
        $soap = <<<EOF
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:acc="http://siebel.com/sales/account/">
   <soapenv:Header/>
   <soapenv:Body>
      <acc:QueryAccount>
         <crm_code>{$code}</crm_code>
      </acc:QueryAccount>
   </soapenv:Body>
</soapenv:Envelope>
EOF;
        $opt = array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: text/xml",
                'content' => $soap
            )
        );
        $context = stream_context_create($opt);
//        $url = 'http://172.16.26.152:8088/eai_anon_chs/start.swe?SWEExtSource=AnonWebService&amp;SweExtCmd=Execute';
        $url = 'http://172.16.26.154:7780/eai_anon_chs/start.swe?SWEExtSource=AnonWebService&amp;SweExtCmd=Execute';
        $str = file_get_contents($url, false, $context);  //得到客户crm数据
        $need = strstr($str, '<biz_scope>');
        $need = strstr($need, '</rpc:QueryAccountResponse>', true);
        $xml = '<root>' . $need . '</root>';
        $xmlObj = simplexml_load_string($xml);
        $arr = json_decode(json_encode($xmlObj), true);
        if (empty($arr['crm_code'])) {
            return null;
        }
        if (!empty($arr)) {
            $country = new CountryModel();
            $nameAndCode = $country->getCountryBnCodeByName($arr['country_bn']);
            $arr['country_brief'] = $nameAndCode['bn'];
            $arr['country_code'] = $nameAndCode['int_tel_code'];
        }
        $info = array(
            'official_email' => !empty($arr['email']) ? $arr['email'] : null, //邮箱
            'country_bn' => !empty($arr['country_brief']) ? $arr['country_brief'] : null, //国家简称
            'country_name' => !empty($arr['country_bn']) ? $arr['country_bn'] : null, //国家名称
            'areacode' => !empty($arr['country_code']) ? $arr['country_code'] : null, //国家区号
            'mobile' => !empty($arr['mobile']) ? $arr['mobile'] : null, //区号,电话
            'first_name' => !empty($arr['first_name']) ? $arr['first_name'] : null, //姓名
            'name' => !empty($arr['name']) ? $arr['name'] : null, //公司名称
            'biz_scope' => !empty($arr['biz_scope']) ? $arr['biz_scope'] : null, //经营范围
            'intent_product' => NULL, //意向产品
            'purchase_amount' => NULL //预计年采购额
        );
        return $info;
    }

    /**
     * CRM测试
     */
    public function testCrmAction() {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $info = $model->testCrm($data);
        if (!empty($info)) {
            $dataJson = array(
                'code' => 1,
                'message' => 'CRM返回数据',
                'data' => $info
            );
        } else {
            $dataJson = array(
                'code' => 2,
                'message' => 'CRM正常流程',
                'data' => $info
            );
        }
        $this->jsonReturn($dataJson);
    }

    /**
     * 获取客户类型名称列表
     */
    public function getBuyerTypeListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = isset($data['lang']) ? $data['lang'] : 'zh';
        $type = new BuyerTypeModel();
        $info = $type->buyerNameList($lang);
        $dataJson = array(
            'code' => 1,
            'message' => '客户类型名称列表',
            'data' => $info
        );
        $this->jsonReturn($dataJson);
    }
    /*
     * 客户会员自动升级-wnags
     * */
    public function autoUpgradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $order=new OrderModel();
        $auto=$order->autoUpgradeByOrder($data);
        $dataJson['code'] = 1;
        if($auto=='senior'){
            $dataJson['message'] = '高级';
        }elseif($auto=='general'){
            $dataJson['message'] = '普通';
        }elseif($auto=='void'){
            $dataJson['message'] = '无交易';
        }elseif($auto=='param'){
            $dataJson['code'] = 0;
            $dataJson['message'] = '缺少参数';
        }
        $this->jsonReturn($dataJson);
    }
    protected function testUpgradeAction(){
        set_time_limit(0);
        $model=new BuyerModel();
        $buyer=$model->field('id as buyer_id')->where(array('deleted_flag'=>'N'))->select();
        $order=new OrderModel();
        $arr=[];
        foreach($buyer as $k => $v){
            $auto=$order->autoUpgradeByOrder($v);
            $arr[]=$auto;
        }
        print_r(count($arr));

        print_r($arr);
    }
}
