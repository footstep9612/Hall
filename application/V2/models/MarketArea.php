<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarketAreaModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   营销区域
 */
class MarketAreaModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_operation';
    protected $tableName = 'market_area';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    private function _getCondition($condition) {
        $data = [];
        $data['zh.lang'] = 'zh';
        //$this->_getValue($data, $condition, 'lang', 'string');
        $this->_getValue($data, $condition, 'bn', 'string', 'zh.bn');
        $this->_getValue($data, $condition, 'parent_bn', 'string', 'zh.parent_bn');
        $this->_getValue($data, $condition, 'name', 'like', 'zh.name');
        $this->_getValue($data, $condition, 'status', 'string', 'zh.status');
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
     * @desc   营销区域
     */
    public function getlist($condition, $order = 'zh.id desc') {
        try {
            $data = $this->_getCondition($condition);

            $this->alias('zh')
                    ->join('erui2_operation.market_area as en on '
                            . 'en.bn=zh.bn and en.lang=\'en\' and en.`status` = \'VALID\' ', 'inner')
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
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
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
     * @desc   营销区域
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
        $where['id'] = $id;
        if (!empty($where['id'])) {
            try {


                $flag = $this->where($where)
                        ->delete();

                return $flag;
            } catch (Exception $ex) {
                Log::write($ex->getMessage(), Log::ERR);
            }
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
     * @desc   营销区域
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
     * @desc   营销区域
     */
    public function create_data($create = [], $uid = 0) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $newbn = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);
            $langs = ['en', 'zh', 'es', 'ru'];
            $this->startTrans();
            foreach ($langs as $lang) {
                $create['bn'] = $newbn;
                $flag = $this->_updateandcreate($create, $lang, $newbn, $uid);
                if (!$flag) {
                    $this->rollback();

                    return false;
                }
            }
            $market_area_team_model = new MarketAreaTeamModel();
            $market_area_team_model->updateandcreate($create, $newbn, $uid);
            $this->commit();

            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $uid) {
        if (!isset($data['bn']) || !$data['bn']) {
            return false;
        }
        $newbn = ucwords($data['en']['name']);
        $data['en']['name'] = ucwords($data['en']['name']);
        $this->startTrans();
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $flag = $this->_updateandcreate($data, $lang, $newbn, $uid);
            if (!$flag) {
                $this->rollback();

                return false;
            }
        }
        $market_area_team_model = new MarketAreaTeamModel();
        $market_area_team_model->updateandcreate($data, $newbn, $uid);
        $this->commit();

        return true;
    }

    private function _updateandcreate($data, $lang, $newbn, $uid = 0) {
        if (isset($data[$lang]['name'])) {
            $where['lang'] = $lang;
            $where['bn'] = $data['bn'];
            $arr['bn'] = $newbn;
            $arr['lang'] = $lang;
            $arr['name'] = $data[$lang]['name'];
            if ($this->Exits($where)) {
                $arr['updated_at'] = date('Y-m-d H:i:s');
                $arr['updated_by'] = $uid;
                $flag = $this->where($where)->save($arr);
                return $flag;
            } else {
                $arr['updated_at'] = date('Y-m-d H:i:s');
                $arr['updated_by'] = $uid;
                $arr['created_at'] = date('Y-m-d H:i:s');
                $arr['created_by'] = $uid;
                $flag = $this->add($arr);
                return $flag;
            }
        } else {
            return true;
        }
    }

}
