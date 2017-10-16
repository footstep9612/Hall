<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportTariffModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   增值税、关税信息
 */
class ExportTariffModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_config';
    protected $tableName = 'export_tariff';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   增值税、关税信息
     */
    private function _getCondition($condition) {
        $data = [];
        $this->_getValue($data, $condition, 'lang', 'string'); //语言
        $this->_getValue($data, $condition, 'country_bn', 'string'); //国家简称
        $this->_getValue($data, $condition, 'cat_name', 'string'); //品类
        $this->_getValue($data, $condition, 'tax_no', 'string'); //税号
        $this->_getValue($data, $condition, 'unit', 'string'); //计量单位
        $this->_getValue($data, $condition, 'created_by', 'string'); //创建人
        $this->_getValue($data, $condition, 'status', 'string', 'status'); //状态
        if (!$data['zh.status']) {
            $data['zh.status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'url', 'like', 'zh.url');

        return $data;
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @param string $order 排序
     * @param bool $type 是否分页
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $data = $this->_getCondition($condition);

            $this->field('id,lang,country_bn,cat_name,tax_no,unit,'
                            . 'supervised_criteria,tax_rebate_rate,export_tariff_rate,'
                            . 'import_tariff_rate,va_tax_rate,hs,remarks,status,created_by,'
                            . 'created_at,updated_by,updated_at,checked_by,checked_at')
                    ->where($data);

            return $this->order($order)
                            ->select();
        } catch (Exception $ex) {
            print_r($ex);
            return [];
        }
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {

            return 0;
        }
    }

    /**
     * Description of 详情
     * @param string $bn 区域简码
     * @param string $lang 语言 默认英文
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function info($bn = '', $lang = 'en') {
        $where['bn'] = $bn;
        $where['lang'] = $lang;
        if (!empty($where)) {
            $row = $this->where($where)
                    ->field('id,lang,country_bn,cat_name,tax_no,unit,'
                            . 'supervised_criteria,tax_rebate_rate,export_tariff_rate,'
                            . 'import_tariff_rate,va_tax_rate,hs,remarks,status,created_by,'
                            . 'created_at,updated_by,updated_at,checked_by,checked_at')
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            return $this->where($where)
                            ->delete();
        } else {
            return false;
        }
    }

    /**
     * Description of 判断数据是否存在
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function Exits($where) {

        return $this->_exist($where);
    }

    /**
     * Description of 增
     * @param array $create 新增的数据
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function create_data($create = [], $uid = 0) {
        $create['created_by'] = defined('UID') ? UID : 0;
        $create['created_at'] = date('Y-m-d H:i:s');
        $create['cat_name'] = $create['country_bn'];
        $create['unit'] = 1;
        $create['tax_rebate_rate'] = 0;
        $create['tax_no'] = $create['country_bn'];
        $create['export_tariff_rate'] = 0;
        $create['hs'] = $create['country_bn'];
        $create['supervised_criteria'] = $create['country_bn'];
        $data = $this->create($create);

        var_dump($data);
        return $this->add($data);
//        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
////            $datalist = [];
////            $arr['bn'] = ucwords($create['en']['name']);
////            $create['en']['name'] = ucwords($create['en']['name']);
////            foreach ($create as $key => $name) {
////                $arr['lang'] = $key;
////                $arr['name'] = $name;
////                $datalist[] = $arr;
////            }
//            return $this->addAll($datalist);
//        } else {
//            return false;
//        }
    }

}
