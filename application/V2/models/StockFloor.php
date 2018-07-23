<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货楼层
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class StockFloorModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_floor';
    protected $dbName = 'erui_stock';

    const SHOW_TYPE_P = 'P';
    const SHOW_TYPE_A = 'A';
    const SHOW_TYPE_M = 'M';
    const SHOW_TYPE_AP = 'AP';
   const SHOW_TYPE_PM = 'PM';
    const SHOW_TYPE_AM = 'AM';
    const SHOW_TYPE_APM = 'APM';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'country_bn');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'floor_name', 'like');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'onshelf_flag', 'bool');
        $this->_getValue($where, $condition, 'lang');
        switch ($condition['show_type']) {
            case self::SHOW_TYPE_P:
                $where['show_type'] = self::SHOW_TYPE_P;
                break;
            case self::SHOW_TYPE_M:
                $where['show_type'] = self::SHOW_TYPE_M;
                break;
            case self::SHOW_TYPE_A:
                $where['show_type'] = self::SHOW_TYPE_A;
                break;
            case self::SHOW_TYPE_AP:
                $where['show_type'] = self::SHOW_TYPE_AP;
                break;
            case self::SHOW_TYPE_AM:
                $where['show_type'] = self::SHOW_TYPE_AM;
                break;
            case self::SHOW_TYPE_PM:
                $where['show_type'] = self::SHOW_TYPE_PM;
                break;
            case self::SHOW_TYPE_APM:
                $where['show_type'] = self::SHOW_TYPE_APM;
                break;
        }

        return $where;
    }

    /**
     * Description of 判断现货楼层是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $floor_name, $lang, $id = null, $show_type = 'P') {

        $where['country_bn'] = trim($country_bn);
        $where['floor_name'] = trim($floor_name);
        $where['lang'] = trim($lang);
        if ($id) {
            $where['id'] = ['neq', $id];
        }
        switch ($show_type) {
            case 'P':
                $where['show_type'] = ['in', ['APM', 'P', 'PM', 'AP']];
                break;
            case 'M':
                $where['show_type'] = ['in', ['APM', 'M', 'PM', 'AM']];
                break;
            case 'A':
                $where['show_type'] = ['in', ['APM', 'A', 'AP', 'AM']];
                break;
            default : $where['show_type'] = ['in', ['APM', 'P', 'PM', 'AP']];
                break;
        }
        return $this->where($where)->getField('id');
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function getList($condition) {
        $where = $this->_getCondition($condition);
        list($from, $size) = $this->_getPage($condition);

        return $this->where($where)
                        ->limit($from, $size)
                        ->select();
    }

    /**
     * Description of 获取SPU关联列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  SPU关联
     */
    public function getCont($condition) {
        $where = $this->_getCondition($condition);
        return $this->where($where)
                        ->count();
    }

    /**
     * Description of 获取现货楼层详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function getInfo($id) {
        $where['id'] = $id;

        return $this->where($where)->find();
    }

    /**
     * Description of 新加现货楼层
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function createData($condition) {
        $condition['country_bn'] = trim($condition['country_bn']);
        $condition['floor_name'] = trim($condition['floor_name']);
        $condition['onshelf_flag'] = trim($condition['onshelf_flag']) == 'Y' ? 'Y' : 'N';
        $condition['sku_count'] = intval($condition['sku_count']);
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['deleted_flag'] = 'N';
        $this->startTrans();
        try {
            $data = $this->create( $condition );
            $data[ 'created_at' ] = date( 'Y-m-d H:i:s' );
            $data[ 'created_by' ] = defined( 'UID' ) ? UID : 0;
            switch ( $condition[ 'show_type' ] ) {
                case self::SHOW_TYPE_A:
                    $data[ 'show_type' ] = self::SHOW_TYPE_A;
                    break;
                case self::SHOW_TYPE_P:
                    $data[ 'show_type' ] = self::SHOW_TYPE_P;
                    break;
                case self::SHOW_TYPE_M:
                    $data[ 'show_type' ] = self::SHOW_TYPE_M;
                    break;
                case self::SHOW_TYPE_PM:
                    $data[ 'show_type' ] = self::SHOW_TYPE_PM;
                    break;
                case self::SHOW_TYPE_AP:
                    $data[ 'show_type' ] = self::SHOW_TYPE_AP;
                    break;
                case self::SHOW_TYPE_AM:
                    $data[ 'show_type' ] = self::SHOW_TYPE_AM;
                    break;
                case self::SHOW_TYPE_APM:
                    $data[ 'show_type' ] = self::SHOW_TYPE_APM;
                    break;
                default :
                    $data[ 'show_type' ] = self::SHOW_TYPE_P;
                    break;
            }
            $id = $this->add( $data );
            if ( isset( $condition[ 'ads' ] ) && !empty( $condition[ 'ads' ] ) ) {
                $sfadsModel = new StockFloorAdsModel();
                $rel = $sfadsModel->createData( $condition[ 'ads' ] ,$id , $condition['country_bn'], $condition['lang'] );
                if ( !$rel ) {
                    $this->rollback();
                    return false;
                }
            }
            if ( $id ) {
                $this->commit();
                return $id;
            } else {
                $this->rollback();
                return false;
            }
        }catch (Exception $e){
            $this->rollback();
            return false;
        }
    }

    /**
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function updateData($id, $condition) {
        $condition['country_bn'] = trim($condition['country_bn']);
        $condition['floor_name'] = trim($condition['floor_name']);
        $condition['onshelf_flag'] = trim($condition['onshelf_flag']) == 'Y' ? 'Y' : 'N';
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['deleted_flag'] = 'N';
        $this->startTrans();
        try{
            $data = $this->create($condition);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = defined('UID') ? UID : 0;
            switch ($condition['show_type']) {
                case self::SHOW_TYPE_A:
                    $data['show_type'] = self::SHOW_TYPE_A;
                    break;
                case self::SHOW_TYPE_P:
                    $data['show_type'] = self::SHOW_TYPE_P;
                    break;
                case self::SHOW_TYPE_M:
                    $data['show_type'] = self::SHOW_TYPE_M;
                    break;
                case self::SHOW_TYPE_PM:
                    $data['show_type'] = self::SHOW_TYPE_PM;
                    break;
                case self::SHOW_TYPE_AP:
                    $data['show_type'] = self::SHOW_TYPE_AP;
                    break;
                case self::SHOW_TYPE_AM:
                    $data['show_type'] = self::SHOW_TYPE_AM;
                    break;
                case self::SHOW_TYPE_APM:
                    $data['show_type'] = self::SHOW_TYPE_APM;
                    break;
            }
            $rel = $this->where(['id' => $id])->save($data);
            if ( isset( $condition[ 'ads' ] ) && !empty( $condition[ 'ads' ] ) ) {
                $sfadsModel = new StockFloorAdsModel();
                $rel = $sfadsModel->updateData( $condition[ 'ads' ],$id, $condition['country_bn'],$condition['lang'] );
                if ( !$rel ) {
                    $this->rollback();
                    return false;
                }
            }
            if ( $rel ) {
                $this->commit();
                return $rel;
            } else {
                $this->rollback();
                return false;
            }
        }catch (Exception $e){
            $this->rollback();
            return false;
        }

    }

    /**
     * Description of 更新现货楼层上下架状态
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function onshelfData($id, $onshelf_flag) {

        $condition['onshelf_flag'] = (trim($onshelf_flag) == 'Y' || $onshelf_flag===true) ? 'Y' : 'N';
        $condition['deleted_flag'] = 'N';
        $data = $this->create($condition);
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

    /**
     * Description of 更新产品数量
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function ChangeSkuCount($floor_id, $count = 1) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['updated_by'] = defined('UID') ? UID : 0;

        $data['sku_count'] = ['exp', 'sku_count+' . $count];
        return $this->where(['id' => $floor_id])->save($data);
    }

    /**
     * Description of 更新产品数量
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function addGoods($floor_id, $country_bn, $lang, $skus) {

        $this->startTrans();
        $stock_model = new StockModel();
        foreach ($skus as $sku) {
            $flag = $stock_model->where(['lang' => $lang,
                        'country_bn' => $country_bn,
                        'sku' => $sku['sku']])->save(['floor_id' => $floor_id, 'sort_order'=>(isset($sku['sort_order']) && $sku['sort_order']) ? intval($sku['sort_order']) : 0, 'recommend_home'=>(isset($sku['recommend_home']) && ($sku['recommend_home']===true || $sku['recommend_home']=='Y' || $sku['recommend_home']==1)) ? 'Y' : 'N',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => defined('UID') ? UID : 0
            ]);
            if (!$flag) {
                $this->rollback();
                return false;
            }
            $this->ChangeSkuCount($floor_id, 1);
        }
        $this->commit();
        return true;
    }

    /**
     * 删除
     * @author link
     */
    public function deleteData($condition){
        if(!isset($condition['id'])){
            jsonReturn('', MSG::ERROR_PARAM, '请选择楼层ＩＤ');
        }
        if(is_array($condition['id'])){
            $where['id'] = ['in', $condition['id']];
        }else{
            $where['id'] = trim($condition['id']);
        }
        return $this->where($where)->save(['deleted_at'=>date('Y-m-d H:i:s',time()), 'deleted_by'=> defined("UID") ? UID : 0, "deleted_flag"=>"Y"]);
    }

}
