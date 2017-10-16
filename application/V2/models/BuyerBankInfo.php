<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 15:09
 */
class BuyerBankInfoModel extends PublicModel{
    protected $tableName = 'buyer_bank_info';
    protected $dbName = 'erui_buyer'; //数据库名称

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    /**
     * 获取银行信息
     * @author klp
     */
    public function getBuyerBankInfo($userInfo)
    {
//        $info = getLoinInfo();
        $where=array();
        if(isset($userInfo['buyer_id']) && !empty($userInfo['buyer_id'])) {
            $where['buyer_id'] = $userInfo['buyer_id'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        if (isset($info['lang']) && in_array($info['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($info['lang']);
        }
        $where['deleted_flag'] = 'N';
        $field = 'buyer_id,swift_code,bank_name,bank_account,country_code,country_bn,address,zipcode,phone,fax,turnover,profit,total_assets,reg_capital_cur_bn,equity_ratio,equity_capital,branch_count,employee_count,remarks,created_by,created_at';
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
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $result = $this->add($data);
                    if(!$result){
                        return false;
                    }
                }
            } else{
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
    private function checkParam($param = []) {
        if (empty($param)) {
            return false;
        }
        $data = $results = array();
        if(!empty($param['bank_name'])) {
            $data['bank_name'] = $param['bank_name'];
        } else{
            $results['code'] = -101;
            $results['message'] = '[bank_name]不能为空!';
        }
        if(!empty($param['bank_address'])) {
            $data['address'] = $param['bank_address'];
        } else{
            $results['code'] = -101;
            $results['message'] = '[bank_address]不能为空!';
        }
        if(!empty($param['bank_country_code'])) {
            $data['country_code']['country_code'] = $param['bank_country_code'];
        } else{
            $results['code'] = -101;
            $results['message'] = '[bank_country_code]不能为空!';
        }
        if(!empty($param['swift_code'])) {
            $data['swift_code'] = $param['swift_code'];
        } else{
            $results['code'] = -101;
            $results['message'] = '[swift_code]不能为空!';
        }
        if(!empty($param['bank_account'])) {
            $data['bank_account'] = $param['bank_account'];
        }
        if(!empty($param['country_bn'])) {
            $data['country_bn'] = $param['country_bn'];
        }
        if(!empty($param['bank_zipcode'])) {
            $data['country_code'] = $param['bank_zipcode'];
        }
        if(!empty($param['bank_equity_capital'])) {
            $data['country_code'] = $param['bank_equity_capital'];
        }
        if(!empty($param['bank_phone'])) {
            $data['phone'] = $param['bank_phone'];
        }
        if(!empty($param['bank_fax'])) {
            $data['fax'] = $param['bank_fax'];
        }
        if(!empty($param['bank_turnover'])) {
            $data['turnover'] = $param['bank_turnover'];
        }
        if(!empty($param['bank_profit'])) {
            $data['profit'] = $param['bank_profit'];
        }
        if(!empty($param['bank_assets'])) {
            $data['total_assets'] = $param['bank_assets'];
        }
        if(!empty($param['reg_capital_cur_bn'])) {
            $data['reg_capital_cur_bn'] = $param['reg_capital_cur_bn'];
        }
        if(!empty($param['bank_equity_capital'])) {
            $data['equity_capital'] = $param['bank_equity_capital'];
        }
        if(!empty($param['bank_employee_count'])) {
            $data['employee_count'] = $param['bank_employee_count'];
        }
        if(!empty($param['bank_equity_ratio'])) {
            $data['equity_ratio'] = $param['bank_equity_ratio'];
        }
        if(!empty($param['branch_count'])) {
            $data['branch_count'] = $param['branch_count'];
        }
        if(!empty($param['bank_remarks'])) {
            $data['remarks'] = $param['bank_remarks'];
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
    }

}