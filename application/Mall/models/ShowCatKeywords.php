<?php

/**
 * 展示分类
 * User: jhw
 * Date: 2018/5/08
 * Time: 15:52
 */
class ShowCatKeywordsModel extends PublicModel {

    protected $dbName = 'erui_goods';
    protected $tableName = 'show_cat_keywords';

    public function __construct() {

        parent::__construct();
    }


    protected function _getcondition($condition = []) {
        $where = [];
        getValue($where, $condition, 'cat_no');
        getValue($where, $condition, 'country_bn');
        getValue($where, $condition, 'name');
        getValue($where, $condition, 'lang');
        $where['deleted_flag'] = 'N';
        return $where;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author jhw
     */
    public function getlist($condition = [], $lang = 'en') {
        $where = $this->_getcondition($condition);
        $where['lang'] = $lang;
        $where['deleted_flag'] = 'N';
        $this->where($where);
        if (isset($condition['page']) && isset($condition['countPerPage'])) {
            return $this->limit($condition['page'] . ',' . $condition['countPerPage']);
        }
        $data = $this->field('id,cat_no,content,lang,name')
                ->order('id DESC')
                ->select();

        return $data;
    }
    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author jhw
     */
        public function getKeywordinfo($condition = []) {
            $where = $this->_getcondition($condition);
            $this->where($where);
            $data = $this->field('id,cat_no,cat_name,content,lang,name')
                ->order('id DESC')
                ->find();
            return $data;
        }
}
