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

//    protected $autoCheckFields = false;
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        $sql =  'SELECT `id`,`serial_no`,`buyer_no`,`lang`,`buyer_type`,`name`,`bn`,`profile`,`country_code`,`country_bn`,`province`,`city`,`official_email`,';
        $sql .=  '`official_email`,`official_phone`,`official_fax`,`first_name`,`last_name`,`brand`,`official_website`,`logo`,`sec_ex_listed_on`,`line_of_credit`,`credit_available`,`credit_cur_bn`,`buyer_level`,`credit_level`,';
        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`recommend_flag`,`status`,`remarks`,`apply_at`,`created_by`,`created_at`,`checked_by`,`checked_at`';
        $sql .= ' FROM '.$this->g_table;
        $where ="";
        if ( !empty($condition['country_bn']) ){
            $where .= ' WHERE country_bn ='.$condition['country_bn'];
        }
        if ( !empty($condition['name']) ){
            if($where){
                $where .= " and name like '%".$condition['name'] ."%'";
            }else{
                $where .=" WHERE name like '%".$condition['name'] ."%'";
            }
        }
        if($where) {
            $sql .= $where;
        }
        $sql .= ' Order By '.$order;
        if ( $condition['num'] ){
            $sql .= ' LIMIT '.$condition['page'].','.$condition['num'];
        }
        return $this->query( $sql );
    }

    public function create_data($create = [])
    {
        if(isset($create['buyer_no'])){
            $data['buyer_no'] = $create['buyer_no'];
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
        if(isset($create['country_code'])){
            $data['country_code'] = $create['country_code'];
        }
        if(isset($create['country_bn'])){
            $data['country_bn'] = $create['country_bn'];
        }
        if(isset($create['official_email'])){
            $data['official_email'] = $create['official_email'];
        }
        if(isset($create['official_phone'])){
            $data['official_phone'] = $create['official_phone'];
        }
        if(isset($create['official_fax'])){
            $data['official_fax'] = $create['official_fax'];
        }
        if(isset($create['first_name'])){
            $data['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $data['last_name'] = $create['last_name'];
        }
        if(isset($create['province'])){
            $data['province'] = $create['province'];
        }
        if(isset($create['logo'])){
            $data['logo'] = $create['logo'];
        }
        if(isset($create['city'])){
            $data['city'] = $create['city'];
        }

        if(isset($create['brand'])){
            $data['brand'] = $create['brand'];
        }
        if(isset($create['bank_name'])){
            $data['bank_name'] = $create['bank_name'];
        }
        if(isset($create['official_website'])){
            $data['official_website'] = $create['official_website'];
        }
        if(isset($create['remarks'])){
            $data['remarks'] = $create['remarks'];
        }
        if(isset($create['checked_by'])){
            $data['checked_by'] = $create['checked_by'];
        }
        if(isset($create['checked_by'])){
            $data['checked_by'] = $create['checked_by'];
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['checked_at'] = date('Y-m-d H:i:s');
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
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($create,$where){

        if(isset($create['buyer_no'])){
            $data['buyer_no'] = $create['buyer_no'];
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
        if(isset($create['country_code'])){
            $data['country_code'] = $create['country_code'];
        }
        if(isset($create['country_bn'])){
            $data['country_bn'] = $create['country_bn'];
        }
        if(isset($create['official_email'])){
            $data['official_email'] = $create['official_email'];
        }
        if(isset($create['official_phone'])){
            $data['official_phone'] = $create['official_phone'];
        }
        if(isset($create['official_fax'])){
            $data['official_fax'] = $create['official_fax'];
        }
        if(isset($create['first_name'])){
            $data['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $data['last_name'] = $create['last_name'];
        }
        if(isset($create['province'])){
            $data['province'] = $create['province'];
        }
        if(isset($create['logo'])){
            $data['logo'] = $create['logo'];
        }
        if(isset($create['city'])){
            $data['city'] = $create['city'];
        }

        if(isset($create['brand'])){
            $data['brand'] = $create['brand'];
        }
        if(isset($create['bank_name'])){
            $data['bank_name'] = $create['bank_name'];
        }
        if(isset($create['official_website'])){
            $data['official_website'] = $create['official_website'];
        }
        if(isset($create['remarks'])){
            $data['remarks'] = $create['remarks'];
        }
        if(isset($create['checked_by'])){
            $data['checked_by'] = $create['checked_by'];
        }
        if(isset($create['checked_by'])){
            $data['checked_by'] = $create['checked_by'];
        }
        if($create['status']){
            switch ($create['status']) {
                case self::STATUS_VALID:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_INVALID:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_DELETED:
                    $data['status'] = $create['status'];
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
     * 获取企业信息
     * @author klp
     */
    public function buyerInfo($info){
        //jsonReturn(123);
        //$info['id'] = '20170630000001'; $info['lang']='en';//测试
        $where=array();
        if(!empty($info['customer_id'])){
            $where['customer_id'] = $info['customer_id'];
        } else{
            jsonReturn('','-1001','用户[buyer_id]不可以为空');
        }
        if (isset($info['lang']) && in_array($info['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($info['lang']);
        }
        //$field = 'lang,serial_no,buyer_type,buyer_no,name,bn,country_code,country_bn,profile,province,city,official_email,official_phone,official_fax,first_name,last_name,brand,official_website,sec_ex_listed_on,line_of_credit,credit_available,credit_cur_bn,buyer_level,credit_level,recommend_flag,status,remarks,apply_at,created_by,created_at,checked_by,checked_at';
        try{
            $buyerInfo =  $this->where($where)->find();
            /*if($buyerInfo){
                $BuyerreginfoModel = new BuyerreginfoModel();
                $result = $BuyerreginfoModel->buyerRegInfo($where);
                return !empty($result) ? array_merge($buyerInfo,$result) : $buyerInfo;
            }*/
            return $buyerInfo;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 用户及企业部分信息新建/编辑  --门户
     * @author klp
     */
    public function editInfo($token,$input){
        if (!isset($input)) {
            return false;
        }
        $this->startTrans();
        try {
            if (is_array($input)) {
                $checkout = $this->checkParam($input);
                $data = [
                    'name' => $checkout['name'],
                    'country_code' => strtoupper($checkout['country_code']),
                    'country_bn' => strtoupper($checkout['country_code']),
                    'official_email' => isset($checkout['official_email']) ? $checkout['official_email'] : '',
                    'official_phone' => isset($checkout['official_phone']) ? $checkout['official_phone'] : '',
                    'official_fax' => isset($checkout['official_fax']) ? $checkout['official_fax'] : '',
                    'first_name' => isset($checkout['created_by']) ? $checkout['created_by'] : '',
                    'official_website' => isset($checkout['official_website']) ? $checkout['official_website'] : '',
                    'province' => isset($checkout['province']) ? $checkout['province'] : '',//暂为办公地址

                    'remarks' => isset($checkout['remarks']) ? $checkout['remarks'] : '',
                    'recommend_flag' => isset($checkout['recommend_flag']) ? strtoupper($checkout['recommend_flag']) : 'N'
                ];
                //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                $result = $this->field('id')->where(['id' => $token['id']])->find();
                if ($result) {
                    $result = $this->where(['id' => $token['id']])->save($data);
                    if(!$result){
                        $this->rollback();
                        return false;
                    }
                } else {
                    // 生成用户编码
                    $condition['page']=0;
                    $condition['countPerPage']=1;
                    $data_t_buyer = $this->getlist($condition);
                    if($data_t_buyer&&substr($data_t_buyer[0]['buyer_no'],1,8) == date("Ymd")){
                        $no=substr($data_t_buyer[0]['buyer_no'],-1,6);
                        $no++;
                    }else{
                        $no=1;
                    }
                    $temp_num = 1000000;
                    $new_num = $no + $temp_num;
                    $real_num = "C".date("Ymd").substr($new_num,1,6); //即截取掉最前面的“1”即为buyer_no

                    $data['buyer_no'] =$real_num;
                    $data['serial_no'] =$real_num;
                    $data['apply_at'] = date('Y-m-d H:i:s', time());
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $data['status'] = self::STATUS_CHECKING;//待审状态
                    $result= $this->add($data);
                    if(!$result){
                        $this->rollback();
                        return false;
                    }
                }
                //buyer_reg_info
                $buyerRegInfo = new BuyerreginfoModel();
                $result = $buyerRegInfo->createInfo($token,$input);
                if(!$result){
                    $this->rollback();
                    return false;
                }
                //buyer_address
                $BuyerBankInfoModel = new BuyerBankInfoModel();
                $res = $BuyerBankInfoModel->editInfo($token,$input);
                if(!$res){
                    $this->rollback();
                    return false;
                }
            } else {
                return false;
            }
            $this->commit();
            return $token['buyer_id'];
        } catch(\Kafka\Exception $e){
            $this->rollback();
            //var_dump($e);//测试
            return false;
        }
    }

    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($param = []) {
        if (empty($param)) {
            return false;
        }
        $results = array();
        if(empty($param['name'])) {
            $results['code'] = -101;
            $results['message'] = '[name]不能为空!';
        }
        if(empty($param['bank_name'])) {
            $results['code'] = -101;
            $results['message'] = '[bank_name]不能为空!';
        }
        if(empty($param['bank_address'])) {
            $results['code'] = -101;
            $results['message'] = '[bank_address]不能为空!';
        }
        if(empty($param['province'])) {
            $results['code'] = -101;
            $results['message'] = '[province]不能为空!';
        }
        if($results){
            jsonReturn($results);
        }
        return $param;
    }


    /**
     * 提交易瑞   -- 待审核
     * @author klp
     */
    public function subCheck($data)
    {
        if (empty($data)) {
            return false;
        }
        //新状态可以补充
        $status = [];
        switch ($data['status_type']) {
            case 'check':    //审核
                $status['status'] = self::STATUS_CHECKING;
                break;

        }
        $result = $this->where($token['customer_id'])->save(['status' => $status['status']]);
        return $result ? true : false;
    }
}
