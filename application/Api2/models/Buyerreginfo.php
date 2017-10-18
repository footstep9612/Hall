<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 采购商注册信息
 * Description of Buyerreginfo
 *
 * @author zhongyg
 */
class BuyerreginfoModel extends PublicModel {
    protected $tableName = 'buyer_reg_info';
    protected $dbName = 'erui_buyer';

    const STATUS_NORMAL = 'NORMAL'; //NORMAL-正常；
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除

    public function __construct($str = '') {
        parent::__construct($str = '');
    }
    //定义校验规则
    protected $field = array(
        'registered_in' => array('required'),
    );

    /**
     * 根据条件获取信息(企业)
     * @param mix $condition
     * @return mix
     * @author
     */
    public function buyerRegInfo($condition=[]) {
        if(empty($condition))
            return false;
        $where=array();
        if(!empty($condition['id'])){
            $where['buyer_id'] = $condition['id'];
        } else{
            jsonReturn('','-1001','用户[buyer_id]不可以为空');
        }
//        if (isset($info['lang']) && in_array($info['lang'], array('zh', 'en', 'es', 'ru'))) {
//            $where['lang'] = strtolower($info['lang']);
//        }
        $field = 'legal_person_name,legal_person_gender,reg_date,expiry_date,registered_in,reg_capital,social_credit_code,biz_nature,biz_scope,biz_type,service_type,branch_count,employee_count,equitiy,turnover,profit,total_assets,reg_capital_cur_bn,equity_ratio,equity_capital,created_by,created_at,deleted_flag';
        try{
            $buyerRegInfo =  $this->field($field)->where($where)->find();
            return $buyerRegInfo ? $buyerRegInfo : array();
        } catch(Exception $e){
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return false;
        }
    }

    /**
     * 企业信息新建/编辑 --门户
     * @author klp
     */
    public function createInfo($token,$input){
        if (!isset($input))
            return false;
        try {
            if (is_array($input)) {
                $data = $this->checkParam($input);
                //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                $result = $this->field('buyer_id')->where(['buyer_id' => $token['id']])->find();
                if ($result) {
                    $data['updated_at'] = date('Y-m-d H:i:s', time());
                    $result = $this->where(['buyer_id' => $token['id']])->save($data);
                    if(!$result){
                        return false;
                    }
                } else {
                    $data['buyer_id'] =$token['id'];
//                    $data['created_by'] = $token['buyer_id'];
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $result = $this->add($data);
                    if(!$result){
                        return false;
                    }
                }
            } else {
                return false;
            }
            return true;
        } catch(Exception $e){
           // var_dump($e);//测试
            return false;
        }
    }

    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($params = []) {
        if (empty($params)) {
            return false;
        }
        $data = $results = array();

        if(!empty($params['registered_in'])) {
            $data['registered_in'] = $params['registered_in'];
        } else{
            $results['code'] = -101;
            $results['message'] = '[registered_in]不能为空!';
        }
        if(!empty($params['legal_person_name'])) {
            $data['legal_person_name'] = $params['legal_person_name'];
        }
        if(!empty($params['legal_person_gender'])) {
            $data['legal_person_gender'] = $params['legal_person_gender'];
        }
        if(!empty($params['reg_date'])) {
            $data['reg_date'] = $params['reg_date'];
        }
        if(!empty($params['expiry_date'])) {
            $data['expiry_date'] = $params['expiry_date'];
        }
        if(!empty($params['reg_capital'])) {
            $data['reg_capital'] = $params['reg_capital'];
        }
        if(!empty($params['reg_capital_cur_bn'])) {
            $data['reg_capital_cur_bn'] = $params['reg_capital_cur_bn'];
        }
        if(!empty($params['social_credit_code'])) {
            $data['social_credit_code'] = $params['social_credit_code'];
        }
        if(!empty($params['biz_nature'])) {
            $data['biz_nature'] = $params['biz_nature'];
        }
        if(!empty($params['biz_scope'])) {
            $data['biz_scope'] = $params['biz_scope'];
        }
        if(!empty($params['biz_type'])) {
            $data['biz_type'] = $params['biz_type'];
        }
        if(!empty($params['service_type'])) {
            $data['service_type'] = $params['service_type'];
        }
        if(!empty($params['employee_count'])) {
            $data['employee_count'] = $params['employee_count'];
        }
        if(!empty($params['equitiy'])) {
            $data['equitiy'] = $params['equitiy'];
        }
        if(!empty($params['turnover'])) {
            $data['turnover'] = $params['turnover'];
        }
        if(!empty($params['profit'])) {
            $data['profit'] = $params['profit'];
        }
        if(!empty($params['total_assets'])) {
            $data['total_assets'] = $params['total_assets'];
        }
        if(!empty($params['equity_ratio'])) {
            $data['equity_ratio'] = $params['equity_ratio'];
        }
        if(!empty($params['equity_capital'])) {
            $data['equity_capital'] = $params['equity_capital'];
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {
        
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {
        
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($code = '', $id = '', $lang = '') {
        
    }

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($code = '', $id = '', $lang = '') {
        
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($upcondition = []) {
        
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($params = []) {
        if(isset($params['registered_in'])) {
            $data['registered_in'] = $params['registered_in'];
        }
        if(isset($params['legal_person_name'])) {
            $data['legal_person_name'] = $params['legal_person_name'];
        }
        if(isset($params['legal_person_gender'])) {
            $data['legal_person_gender'] = $params['legal_person_gender'];
        }
        if(isset($params['reg_date'])) {
            $data['reg_date'] = $params['reg_date'];
        }
        if(isset($params['expiry_date'])) {
            $data['expiry_date'] = $params['expiry_date'];
        }
        if(isset($params['reg_capital'])) {
            $data['reg_capital'] = $params['reg_capital'];
        }
        if(isset($params['reg_capital_cur_bn'])) {
            $data['reg_capital_cur_bn'] = $params['reg_capital_cur_bn'];
        }
        if(isset($params['social_credit_code'])) {
            $data['social_credit_code'] = $params['social_credit_code'];
        }
        if(isset($params['biz_nature'])) {
            $data['biz_nature'] = $params['biz_nature'];
        }
        if(isset($params['biz_scope'])) {
            $data['biz_scope'] = $params['biz_scope'];
        }
        if(isset($params['biz_type'])) {
            $data['biz_type'] = $params['biz_type'];
        }
        if(isset($params['service_type'])) {
            $data['service_type'] = $params['service_type'];
        }
        if(isset($params['employee_count'])) {
            $data['employee_count'] = $params['employee_count'];
        }
        if(isset($params['equitiy'])) {
            $data['equitiy'] = $params['equitiy'];
        }
        if(isset($params['turnover'])) {
            $data['turnover'] = $params['turnover'];
        }
        if(isset($params['profit'])) {
            $data['profit'] = $params['profit'];
        }
        if(isset($params['total_assets'])) {
            $data['total_assets'] = $params['total_assets'];
        }
        if(isset($params['equity_ratio'])) {
            $data['equity_ratio'] = $params['equity_ratio'];
        }
        if(isset($params['equity_capital'])) {
            $data['equity_capital'] = $params['equity_capital'];
        }
        if (isset($params['intent_product'])) {
            $data['intent_product'] = $params['intent_product'];
        }
        if (isset($params['purchase_quota'])) {
            $data['purchase_quota'] = $params['purchase_quota'];
        }
        $data['buyer_id'] =$params['buyer_id'];
        $data['created_by'] = $params['buyer_id'];
        $data['created_at'] = Date("Y-m-d H:i:s");
        $arr = $this->create($data);
        return $this->add($arr);
    }

}
