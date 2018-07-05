<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货
 * @author  zhongyg
 * @date    2017-12-6 9:07:59
 * @version V2.0
 * @desc
 */
class StockModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['s.deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn', 'string', 's.country_bn');
        $this->_getValue($where, $condition, 'floor_name', 'like', 'sf.floor_name');
        $this->_getValue($where, $condition, 'show_name', 'like', 's.show_name');
        $this->_getValue($where, $condition, 'floor_id', 'int', 's.floor_id');
        $this->_getValue($where, $condition, 'lang', 'string', 's.lang');
        $employee_model = new EmployeeModel();
        if (isset($condition['created_by_name']) && $condition['created_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['created_by_name']));
            if ($userids) {
                $where['s.created_by'] = ['in', $userids];
            } else {
                $where['s.created_by'] = null;
            }
        }

        if (isset($condition['updated_by_name']) && $condition['updated_by_name']) {
            $userids = $employee_model->getUseridsByUserName(trim($condition['updated_by_name']));
            if ($userids) {
                $where['s.updated_by'] = ['in', $userids];
            } else {
                $where['s.updated_by'] = null;
            }
        }
        $this->_getValue($where, $condition, 'sku', 'string', 's.sku');
        $this->_getValue($where, $condition, 'show_flag', 'bool', 'sf.show_flag');
        $this->_getValue($where, $condition, 'created_at', 'between', 's.created_at');
        $this->_getValue($where, $condition, 'updated_at', 'between', 's.updated_at');
        if(isset($condition['keyword']) && !empty($condition['keyword'])){
            $where = "s.country_bn='".$condition['country_bn']."' AND s.lang='".$condition['lang']."' AND s.deleted_at is null AND s.status='VALID' AND (s.show_name like '%".$condition['keyword']."%' OR s.sku='".$condition['keyword']."')";
            if(isset($condition['floor_id'])){
                $where.=" AND floor_id = ".intval($condition['floor_id']);
            }
        }
        return $where;
    }

    /**
     * Description of 判断国家现货是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($where) {
        $where['deleted_flag'] = 'N';

        return $this->where($where)->field('id,floor_id')->find();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getList($condition) {
        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);
        $data = [];
        $list = $this->alias('s')
                ->field('s.id,s.sku,s.show_name,s.lang,s.stock,s.floor_id,s.spu,s.country_bn,s.recommend_home,
                        s.created_at,s.updated_by,s.created_by,s.updated_at')
                ->where($where)
                ->limit($from, $size)
                ->order('s.sort_order desc')
                ->select();
        if($list){
            $this->_setUser($list);
            $data['data'] = $list;
            $data['count'] = $this->getCount($condition);
            $data['current_no'] = isset($condition['current_no']) ? $condition['current_no'] : 1;
            $data['pagesize'] = $size;
        }
        return $data;
    }

    public function getCount($condition) {
        $where = $this->_getCondition($condition);

        return $this->alias('s')->where($where)->count();
    }

    /**
     * Description of 获取现货详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getInfo($country_bn, $lang, $sku) {
        $where['country_bn'] = $country_bn;
        $where['lang'] = $lang;
        $where['sku'] = $sku;
        return $this->where($where)->find();
    }

    private function getSpu($sku, $lang) {
        $where = ['deleted_flag' => 'N',
            'lang' => $lang,
            'sku' => $sku,
        ];
        $goods_model = new GoodsModel();
        $data = $goods_model->field('spu,name,show_name,model')->where($where)->find();

        if (empty($data['show_name']) && empty($data['show_name']) && $data['spu']) {
            $prodcut_model = new ProductModel();
            $where_spu = ['deleted_flag' => 'N',
                'lang' => $lang,
                'spu' => $data['spu'],
            ];
            $product = $prodcut_model->field('spu,name,show_name')->where($where_spu)->find();

            $data['name'] = $product['name'];
            $data['show_name'] = empty($product['show_name']) ? $product['name'] : $product['show_name'];
        } elseif (empty($data['show_name']) && empty($data['show_name']) && $data['spu']) {
            $data['show_name'] = $data['name'];
        }

        return $data;
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function createData($country_bn, $skus, $lang) {
        $stock_cost_price_model = new StockCostPriceModel();
        $this->startTrans();
        foreach ($skus as $sku) {
            $row = $this->getExit(['country_bn'=>$country_bn,'lang'=>$lang,'sku'=>$sku]);
            if (!$row) {
                $goods_name = $this->getSpu($sku, $lang);
                if (empty($goods_name['spu'])) {
                    return false;
                }
                $data = [
                    'country_bn' => $country_bn,
                    'lang' => $lang,
                    'spu' => $goods_name['spu'],
                    'name' => $goods_name['name'],
                    'show_name' => $goods_name['show_name'],
                    'sku' => $sku,
                    'model' => $goods_name['model'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => defined('UID') ? UID : 0
                ];
                $flag = $this->add($data);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }

                $flag_price = $stock_cost_price_model->updateData($country_bn, $lang, $sku, false);
                if (!$flag_price) {

                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 更新
     * @author link
     * @date 2018-07-04
     * @param $condition
     * @return bool
     */
    public function updateDate($condition){
        $where['id'] = $condition['id'];
        $data = [
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => defined('UID') ? UID : 0
        ];
        if(isset($condition['recommend_home']) && $condition['recommend_home']!==''){
            $data['recommend_home'] = $condition['recommend_home'] ? 1 : 0;
        }
        if(isset($condition['stock']) && $condition['stock']!==''){
            $data['stock'] = intval($condition['stock']);
        }
        if(isset($condition['sort_order']) && $condition['sort_order']!==''){
            $data['sort_order'] = intval($condition['sort_order']);
        }
        if(isset($condition['price'])){
            $data['price'] = ($condition['price'] == '') ? null : $condition['price'];
        }
        if(isset($condition['price_strategy_type'])){
            $data['price_strategy_type'] = $condition['price_strategy_type'];
        }
        if(isset($condition['price_cur_bn'])){
            $data['price_cur_bn'] = $condition['price_cur_bn'];
        }
        if(isset($condition['price_symbol'])){
            $data['price_symbol'] = $condition['price_symbol'];
        }

        return $this->where($where)->save($data);
    }

    /**
     * 清除楼层商品
     * @author link
     * @date 2018-07-04
     */
    public function clearFloor($condition){
        $where['floor_id'] = is_array($condition['floor_id']) ? ['in',$condition['floor_id']] : trim($condition['floor_id']);
        return $this->where($where)->save(['floor_id' => 0, 'updated_at'=>date('Y-m-d H:i:s',time()), 'updated_by'=>defined('UID') ? UID : 0]);
    }



    /**
     * Description of 更新库存
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
/*    public function UpdateStock($country_bn, $sku, $lang, $stock) {

        $where = ['country_bn' => $country_bn, 'sku' => $sku, 'lang' => $lang];
        $data = [
            'stock' => $stock,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => defined('UID') ? UID : 0
        ];
        $flag = $this->where($where)->save($data);


        return $flag;
    }*/

    /**
     * Description of 更新排序
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
 /*   public function UpdateSort($country_bn, $sku, $lang, $sort_order) {

        $where = ['country_bn' => $country_bn, 'lang' => $lang];
        if (is_array($sku)) {
            $where['sku'] = ['in', $sku];
        } else {
            $where['sku'] = $sku;
        }
        $data = [
            'sort_order' => intval($sort_order),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => defined('UID') ? UID : 0
        ];
        $flag = $this->where($where)->save($data);


        return $flag;
    }*/

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function deleteData($condition) {
        $floor_id = isset($condition['floor_id']) ? $condition['floor_id'] : '';
        $ids = [];
        if(!is_array($condition['id'])){
            $ids[] = $condition['id'];
        }else{
            $ids = $condition['id'];
        }

        $this->startTrans();
        $stock_floor_model = new StockFloorModel();
        foreach ($ids as $id) {
            $row = $this->getExit(['id'=>$id]);
            if ($row) {
                $where = [ 'id' => $id ];
                if($floor_id){
                    $data = [
                        'floor_id'=>0,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => defined('UID') ? UID : 0
                    ];
                }else{
                    $data = [
                        'deleted_flag' => 'Y',
                        'deleted_at' => date('Y-m-d H:i:s'),
                        'deleted_by' => defined('UID') ? UID : 0
                    ];
                }
                $flag = $this->where($where)->save($data);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
                if ($row['floor_id']) {
                    $flag = $stock_floor_model->ChangeSkuCount($row['floor_id'], -1);
                    if (!$flag) {
                        $this->rollback();
                        return false;
                    }
                }
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 更新现货价格策略
     */
    public function updatePriceStrategyType($input){
        if(!isset($input['country_bn']) || empty($input['country_bn'])){
            jsonReturn('',MSG::ERROR_PARAM,'country_bn 不能为空');
        }
        if(!isset($input['price_strategy_type']) || empty($input['price_strategy_type'])){
            jsonReturn('',MSG::ERROR_PARAM,'price_strategy_type 不能为空');
        }

        try{
            $data = [
                'price_strategy_type' => isset($input['price_strategy_type']) ? $input['price_strategy_type'] : 1,
            ];
            $where = [];
            if($input['country_bn']){
                $where['country_bn']= ucfirst(trim($input['country_bn']));
            }
            if(isset($input['sku'])){
                if(is_array($input['sku'])){
                    $where['sku'] = ['in',$input['sku']];
                }else{
                    $where['sku']= trim($input['sku']);
                }
            }
            $data['updated_at'] = date('Y-m-d H:i:s',time());
            $data['updated_by'] = defined('UID') ? UID : 0;
            $id = $this->where($where)->save($data);
            return $id ? $id : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 更新现货价格
     */
    public function updatePrice($input){
        if(!isset($input['country_bn']) || empty($input['country_bn'])){
            jsonReturn('',MSG::ERROR_PARAM,'country_bn 不能为空');
        }

        try{
            $data = [
                'price' => isset($input['price']) ? $input['price'] : '',
                'price_symbol' => isset($input['price_symbol']) ? $input['price_symbol'] : '',
                'price_cur_bn' => isset($input['price_cur_bn']) ? $input['price_cur_bn'] : '',
            ];
            $where = [];
            if($input['country_bn']){
                $where['country_bn']= ucfirst(trim($input['country_bn']));
            }
            if(isset($input['sku'])){
                if(is_array($input['sku'])){
                    $where['sku'] = ['in',$input['sku']];
                }else{
                    $where['sku']= trim($input['sku']);
                }
            }
            $data['updated_at'] = date('Y-m-d H:i:s',time());
            $data['updated_by'] = defined('UID') ? UID : 0;
            $id = $this->where($where)->save($data);
            return $id ? $id : false;
        }catch (Exception $e){
            return false;
        }
    }

}
