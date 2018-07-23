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
class StockFloorAdsModel extends PublicModel {

    //put your code here
    protected $tableName = 'stock_floor_ads';
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
        $this->_getValue($where, $condition, 'floor_id');
        $this->_getValue($where, $condition, 'created_by');
        $this->_getValue($where, $condition, 'img_name', 'like');


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
    public function getExit($condition, $id = null, $show_type = 'P') {

        $where['country_bn'] = trim($condition['country_bn']);
        $where['floor_id'] = trim($condition['floor_id']);
        $where['img_name'] = trim($condition['img_name']);

        $where['lang'] = trim($condition['lang']);
        $where['group'] = trim($condition['group']);

        if ($id) {
            $where['id'] = ['neq', $id];
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
     * 根据条件获取楼层图
     * @param $condition
     * @return mixed
     */
    public function getData($condition) {
        if(!isset($condition['deleted_at'])){
            $condition['deleted_at'] = ['exp','is null'];
        }
        if(!isset($condition['status'])){
            $condition['status'] = 'VALID';
        }

        return $this->field('id,country_bn,lang,floor_id,sort_order,img_url,img_name,link,group,status,created_at,created_by,updated_at,updated_by,show_type')->where($condition)->select();
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
    public function createData($input,$floor_id,$country_bn,$lang) {
        if(count($input) == count($input, 1)){
            $inputData[] = $input;
        }else{
            $inputData = $input;
        }
        $data = [];
        foreach($inputData as $key =>$condition){
            $info = [];
            if (empty($condition['show_type']) && !empty($condition['floor_id'])) {
                $home_floor_model = new HomeFloorModel ();
                $info = $home_floor_model->getInfo($condition['floor_id']);
            } elseif (!empty($condition['show_type'])) {
                $info['show_type'] = $condition['show_type'];
            }

            switch ($info['show_type']) {
                case self::SHOW_TYPE_A:
                    $condition['show_type'] = self::SHOW_TYPE_A;
                    break;
                case self::SHOW_TYPE_P:
                    $condition['show_type'] = self::SHOW_TYPE_P;
                    break;
                case self::SHOW_TYPE_M:
                    $condition['show_type'] = self::SHOW_TYPE_M;
                    break;
                case self::SHOW_TYPE_PM:
                    $condition['show_type'] = self::SHOW_TYPE_PM;
                    break;
                case self::SHOW_TYPE_AP:
                    $condition['show_type'] = self::SHOW_TYPE_AP;
                    break;
                case self::SHOW_TYPE_AM:
                    $condition['show_type'] = self::SHOW_TYPE_AM;
                    break;
                case self::SHOW_TYPE_APM:
                    $condition['show_type'] = self::SHOW_TYPE_APM;
                    break;
                default : $condition['show_type'] = self::SHOW_TYPE_P;
                    break;
            }
            $condition['country_bn'] = $country_bn;
            $condition['img_url'] = trim($condition['img_url']);
            $condition['lang'] = $lang;
            $condition['img_name'] = trim($condition['img_name']);
            $condition['floor_id'] = $floor_id;
            $condition['sort_order'] = intval($condition['sort_order']);
            $condition['group'] = trim($condition['group']);
            $condition['link'] = trim($condition['link']);
            $condition['deleted_flag'] = 'N';
            $condition = $this->create($condition);
            $condition['created_at'] = date('Y-m-d H:i:s');
            $condition['created_by'] = defined('UID') ? UID : 0;
            unset($condition['id']);
            $data[] = $condition;
            unset($condition);
        }
        return $this->addAll($data);
    }

    /**
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货楼层
     */
    public function updateData($input,$floor_id,$country_bn,$lang) {
        if(count($input) == count($input, 1)){
            $inputData[] = $input;
        }else{
            $inputData = $input;
        }
        foreach($inputData as $key =>$condition){
            $condition['country_bn'] = ucfirst(strtolower($country_bn));
            $condition['img_url'] = trim($condition['img_url']);
            $condition['img_name'] = trim($condition['img_name']);
            $condition['floor_id'] = $floor_id;
            $condition['lang'] = $lang;
            $condition['group'] = trim($condition['group']);
            $condition['link'] = trim($condition['link']);
            $condition['sort_order'] = intval($condition['sort_order']);
            $condition['deleted_flag'] = 'N';
            $data = $this->create($condition);
            $data['updated_at'] = date('Y-m-d H:i:s');
            $data['updated_by'] = defined('UID') ? UID : 0;
            $info = [];
            if (empty($condition['show_type']) && !empty($condition['floor_id'])) {
                $stock_floor_model = new StockFloorModel ();
                $info = $stock_floor_model->getInfo($condition['floor_id']);
            } elseif (!empty($condition['show_type'])) {
                $info['show_type'] = $condition['show_type'];
            }
            switch ($info['show_type']) {
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
                default : $data['show_type'] = self::SHOW_TYPE_P;
                    break;
            }
            if(isset($condition['id']) && !empty($condition['id'])){
                $rel = $this->where(['id' => $condition['id']])->save($data);
            }else{
                $data['created_at'] = date('Y-m-d H:i:s',time());
                $data['created_by'] = defined('UID') ? UID : 0;
                unset($data['updated_at'],$data['updated_by']);
                $rel = $this->add($data);
            }
            if(!$rel){
                return false;
            }
        }
        return true;
    }

    /**
     * Description of 删除广告
     * @author  link
     * @date    2017-07-04
     */
    public function deletedData($condition) {
        if(isset($condition['id'])){
            $where['id'] = is_array($condition['id']) ? ['in',$condition['id']] : trim($condition['id']);
        }elseif(isset($condition['floor_id'])){
            $where['floor_id'] = is_array($condition['floor_id']) ? ['in',$condition['floor_id']] : trim($condition['floor_id']);
        }

        $data['deleted_flag'] = 'Y';
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $data['deleted_by'] = defined('UID') ? UID : 0;

        return $this->where($where)->save($data);
    }

}
