<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 15:09
 */
class BuyerBankInfoModel extends PublicModel{
    protected $tableName = 'buyer_bank_info';
    protected $dbName = 'erui2_buyer'; //数据库名称

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    /**
     * 获取银行信息
     * @author klp
     */
    public function getBuyerBankInfo($info)
    {
        //$info['customer_id'] = '20170630000001'; $info['lang']='en';//测试
        $where=array();
        if(!empty($info['buyer_id'])){
            $where['buyer_id'] = $info['buyer_id'];
        } else{
            jsonReturn('','-1001','用户[buyer_id]不可以为空');
        }
        if (isset($info['lang']) && in_array($info['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($info['lang']);
        }
        $field = 'buyer_id,swift_code,bank_name,bank_account,country_code,country_bn,address,zipcode,phone,fax,turnover,profit,total_assets,reg_capital_cur_bn,equity_ratio,equity_capital,remarks,created_by,created_at';
        try{
            $buyerBankInfo =  $this->field($field)->where($where)->find();
            return $buyerBankInfo ? $buyerBankInfo : array();
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 银行信息新建/编辑-门户
     * @author klp
     */
    public function editInfo($token,$input){
        if (!isset($input))
            return false;
        try {
            if (is_array($input)) {
                $checkout = $this->checkParam($input);
                $data = [
                    'buyer_id' => $token['buyer_id'],
                    'swift_code' => $checkout['swift_code'],
                    'country_code' => $checkout['bank_country_code'],
                    'bank_name' => isset($checkout['bank_name']) ? $checkout['bank_name'] : '',
                    'bank_account' => isset($checkout['bank_account']) ? $checkout['bank_account'] : '',
                    'country_bn' => isset($checkout['country_bn']) ? $checkout['country_bn'] : '',
                    'address' => isset($checkout['bank_address']) ? $checkout['bank_address'] : '',
                    'zipcode' => isset($checkout['zipcode']) ? $checkout['zipcode'] : '',
                    'phone' => isset($checkout['phone']) ? $checkout['phone'] : '',
                    'fax' => isset($checkout['fax']) ? $checkout['fax'] : '',
                    'turnover' => isset($checkout['bank_turnover']) ? $checkout['bank_turnover'] : '',
                    'profit' => isset($checkout['bank_profit']) ? $checkout['bank_profit'] : '',
                    'total_assets' => isset($checkout['bank_assets']) ? $checkout['bank_assets'] : '',
                    'reg_capital_cur_bn' => isset($checkout['reg_capital_cur_bn']) ? $checkout['reg_capital_cur_bn'] : '',
                    'equity_capital' => isset($checkout['bank_equity_capital']) ? $checkout['bank_equity_capital'] : '',
                    'employee_count' => isset($checkout['bank_employee_count']) ? $checkout['bank_employee_count'] : '',
                    'equity_ratio' => isset($checkout['equity_ratio']) ? $checkout['equity_ratio'] : '',
                    'branch_count' => isset($checkout['branch_count']) ? $checkout['branch_count'] : 0,
                    'remarks' => isset($checkout['bank_remarks']) ? $checkout['bank_remarks'] : ''
                ];
                //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                $result = $this->field('buyer_id')->where(['buyer_id' => $token['buyer_id']])->find();
                if ($result) {
                    $result = $this->where(['buyer_id' => $token['buyer_id']])->save($data);
                    if(!$result){
                        return false;
                    }
                } else {
                    $data['created_by'] = $token['user_name'];
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $result = $this->add($data);
                    if(!$result){
                        return false;
                    }
                }
            }
            return true;
        } catch(\Kafka\Exception $e){
            return false;
        }
    }

}