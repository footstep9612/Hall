<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 导航关键词
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class HomeCountryNavModel extends PublicModel {

    //put your code here
    protected $tableName = 'home_country_nav';
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
        if(isset($condition['group'])){
            $where['group'] = strtoupper($condition['group']);
        }
        if(isset($condition['group_id'])){
            $where['group_id'] = trim($condition['group_id']);
        }
        if(isset($condition['country_bn'])){
            $where['country_bn'] = trim($condition['country_bn']);
        }
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'nav_name', 'like');
        $this->_getValue($where, $condition, 'created_by');
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
     * Description of 判断导航是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  导航
     */
    public function getExit($condition, $id = null) {
        $where['group'] = strtoupper($condition['group']);
        $where['nav_name'] = trim($condition['nav_name']);
        $where['nav_url'] = trim($condition['nav_url']);
        $where['lang'] = trim($condition['lang']);
        if(isset($condition['country_bn'])){
            $where['country_bn'] = trim($condition['country_bn']);
        }
        if(isset($condition['group_id'])){
            $where['group_id'] = trim($condition['group_id']);
        }
        $this->_getValue($where, $condition, 'created_at', 'between');
        $where['deleted_at'] = ['exp','is null'];
        if ($id) {
            $where['id'] = ['neq', $id];
        }

        switch ($condition['show_type']) {
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
     * Description of 获取导航列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  导航
     */
    public function getList($condition) {
        try{
            $where = $this->_getCondition($condition);
            list($from, $size) = $this->_getPage($condition);

            $list = $this->where($where)->order('sort_order DESC')
                ->limit($from, $size)
                ->select();
            $data = [];
            if($list){
                $data['data']=$list;
                $count = $this->getCont($condition);
                $data['count'] = $count ? $count : 0;
                $data['current_no'] = isset($condition['current_no']) ? intval($condition['current_no']) : 1;
                $data['pagesize'] = $size;
            }
            return $data;
        }catch (Exception $e){
            return false;
        }
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
     * Description of 获取导航详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  导航
     */
    public function getInfo($id) {
        $where['id'] = $id;

        return $this->where($where)->find();
    }

    /**
     * Description of 新加导航
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  导航
     */
    public function createData($condition) {
        $condition['group'] = strtoupper($condition['group']);
        if(isset($condition['country_bn'])){
            $condition['country_bn'] = trim($condition['country_bn']);
        }
        if(isset($condition['group_id'])){
            $condition['group_id'] = trim($condition['group_id']);
        }
        $condition['nav_name'] = trim($condition['nav_name']);
        $condition['nav_url'] = trim($condition['nav_url']);
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['lang'] = trim($condition['lang']);
        $condition['deleted_flag'] = 'N';
        $data = $this->create($condition);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;
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
            default : $data['show_type'] = self::SHOW_TYPE_P;
                break;
        }
        return $this->add($data);
    }

    /**
     * Description of 更新导航
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  导航
     */
    public function updateData($condition, $id) {
        $condition['group'] = strtoupper($condition['group']);
        if(isset($condition['country_bn'])){
            $condition['country_bn'] = trim($condition['country_bn']);
        }
        if(isset($condition['group_id'])){
            $condition['group_id'] = trim($condition['group_id']);
        }
        $condition['sort_order'] = intval($condition['sort_order']);
        $condition['nav_name'] = trim($condition['nav_name']);
        $condition['nav_url'] = trim($condition['nav_url']);
        $condition['lang'] = trim($condition['lang']);
        $condition['deleted_flag'] = 'N';
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
            default :
                break;
        }
        return $this->where(['id' => $id])->save($data);
    }

    /**
     * Description of 删除广告
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function DeletedData($id) {


        $data['deleted_flag'] = 'Y';
        $data['deleted_at'] = date('Y-m-d H:i:s');
        $data['deleted_by'] = defined('UID') ? UID : 0;

        return $this->where(['id' => $id])->save($data);
    }

}
