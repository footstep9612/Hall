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
    protected $g_table = 'erui_buyer.t_buyer_account';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }



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
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function login($data) {
        $where=array();
        if(!empty($data['email'])){
            $where['email'] = $data['email'];
        }
        if(!empty($data['mobile'])){
            $where['mobile'] = $data['mobile'];
        }
        if(empty($where['mobile'])&&empty($where['email'])){
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if(!empty($data['password'])){
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'NORMAL';
        $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        $row = $this->where($where)
            ->field('id,user_no,name,email,mobile,status')
            ->find();
        return $row;
    }

    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
//    public function Exist($data) {
//        $sql =  'SELECT `id`,`serial_no`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
//        $sql .=  '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
//        $sql .=  '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`status`,`remarks`,`apply_at`,`approved_at`';
//        $sql .= ' FROM '.$this->g_table;
//        $where = '';
//        if ( !empty($data['email']) ){
//            $where .= " where email = '" .$data['email']."'";
//        }
//        if ( !empty($data['mobile']) ){
//            if($where){
//                $where .= " or mobile = '" .$data['mobile']."'";
//            }else{
//                $where .= " where mobile = '" .$data['mobile']."'";
//            }
//
//        }
//        if ( $where){
//            $sql .= $where;
//        }
//        $row = $this->query( $sql );
//        return empty($row) ? false : (isset($row['customer_id']) ? $row['customer_id'] : true);
//    }



    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($create = []) {
        $data['customer_id']=$create['customer_id'];
        $data['serial_no']=$create['serial_no'];
        $data['lang']=$create['lang'];
        $data['name']=$create['name'];
        $data['bn']=$create['bn'];
        $data['profile']=$create['profile'];
        $data['country']=$create['country'];
        $data['province']=$create['province'];
        $data['reg_date']=date('Y-m-d');
        $data['logo']=$create['logo'];
        $data['official_website']=$create['official_website'];
        $data['brand']=$create['brand'];
        $data['bank_name']=$create['bank_name'];
        $data['swift_code']=$create['swift_code'];
        $data['bank_address']=$create['bank_address'];
        $data['bank_account']=$create['bank_account'];
        $data['buyer_level']=$create['buyer_level'];
        $data['credit_level']=$create['credit_level'];
        $data['finance_level']=$create['finance_level'];
        $data['logi_level']=$create['logi_level'];
        $data['qa_level']=$create['qa_level'];
        $data['steward_level']=$create['steward_level'];
        $data['remarks']=$create['remarks'];
        $data['apply_at']=date('Y-m-d H:i:s');
        $data['approved_at']=$create['approved_at'];
        $datajson = $this->create($data);
        return $this->add($datajson);
    }

}
