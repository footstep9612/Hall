<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class Goods_MaterialCatModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'material_cat'; //数据表表名
    protected $langs = ['en', 'es', 'zh', 'ru'];

    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zyg
     *
     */
    protected function _getcondition($condition = []) {
        $where = [];
        $this->_getValue($where, $condition, 'id', 'string');
        $this->_getValue($where, $condition, 'cat_no', 'string');
        if (isset($condition['cat_no3']) && $condition['cat_no3']) {
            $where['cat_no'] = $condition['cat_no3'];
        } elseif (isset($condition['cat_no2']) && $condition['cat_no2']) {
            $where['level_no'] = 3;
            $where['parent_cat_no'] = $condition['cat_no2'];
        } elseif (isset($condition['cat_no1']) && $condition['cat_no1']) {
            $where['level_no'] = 2;
            $where['parent_cat_no'] = $condition['cat_no1'];
        } elseif (isset($condition['level_no']) && intval($condition['level_no']) <= 3) {
            $where['level_no'] = intval($condition['level_no']);
        }
        $this->_getValue($where, $condition, 'parent_cat_no', 'string');
        $this->_getValue($where, $condition, 'mobile', 'like');
        $this->_getValue($where, $condition, 'lang', 'string');
        $this->_getValue($where, $condition, 'name', 'like');
        $this->_getValue($where, $condition, 'sort_order', 'string');
        $this->_getValue($where, $condition, 'created_at', 'between');
        $this->_getValue($where, $condition, 'created_by', 'string');
        if (isset($condition['status'])) {
            switch ($condition['status']) {

                case self::STATUS_DELETED:
                    $where['status'] = $condition['status'];
                    break;
                case self::STATUS_DRAFT:
                    $where['status'] = $condition['status'];
                    break;
                case self::STATUS_APPROVING:
                    $where['status'] = $condition['status'];
                    break;
                case self::STATUS_VALID:
                    $where['status'] = $condition['status'];
                    break;
                default : $where['status'] = self::STATUS_VALID;
            }
        } else {
            $where['status'] = self::STATUS_VALID;
        }

        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getCount($condition = []) {
        $where = $this->_getcondition($condition);
        try {
            return $this->where($where)
                            ->count('id');
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 分类树形
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function tree($condition = []) {
        $where = $this->_getcondition($condition);



        try {
            $data = $this->where($where)
                    ->order('sort_order DESC')
                    ->field('cat_no as value,name as label,level_no,parent_cat_no')
                    ->select();
            $ret = $ret1 = $ret2 = $ret3 = [];
            foreach ($data as $item) {
                switch ($item['level_no']) {
                    case 1:
                        $ret1[$item['value']] = $item;
                        break;
                    case 2:
                        $ret2[$item['parent_cat_no']][$item['value']] = $item;
                        break;
                    case 3:

                        $ret3[$item['parent_cat_no']][$item['value']] = $item;
                        break;
                }
            }
            foreach ($ret1 as $cat_no1 => $item1) {
                $item1['label'] = $item1['label'] . '-' . $item1['value'];
                if (!empty($ret2[$cat_no1])) {
                    foreach ($ret2[$cat_no1] as $cat_no2 => $item2) {

                        $item2['label'] = $item2['label'] . '-' . $item2['value'];
                        if (!empty($ret3[$cat_no2]) && !$is_two) {
                            foreach ($ret3[$cat_no2] as $item3) {
                                unset($item3['parent_cat_no'], $item3['level_no']);
                                $item3['label'] = $item3['label'] . '-' . $item3['value'];
                                $item2['children'][] = $item3;
                            }
                        }
                        unset($item2['parent_cat_no'], $item2['level_no']);
                        $item1['children'][] = $item2;
                    }
                }
                $ret[] = $item1;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
