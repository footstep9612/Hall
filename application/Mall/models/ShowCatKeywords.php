<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 15:52
 */
class ShowCatKeywordsModel extends PublicModel {

    //状态
    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $dbName = 'erui_goods';
    protected $tableName = 'show_cat_keywords';

    public function __construct() {

        parent::__construct();
    }


    protected function _getcondition($condition = []) {
        $where = [];
        getValue($where, $condition, 'cat_no');
        $where['deleted_flag'] = 'N';
        return $where;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
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

}
