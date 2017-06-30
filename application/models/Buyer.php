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
    Protected $autoCheckFields = false;
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

//    /**
//     * 获取列表
//     * @param  string $code 编码
//     * @param  int $id id
//     * @param  string $lang 语言
//     * @return mix
//     * @author zyg
//     */
//    public function info($id = '') {
//        $where['id'] = $id;
//        return $this->where($where)
//                        ->field('id,user_id,name,email,mobile,status')
//                        ->find();
//    }



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
            $buyerInfo = $this->where("email='".$data['email']."'")
                              ->field('customer_id,lang,name,bn,country,province,city,official_website,buyer_level')
                              ->find();
        } else{
            $buyerInfo = $this->where("customer_id='".$data['customer_id']."'")
                              ->field('customer_id,lang,name,bn,country,province,city,official_website,buyer_level')
                              ->find();

        }
        if($buyerInfo){
            //通过顾客id查询用户信息
            $buyerAccount = new BuyerAccountModel();
            $userInfo = $buyerAccount->field('email,user_name,phone,first_name,last_name,status')
                                     ->where(array('customer_id' => $buyerInfo['customer_id']))
                                     ->find();


            //通过顾客id查询用户邮编
            $buyerAddress = new BuyerAddressModel();
            $zipCode = $buyerAddress->field('zipcode')
                                    ->where(array('customer_id' => $buyerInfo['customer_id']))
                                    ->find();

            $buyerInfo['email'] = $userInfo['email'];
            $buyerInfo['user_name'] = $userInfo['user_name'];
            $buyerInfo['phone'] = $userInfo['phone'];
            $buyerInfo['first_name'] = $userInfo['first_name'];
            $buyerInfo['last_name'] = $userInfo['last_name'];
            $buyerInfo['status'] = $userInfo['status'];
            $buyerInfo['zipcode'] = $zipCode['zipcode'];

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
    public function getService($condition=[])
    {
        if(!empty($condition['customer_id'])){
            $where['customer_id'] = $condition['customer_id'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        $lang = $condition['lang'] ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel = $this->where($where)
            ->field('buyer_level')
            ->find();
        //获取服务
        $MemberBizService = new MemberBizServiceModel();
        $result = $MemberBizService->getService($buyerLevel,$lang);
        $result['buyer_level'] = $buyerLevel;
        if($result){
            return $result;
        } else{
            return array();
        }
    }

    /**
     * 通过顾客id获取国家地区简称
     * @author klp
     */
    public function getInquiryInfo($data)
    {
        if(!empty($data['customer_id'])){
            $where['customer_id'] = $data['customer_id'];
        } else{
            jsonReturn('','-1001','用户[customer_id]不可以为空');
        }
        $country = $this->field('country')->where($where)->find();
        //获取国家简称
        $CountryModel = new CountryModel();
        $country_bn = $CountryModel->field('bn')->where(array('name'=>$country['country']))->find();
        //获取地区简称
        $MarketAreaCountryModel = new MarketAreaCountryModel();
        $marketArea_bn = $MarketAreaCountryModel->field('market_area_bn')->where(array('country_bn'=>$country_bn['bn']))->find();


    }
}
