<?php
/**
 * 价格策略折扣
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:50
 */
class PriceStrategyDiscountModel extends PublicModel{
    //put your code here
    protected $tableName = 'price_strategy_discount';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 新增
     * @param $input
     * @return bool|mixed
     */
    public function createData($input){
        if(empty($input['sku']) || empty($input['country_bn']) ||  empty($input['discount'])){
            return false;
        }
        try{
            $data = [
                'country_bn' => ucfirst(trim($input['country_bn'])),
                'sku' => trim($input['sku']),
            ];
            if($this->getExit($data)===false){
                $data['discount'] = trim($input['discount']);
                $data['name'] = trim($input['name']);
                $data['min_purchase_qty'] = isset($input['min_purchase_qty']) ? intval($input['min_purchase_qty']) : 1;
                $data['max_purchase_qty'] = isset($input['max_purchase_qty']) ? intval($input['max_purchase_qty']) : null;
                $data['validity_start'] = isset($input['validity_start']) ? trim($input['validity_start']) : null;
                $data['validity_end'] = isset($input['validity_end']) ? trim($input['validity_end']) : null;
                $data['created_at'] = date('Y-m-d H:i:s',time());
                $data['created_by'] = defined('UID') ? UID : 0;
                $flag = $this->add($data);
                return $flag ? $flag : false;
            }
        }catch (Exception $e){
            return false;
        }

    }

    /**
     * 更新
     * @param $input
     * @return bool|mixed
     */
    public function updateData($input){
        if(empty($input['country_bn'])){
            jsonReturn('',MSG::MSG_FAILED, 'country_bn不能为空');
        }
        if(empty($input['sku'])){
            jsonReturn('',MSG::MSG_FAILED,'sku不能为空');
        }
        try{
            $data = [];
            foreach($input as $k=>$v){
                if(in_array($k,['discount','name','min_purchase_qty','max_purchase_qty','validity_start','validity_end'])){
                    $data[$k] = trim($v);
                }
            }
            if(empty($data)){
               return false;
            }
            $data['updated_at'] = date('Y-m-d H:i:s',time());
            $data['updated_by'] = defined('UID') ? UID : 0;

            $where =['sku'=>trim($input['sku']),'country_bn'=>ucfirst(trim($input['country_bn']))];
            $flag = $this->where($where)->save($data);
            unset($data);
            return $flag ? $flag : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @param $input
     * @return bool|mixed
     */
    public function deleteData($input){
        if(empty($input['country_bn'])){
            jsonReturn('',MSG::MSG_FAILED, 'country_bn不能为空');
        }
        if(empty($input['sku'])){
            jsonReturn('',MSG::MSG_FAILED,'sku不能为空');
        }
        try{
            $data = [];
            $data['deleted_at'] = date('Y-m-d H:i:s',time());
            $data['deleted_by'] = defined('UID') ? UID : 0;

            $where =['sku'=>trim($input['sku']),'country_bn'=>ucfirst(trim($input['country_bn']))];
            $flag = $this->where($where)->save($data);
            return $flag ? $flag : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 检测是否存在
     * @param array $where
     * @return bool|mixed
     */
    public function getExit($where=[]){
        try{
            $result = $this->field('id')->where($where)->find();
            return $result ? true : false;
        }catch (Exception $e){
            return ['code'=>0, 'error'=>$e];
        }
    }
}