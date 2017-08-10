<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogiRateModel
 * @author  zhongyg
 * @date    2017-8-3 15:39:09
 * @version V2.0
 * @desc
 */
class LogiRateModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_config';
    protected $tableName = 'logi_rate';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    private function _getCondition($condition) {
        $data = [];
        $data['zh.lang'] = 'zh';
        //$this->_getValue($data, $condition, 'lang', 'string');
        $this->_getValue($data, $condition, 'trade_terms_bn', 'string');
        $this->_getValue($data, $condition, 'trans_mode_bn', 'string');
        $this->_getValue($data, $condition, 'from_country', 'string');
        $this->_getValue($data, $condition, 'from_port', 'string');
        $this->_getValue($data, $condition, 'from_port', 'string');
        $this->_getValue($data, $condition, 'from_port', 'string');
        if (!$data['status']) {
            $data['status'] = 'VALID';
        }


        return $data;
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @param string $order 排序
     * @param bool $type 是否分页
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function getlist($condition, $order = 'zh.id desc') {
        try {
            $data = $this->_getCondition($condition);

            $this->alias('zh')
                    ->join('erui2_operation.market_area as en on '
                            . 'en.bn=zh.bn and en.lang=\'en\' and en.`status` = \'VALID\' ', 'left')
                    ->field('zh.bn,zh.parent_bn,zh.name as zh_name,zh.url,en.name as en_name ')
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
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
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
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function info($bn = '', $lang = 'en') {
        $where['bn'] = $bn;
        $where['lang'] = $lang;
        if (!empty($where)) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,url')
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
        if (is_array($id)) {
            $where['id'] = ['in', $id];
        } else {
            $where['id'] = $id;
        }

        if (!empty($where['id'])) {
            return $this->where($where)->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);
        } else {
            return false;
        }
    }

    /**
     * Description of 判断数据是否存在
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function Exits($where) {

        return $this->_exist($where);
    }

    /**
     * Description of 增
     * @param array $create 新增的数据
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function create_data($create = []) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $datalist = [];
            $arr['bn'] = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);


            foreach ($create as $key => $name) {
                $arr['lang'] = $key;
                $arr['name'] = $name;
                $arr['created_by'] = defined('UID') ? UID : 0;
                $arr['created_at'] = date('Y-m-d H:i:s');
                $datalist[] = $arr;
            }
            return $this->addAll($datalist);
        } else {
            return false;
        }
    }

    /**
     * Description of 修改
     * @param array $create 新增的数据
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function update_data($create = []) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $datalist = [];
            $where['bn'] = $create['bn'];
            $arr['bn'] = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);
            $langs = ['en', 'zh', 'es', 'ru'];
            foreach ($langs as $lang) {
                $where['lang'] = $lang;
                if ($this->Exits($where)) {
                    $arr['lang'] = $lang;
                    $arr['name'] = $create[$lang]['name'];
                    $arr['updated_by'] = defined('UID') ? UID : 0;
                    $arr['updated_at'] = date('Y-m-d H:i:s');
                    $this->where($where)->save($arr);
                } else {

                    $arr['lang'] = $lang;
                    $arr['name'] = $create[$lang]['name'];
                    $arr['updated_by'] = defined('UID') ? UID : 0;
                    $arr['updated_at'] = date('Y-m-d H:i:s');
                    $datalist[] = $arr;
                }
            }
            return $this->addAll($datalist);
        } else {
            return false;
        }
    }

}
