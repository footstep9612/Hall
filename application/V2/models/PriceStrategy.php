<?php
/**
 * 价格策略
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/7/5
 * Time: 13:49
 */
class PriceStrategyModel extends PublicModel{
    protected $tableName = 'price_strategy';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 列表
     * @param $condition
     * @return bool|mixed
     */
    public function getList($condition){
        $where = [
            'lang' => $condition['lang']
        ];
        try{
            return $this->field('id,lang,flag,name,remark')->where($where)->order('sort_order DESC')->select();
        }catch (Exception $e) {
            return false;
        }
    }
}