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
class SupplierModel extends PublicModel {
    //put your code here
    protected $tableName = 'supplier';
    protected $dbName = 'erui2_supplier'; //数据库名称
    protected $g_table = 'erui2_supplier.supplier';
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
        $sql =  'SELECT `id`,`lang`,`serial_no`,`supplier_no`,`supplier_type`,`name`,`bn`,`profile`,`reg_capital`,`employee_count`,`country_code`,`country_bn`,`province`,`city`,`official_email`,';
        $sql.=  '`official_email`,`social_credit_code`,`official_phone`,`official_fax`,`first_name`,`last_name`,`brand`,`official_website`,`logo`,`sec_ex_listed_on`,`line_of_credit`,`credit_available`,`credit_cur_bn`,`supplier_level`,`credit_level`,';
        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`recommend_flag`,`status`,`remarks`,`apply_at`,`created_by`,`created_at`,`checked_by`,`checked_at`';
        $sql_count =  'SELECT count(`id`) as num ';
        $str = ' FROM '.$this->g_table;
        $sql .= $str;
        $sql_count .= $str;
        $where =" WHERE 1 = 1";
        if ( !empty($condition['country_bn']) ){
            $where .= ' And country_bn ="'.$condition['country_bn'].'"';
        }
        if ( !empty($condition['name']) ){
            $where .= " And name like '%".$condition['name'] ."%'";
        }
        if ( !empty($condition['supplier_no']) ){
            $where .= ' And supplier_no  ="'.$condition['supplier_no'].'"';
        }
        if ( !empty($condition['status']) ){
            $where .= ' And status  ="'.$condition['status'].'"';
        }
        if ( !empty($condition['checked_at_start']) ){
            $where .= ' And checked_at  >="'.$condition['checked_at_start'].'"';
        }
        if ( !empty($condition['checked_at_end']) ){
            $where .= ' And checked_at  <="'.$condition['checked_at_end'].'"';
        }
        if ( !empty($condition['created_at_start']) ){
            $where .= ' And created_at  >="'.$condition['created_at_start'].'"';
        }
        if ( !empty($condition['created_at_end']) ){
            $where .= ' And created_at  <="'.$condition['created_at_end'].'"';
        }
        if ($where) {
            $sql .= $where;
            $sql_count.= $where;
        }
        $sql .= ' Order By '.$order;
        if ( $condition['num'] ){
            $sql .= ' LIMIT '.$condition['page'].','.$condition['num'];
        }
        $count =$this->query( $sql_count );
        $res['count'] =$count[0]['num'];
        $res['data'] =  $this->query( $sql );
        return $res;
    }



    public function create_data($create = [])
    {


        if(isset($create['serial_no'])){
            $data['serial_no'] = $create['serial_no'];
        }
        if(isset($create['supplier_no'])){
            $data['supplier_no'] = $create['supplier_no'];
        }
        if(isset($create['supplier_type'])){
            $data['supplier_type'] = $create['supplier_type'];
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
        if(isset($create['reg_capital'])){
            $data['reg_capital'] = $create['reg_capital'];
        }
        if(isset($create['employee_count'])){
            $data['employee_count'] = $create['employee_count'];
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
        if(isset($create['social_credit_code'])){
            $data['social_credit_code'] = $create['social_credit_code'];
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
        if(isset($create['logo'])){
            $data['logo'] = $create['logo'];
        }
        if(isset($create['official_website'])){
            $data['official_website'] = $create['official_website'];
        }
        if(isset($create['sec_ex_listed_on'])){
            $data['sec_ex_listed_on'] = $create['sec_ex_listed_on'];
        }
        if(isset($create['line_of_credit'])){
            $data['line_of_credit'] = $create['line_of_credit'];
        }
        if(isset($create['credit_available'])){
            $data['credit_available'] = $create['credit_available'];
        }
        if(isset($create['credit_cur_bn'])){
            $data['credit_cur_bn'] = $create['credit_cur_bn'];
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
        $data['status'] = 'DRAFT';
        $data['created_at'] = date('Y-m-d H:i:s');
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
     * @author jhw
     */
    public function info($data)
    {
        if($data['id']) {
            $buyerInfo = $this->where(array("id" => $data['id']))
                              ->find();
            return $buyerInfo;
        } else{
            return false;
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
        if(isset($create['reg_capital'])){
            $data['reg_capital'] = $create['reg_capital'];
        }
        if(isset($create['employee_count'])){
            $data['employee_count'] = $create['employee_count'];
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
            $data['status'] = $create['status'];
        }
        if(isset($create['social_credit_code'])){
            $data['social_credit_code'] = $create['social_credit_code'];
        }
        if(isset($create['supplier_type'])){
            $data['supplier_type'] = $create['supplier_type'];
        }
        return $this->where($where)->save($data);

    }


}
