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

    //put your code here
    protected $tableName = 'buyer_reg_info';
    protected $dbName = 'erui_buyer';
    Protected $autoCheckFields = false;

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
     * 根据条件获取信息(企业/银行)
     * @param mix $condition
     * @return mix
     * @author
     */
    protected function getBuyerRegInfo($condition=[]) {
        if(empty($condition))
            return false;
        $where=array();
        if(!empty($info['customer_id'])){
            $where['customer_id'] = $condition['customer_id'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        $where['lang'] = $condition['lang'] ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');
        $field = 'legal_person_name,legal_person_gender,expiry_date,registered_in,reg_capital,reg_capital_cur,social_credit_code,biz_nature,biz_scope,biz_type,service_type';
        try{
            $buyerRegInfo =  $this->field($field)->where($where)->find();
            return $buyerRegInfo ? $buyerRegInfo : array();
        } catch(\Kafka\Exception $e){
            return false;
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
            foreach ($input as $key => $value) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {
                    $checkout = $this->checkParam($input, $this->field);
                    $data = [
                        'lang' => $key,
                        'customer_id' => $token['customer_id'],
                        'registered_in' => $checkout['registered_in'],
                        'legal_person_name' => isset($checkout['legal_person_name']) ? $checkout['legal_person_name'] : '',
                        'legal_person_gender' => isset($checkout['legal_person_gender']) ? $checkout['legal_person_gender'] : '',
                        'expiry_date' => isset($checkout['expiry_date']) ? $checkout['expiry_date'] : '',
                        'reg_capital' => isset($checkout['reg_capital']) ? $checkout['reg_capital'] : '',
                        'reg_capital_cur' => isset($checkout['reg_capital_cur']) ? $checkout['reg_capital_cur'] : '',
                        'social_credit_code' => isset($checkout['social_credit_code']) ? $checkout['social_credit_code'] : '',
                        'biz_nature' => isset($checkout['biz_nature']) ? $checkout['biz_nature'] : '',
                        'biz_scope' => isset($checkout['biz_scope']) ? $checkout['biz_scope'] : '',
                        'biz_type' => isset($checkout['biz_type']) ? $checkout['biz_type'] : '',
                        'service_type' => isset($checkout['service_type']) ? $checkout['service_type'] : '',
                        'created_by' => $token['user_name'],
                        'created_at' => date('Y-m-d H:i:s', time())
                    ];
                    $this->add($data);
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
     * 参数校验    注：没有参数或没有规则，默认返回true（即不做验证）
     * @param array $param  参数
     * @param array $field  校验规则
     * @return bool
     *
     * Example
     * checkParam(
     *      array('name'=>'','key'=>''),
     *      array(
     *          'name'=>array('required'),
     *          'key'=>array('method','fun')
     *      )
     * )
     */
    private function checkParam($param = [], $field = []) {
        if (empty($param) || empty($field))
            return array();
        foreach ($param as $k => $v) {
            if (isset($field[$k])) {
                $item = $field[$k];
                switch ($item[0]) {
                    case 'required':
                        if ($v == '' || empty($v)) {
                            jsonReturn('', '-1001', 'Param ' . $k . ' Not null !');
                        }
                        break;
//                    case 'method':
//                        if (!method_exists($item[1])) {
//                            jsonReturn('', '404', 'Method ' . $item[1] . ' nont find !');
//                        }
//                        if (!call_user_func($item[1], $v)) {
//                            jsonReturn('', '1001', 'Param ' . $k . ' Validate failed !');
//                        }
//                        break;
                }
            }
            // $param[$k] = htmlspecialchars(trim($v));
            continue;
        }
        return $param;
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
    public function create_data($createcondition = []) {
        
    }

}
