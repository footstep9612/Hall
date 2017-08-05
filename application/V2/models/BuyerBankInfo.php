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



}