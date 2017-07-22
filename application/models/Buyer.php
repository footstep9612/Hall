<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class BuyerModel extends PublicModel {
    //put your code here
    protected $tableName = 'buyer';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.t_buyer';
//    protected $autoCheckFields = false;
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        $sql =  'SELECT `id`,`serial_no`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .=  '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`status`,`remarks`,`apply_at`,`approved_at`';
        $sql .= ' FROM '.$this->g_table;
        if ( !empty($condition['where']) ){
            $sql .= ' WHERE '.$condition['where'];
        }
        $sql .= ' Order By '.$order;
        if ( $condition['page'] ){
            $sql .= ' LIMIT '.$condition['page'].','.$condition['countPerPage'];
        }
        return $this->query( $sql );
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  string $lang 语言
     * @return mix
     * @author klp
     */
    public function getListCredit($condition) {
        if(empty($condition))
            return false;
        $where = array();
        $current_no = isset($condition['current_no']) ? $condition['current_no'] : 1;
        $pagesize = isset($condition['pagesize']) ? $condition['pagesize'] : 10;     //默认每页10条记录

        //编号
        if (isset($condition['lang']) && !empty($condition['lang'])) {
            $where["lang"] = $condition['lang'];
        }
        //编号
        if (isset($condition['customer_id']) && !empty($condition['customer_id'])) {
            $where["customer_id"] = $condition['customer_id'];
        }
        //审核人
        if (isset($condition['approved_by']) && !empty($condition['approved_by'])) {
            $where["approved_by"] = $condition['approved_by'];
        }
        //公司名称
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where["name"] = $condition['name'];
        }
        //审核状态
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where["status"] = $condition['status'];
        }
        //授信额度(暂无字段,待完善)
        if (isset($condition['credit']) && !empty($condition['credit'])) {
            $where["credit"] = $condition['credit'];
        }
        //信保审核时间段(暂无,待完善)
        if (isset($condition['credit']) && !empty($condition['credit'])) {
            $where["credit"] = $condition['credit'];
        }
        //易瑞审核时间段
        if (isset($condition['approved_start']) && isset($condition['approved_end'])  && !empty($condition['approved_start'])  && !empty($condition['approved_end'])) {
            $where["approved_at"] = array('egt', $condition['approved_start']);
            $where["approved_at"] = array('elt', $condition['approved_end']);
        }
        $field = 'serial_no,customer_id,lang,name,bn,status,apply_at,approved_at';
//        $field .='profile,country,province,city,reg_date,logo,official_website,brand,bank_name,swift_code,bank_address,bank_account,buyer_level,credit_level,finance_level,logi_level,qa_level,steward_level,remarks';
        try {
            $result = $this->field($field)->order("id")->page($current_no, $pagesize)->where($where)->select();
            $data = array();
            if($result){
                foreach($result as $item){
                    //按语言分组
                    $data[$item['lang']] = $item;
                }
            }
            return $data ? true : false;
        } catch (Exception $e) {
            //        $results['code'] = $e->getCode();
            //        $results['message'] = $e->getMessage();
            return false;
        }

    }
    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function Exist($data) {
        $sql =  'SELECT `id`,`serial_no`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .=  '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`status`,`remarks`,`apply_at`,`approved_at`';
        $sql .= ' FROM '.$this->g_table;
        $where = '';
        if ( !empty($data['email']) ){
            $where .= " where email = '" .$data['email']."'";
        }
        if ( !empty($data['mobile']) ){
            if($where){
                $where .= " or mobile = '" .$data['mobile']."'";
            }else{
                $where .= " where mobile = '" .$data['mobile']."'";
            }

        }
        if ( !empty($data['id']) ){
            if($where){
                $where .= " and id = '" .$data['id']."'";
            }else{
                $where .= " where id = '" .$data['id']."'";
            }

        }
        if ( !empty($data['customer_id']) ){
            if($where){
                $where .= " and customer_id = '" .$data['customer_id']."'";
            }else{
                $where .= " where customer_id = '" .$data['customer_id']."'";
            }

        }
        if ( $where){
            $sql .= $where;
        }
        $row = $this->query( $sql );
        return empty($row) ? false : $row;
    }


    public function create_data($create = [])
    {
        if(isset($create['customer_id'])){
            $data['customer_id'] = $create['customer_id'];
        }
        if(isset($create['serial_no'])){
            $data['serial_no'] = $create['serial_no'];
        }
        if(isset($create['lang'])){
            $data['lang'] = $create['lang'];
        }else{
            $data['lang'] = 'en';
        }
        if(isset($create['name'])){
            $data['name'] = $create['name'];
        }
        if(isset($create['bn'])){
            $data['bn'] = $create['bn'];
        }
        if(isset($create['profile'])){
            $data['profile'] = $create['profile'];
        }
        if(isset($create['country'])){
            $data['country'] = $create['country'];
        }
        if(isset($create['province'])){
            $data['province'] = $create['province'];
        }
        if(isset($create['reg_date'])){
            $data['reg_date'] = date('Y-m-d');
        }
        if(isset($create['logo'])){
            $data['logo'] = $create['logo'];
        }
        if(isset($create['official_website'])){
            $data['official_website'] = $create['official_website'];
        }
        if(isset($create['brand'])){
            $data['brand'] = $create['brand'];
        }
        if(isset($create['bank_name'])){
            $data['bank_name'] = $create['bank_name'];
        }
        if(isset($create['swift_code'])){
            $data['swift_code'] = $create['swift_code'];
        }
        if(isset($create['bank_address'])){
            $data['bank_address'] = $create['bank_address'];
        }
        if(isset($create['bank_account'])){
            $data['bank_account'] = $create['bank_account'];
        }
        if(isset($create['buyer_level'])){
            $data['buyer_level'] = $create['buyer_level'];
        }
        if(isset($create['credit_level'])){
            $data['credit_level'] = $create['credit_level'];
        }
        if(isset($create['finance_level'])){
            $data['finance_level'] = $create['finance_level'];
        }
        if(isset($create['logi_level'])){
            $data['logi_level'] = $create['logi_level'];
        }
        if(isset($create['qa_level'])){
            $data['qa_level'] = $create['qa_level'];
        }
        if(isset($create['steward_level'])){
            $data['steward_level'] = $create['steward_level'];
        }
        if(isset($create['remarks'])){
            $data['remarks'] = $create['remarks'];
        }
        $data['apply_at'] = date('Y-m-d H:i:s');
        if(isset($create['approved_at'])){
            $data['approved_at'] = $create['approved_at'];
        }
        try{
            $datajson = $this->create($data);
            $res = $this->add($datajson);
            return $res;
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }
    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author klp
     */
    public function getInfo($data)
    {
        $where=array();
        if(empty($data['customer_id'])) {
            if (!empty($data['email'])) {
                $where['email'] = $data['email'];
            } else {
                jsonReturn('', '-1001', '用户email不可以为空');
            }
            $buyerInfo = $this->where(array("email='".$data['email']."'"))
                              ->field('customer_id,lang,name,bn,country,province,city,official_website,buyer_level')
                              ->find();
        } else{
            $buyerInfo = $this->where(array("customer_id='".$data['customer_id']."'"))
                              ->field('customer_id,lang,name,bn,country,province,city,official_website,buyer_level')
                              ->find();

        }
        if($buyerInfo){
            //通过顾客id查询用户信息
            $buyerAccount = new BuyerAccountModel();
            $userInfo = $buyerAccount->field('email,user_name,mobile,first_name,last_name,status')
                                     ->where(array('customer_id' => $buyerInfo['customer_id']))
                                     ->find();


            //通过顾客id查询用户邮编
            $buyerAddress = new BuyerAddressModel();
            $zipCode = $buyerAddress->field('zipcode,address')
                                    ->where(array('customer_id' => $buyerInfo['customer_id']))
                                    ->find();

            $buyerInfo['email'] = $userInfo['email'];
            $buyerInfo['user_name'] = $userInfo['user_name'];
            $buyerInfo['mobile'] = $userInfo['mobile'];
            $buyerInfo['first_name'] = $userInfo['first_name'];
            $buyerInfo['last_name'] = $userInfo['last_name'];
            $buyerInfo['status'] = $userInfo['status'];
            $buyerInfo['zipcode'] = $zipCode['zipcode'];
            $buyerInfo['address'] = $zipCode['address'];

            return $buyerInfo;
        } else{
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($condition,$where){

        if(isset($condition['lang'])){
            $data['lang']=$condition['lang'];
        }
        if(isset($condition['bn'])){
            $data['bn']=$condition['bn'];
        }
        if(isset($condition['name'])){
            $data['name']=$condition['name'];
        }
        if(isset($condition['profile'])){
            $data['profile']=$condition['profile'];
        }
        if(isset($condition['country'])){
            $data['country']=$condition['country'];
        }
        if(isset($condition['province'])){
            $data['province']=$condition['province'];
        }
        if(isset($condition['logo'])){
            $data['logo']=$condition['logo'];
        }
        if(isset($condition['official_website'])){
            $data['official_website']=$condition['official_website'];
        }
        if(isset($condition['brand'])){
            $data['brand']=$condition['brand'];
        }
        if(isset($condition['bank_name'])){
            $data['bank_name']=$condition['bank_name'];
        }
        if(isset($condition['swift_code'])){
            $data['swift_code']=$condition['swift_code'];
        }
        if(isset($condition['bank_address'])){
            $data['bank_address']=$condition['bank_address'];
        }
        if(isset($condition['bank_account'])){
            $data['bank_account']=$condition['bank_account'];
        }
        if(isset($condition['buyer_level'])){
            $data['buyer_level']=$condition['buyer_level'];
        }
        if(isset($condition['credit_level'])){
            $data['credit_level']=$condition['credit_level'];
        }
        if(isset($condition['finance_level'])){
            $data['finance_level']=$condition['finance_level'];
        }
        if(isset($condition['logi_level'])){
            $data['logi_level']=$condition['logi_level'];
        }
        if(isset($condition['qa_level'])){
            $data['qa_level']=$condition['qa_level'];
        }
        if(isset($condition['steward_level'])){
            $data['steward_level']=$condition['steward_level'];
        }
        if(isset($condition['remarks'])){
            $data['remarks']=$condition['remarks'];
        }
        if($condition['status']){
            switch ($condition['status']) {
                case self::STATUS_VALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_INVALID:
                    $data['status'] = $condition['status'];
                    break;
                case self::STATUS_DELETE:
                    $data['status'] = $condition['status'];
                    break;
            }
        }

        return $this->where($where)->save($data);

    }

    /**
     * 通过顾客id获取会员等级
     * @author klp
     */
    public function getService($info,$token)
    {
        $where=array();
        if(!empty($token['customer_id'])){
            $where['customer_id'] = $token['customer_id'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel =  $this->field('buyer_level')
                            ->where($where)
                            ->find();
        //获取服务
        $MemberBizService = new MemberBizServiceModel();
        $result = $MemberBizService->getService($buyerLevel,$lang);
        if($result){
            return $result;
        } else{
            return array();
        }
    }

    /**
     * 获取企业信息(数据表信息不全,待完善)_
     * @author klp
     */
    public function getBuyerInfo($info)
    {
        $where=array();$info['customer_id'] = '20170630000001'; $info['lang']='en';
        if(!empty($info['customer_id'])){
            $where['customer_id'] = $info['customer_id'];
        } else{
            jsonReturn('','-1001','用户[customer_id]不可以为空');
        }
        $where['lang'] = $info['lang'] ? strtolower($info['lang']) :  'en';
        $field = 'serial_no,name,bn,country,profile,reg_date,bank_name,swift_code,bank_address,bank_account,listed_flag,official_address,reg_date,capital_account,sales,official_phone,fax,official_website,employee_count,credit_total,credit_available,apply_at,approved_at,remarks';
        try{
            $buyerInfo =  $this->field($field)->where($where)->find();
            if($buyerInfo){
                //获取国家代码与企业邮箱与邮箱
                $BuyerAddressModel = new BuyerAddressModel();
                $addressInfo = $BuyerAddressModel->field('tel_country_code,official_email,zipcode')->where($where)->find();
                $buyerInfo['tel_country_code'] = $buyerInfo['official_email'] = $buyerInfo['zipcode'] = '';
                if($addressInfo){
                    $buyerInfo['tel_country_code'] = $$addressInfo['tel_country_code'];
                    $buyerInfo['official_email'] = $$addressInfo['official_email'];
                    $buyerInfo['zipcode'] = $$addressInfo['zipcode'];
                }
                $buyerRegInfo = new BuyerreginfoModel();
                $result = $buyerRegInfo->getBuyerRegInfo($where);
                return $result ? array_merge($buyerInfo,$result) : $buyerInfo;
            }
            return array();
        }catch (Exception $e){var_dump($e);
            return array();
        }
    }

    /**
     * 企业信息新建-门户
     * @author klp
     */
    public function createInfo($token,$input)
    {
        if (!isset($input))
            return false;
        $this->startTrans();
        try {
            foreach ($input as $key => $item) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {
                    $checkout = $this->checkParam($item);
                    $data = [
                        'lang' => $key,
                        'customer_id' => $token['customer_id'],
                        'serial_no' => $checkout['serial_no'],
                        'name' => $checkout['name'],
                        'country' => $checkout['country'],
                        'bank_name' => $checkout['bank_name'],
                        'bank_address' =>  $checkout['bank_address'],
                        'official_address' =>  $checkout['official_address'],
                        'bn' => isset($checkout['bn']) ? $checkout['bn'] : '',
                        'bank_account' => isset($checkout['bank_account']) ? $checkout['bank_account'] : '',
                        'profile' => isset($checkout['profile']) ? $checkout['profile'] : '',
                        'province' => isset($checkout['province']) ? $checkout['province'] : '',
                        'city' => isset($checkout['city']) ? $checkout['city'] : '',
                        'reg_date' => isset($checkout['reg_date']) ? $checkout['reg_date'] : '',
                        'swift_code' => isset($checkout['swift_code']) ? $checkout['swift_code'] : '',
                        'listed_flag' => isset($checkout['listed_flag']) ? $checkout['listed_flag'] : 'N',
                        'registered_time' => isset($checkout['registered_time']) ? $checkout['registered_time'] : '',
                        'capital_account' => isset($checkout['capital_account']) ? $checkout['capital_account'] : 0,
                        'sales' => isset($checkout['sales']) ? $checkout['sales'] : 0,
                        'official_phone' => isset($checkout['official_phone']) ? $checkout['official_phone'] : '',
                        'fax' => isset($checkout['fax']) ? $checkout['fax'] : '',
                        'website_url' => isset($checkout['website_url']) ? $checkout['website_url'] : '',
                        'employees' => isset($checkout['employees']) ? $checkout['employees'] : '',
                        'credit_total' => isset($checkout['credit_total']) ? $checkout['credit_total'] : 0,
                        'credit_available' => isset($checkout['credit_available']) ? $checkout['credit_available'] : 0,
                    ];
                    //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                    $result = $this->field('customer_id')->where(['customer_id' => $token['customer_id'], 'lang' => $key])->find();
                    if ($result) {
                        $this->where(['customer_id' => $token['customer_id'], 'lang' => $key])->save($data);
                    } else {
                        $this->add($data);
                    }
                    //t_buyer_reg_info
                    $buyerRegInfo = new BuyerreginfoModel();
                    $result = $buyerRegInfo->createInfo($token,$input);
                    if($result){
                        return false;
                    }
                    //t_buyer_address
                    $buyerAddressMode = new BuyerAddressModel();
                    $res = $buyerAddressMode->createInfo($token,$input);
                    if($res){
                        return false;
                    }
                }
            }
            $this->commit();
            return $token['customer_id'];
        } catch(\Kafka\Exception $e){
            $this->rollback();
            return false;
        }
    }
    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($param = []) {
        if (empty($param))
            return false;
        if(!isset($param['name']) && empty($param['name'])) { jsonReturn('','-1002','[name]不能为空');}
        if(!isset($param['country']) && empty($param['country'])) { jsonReturn('','-1002','[country]不能为空');}
        if(!isset($param['bank_name']) && empty($param['bank_name'])) { jsonReturn('','-1002','[bank_name]不能为空');}
        if(!isset($param['bank_address']) && empty($param['bank_address'])) { jsonReturn('','-1002','[bank_address]不能为空');}
        if(!isset($param['official_address']) && empty($param['official_address'])) { jsonReturn('','-1002','[official_address]不能为空');}
        return $param;
    }

}
