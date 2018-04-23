<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/23
 * Time: 9:59
 */
class PriceStrategyDiscountModel extends PublicModel {
    //put your code here
    protected $tableName = 'price_strategy_discount';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据id获取价格折扣
     * @param $id
     */
    public function getPriceDiscountById($id){
        if(empty($id)){
            return false;
        }
        try{
            $condition = [
                'id'=>$id,
                'deleted_at'=>['exp', 'is null'],
                'validity_start' => [['exp', 'is null'], ['elt',date('Y-m-d H:i:s',time())],'or'],
                'validity_end' =>[['exp','is null'],['gt',date('Y-m-d H:i:s',time())],'or']
            ];
            $result = $this->field('discount,validity_end')->where($condition)->find();
            return $result ? $result : [];
        }catch (Exception $e){
            return false;
        }
    }

}