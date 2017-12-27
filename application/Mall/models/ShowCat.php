<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 15:52
 */
class ShowCatModel extends PublicModel {

    //状态
    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $dbName = 'erui_goods';
    protected $tableName = 'show_cat';

    public function __construct() {

        parent::__construct();
    }

    /*
     * 获取物料分类
     *
     */

    public function getshowcatsByshowcatnos($show_cat_nos, $lang = 'en', $page_flag = true, $country_bn = 'China') {

        try {

            if ($show_cat_nos) {
                $show_cat_nos = array_values($show_cat_nos);

                $where = [
                    'cat_no' => ['in', $show_cat_nos],
                    'status' => 'VALID',
                    'lang' => $lang,
                    'country_bn' => $country_bn,
                    0 => '`name` is not null and `name`<>\'\''
                ];

                $this->where($where)
                        ->field('cat_no,name')
                        ->group('cat_no');
                if ($page_flag) {
                    $this->limit(0, 20);
                }

                $flag = $this->select();

                return $flag;
            } else {
                return [];
            }
        } catch (Exception $ex) {

            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
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

        $show_cat_product_model = new ShowCatProductModel();
        $show_cat_product_table = $show_cat_product_model->getTableName();
        $show_cat_table = $this->getTableName();
        $lang = $where['lang'];
        if ($where['level_no'] === 1) {
            $where[] = 'cat_no in (select parent_cat_no from ' . $show_cat_table . ' tsc where tsc.cat_no in (select sc.parent_cat_no FROM ' . $show_cat_table . ' sc  '
                    . ' LEFT JOIN ' . $show_cat_product_table . ' scp on scp.lang=sc.lang and scp.onshelf_flag=\'Y\' and sc.cat_no=scp.cat_no'
                    . ' where sc.lang=\'' . $lang . '\' and sc.deleted_flag=\'N\') and tsc.lang=\'' . $lang . '\' and tsc.deleted_flag=\'N\')';
        } elseif ($where['level_no'] === 2) {
            $where[] = 'cat_no in (select sc.parent_cat_no FROM ' . $show_cat_table . ' sc  '
                    . ' LEFT JOIN ' . $show_cat_product_table . ' scp on scp.lang=sc.lang and scp.onshelf_flag=\'Y\' and sc.cat_no=scp.cat_no'
                    . ' where sc.lang=\'' . $lang . '\' and sc.deleted_flag=\'N\')';
        } elseif ($where['level_no'] === 3) {
            $where[] = 'cat_no in (select sc.cat_no FROM ' . $show_cat_table . ' sc  '
                    . ' LEFT JOIN ' . $show_cat_product_table . ' scp on scp.lang=sc.lang and scp.onshelf_flag=\'Y\' and sc.cat_no=scp.cat_no'
                    . ' where sc.lang=\'' . $lang . '\' and sc.deleted_flag=\'N\')';
        }

        try {
            $result = $this
                    ->where($where)
                    ->order('sort_order DESC')
                    ->field('cat_no as value,name as label,parent_cat_no,small_icon')
                    ->select();

            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 分类详情
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function info($cat_no, $country_bn, $lang) {
        $where['deleted_flag'] = 'N';
        if (empty($cat_no)) {
            return [];
        } else {
            $where['cat_no'] = $cat_no;
        }
        if (empty($country_bn)) {
            return [];
        } else {
            $where['country_bn'] = $country_bn;
        }
        if (empty($lang)) {
            return [];
        } else {
            $where['lang'] = $lang;
        }
        $result = $this->where($where)
                ->order('sort_order DESC')
                ->field('cat_no,name ,parent_cat_no')
                ->find();
        return $result;
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
        getValue($where, $condition, 'id');
        getValue($where, $condition, 'cat_no');

        getValue($where, $condition, 'country_bn');
        if (isset($condition['cat_no3']) && $condition['cat_no3']) {
            $where['level_no'] = 3;
            $where['cat_no'] = $condition['cat_no3'];
        } elseif (isset($condition['cat_no2']) && $condition['cat_no2']) {
            $where['level_no'] = 2;
            $where['parent_cat_no'] = $condition['cat_no2'];
        } elseif (isset($condition['cat_no1']) && $condition['cat_no1']) {
            $where['level_no'] = 1;
            $where['parent_cat_no'] = $condition['cat_no1'];
        } elseif (isset($condition['level_no']) && intval($condition['level_no']) <= 3) {
            $where['level_no'] = intval($condition['level_no']);
        } else {
            $where['level_no'] = 1;
        }
        getValue($where, $condition, 'parent_cat_no');
        getValue($where, $condition, 'mobile', 'like');
        getValue($where, $condition, 'lang', 'string');

        getValue($where, $condition, 'name', 'like');
        getValue($where, $condition, 'sort_order', 'string');
        getValue($where, $condition, 'created_at', 'string');
        getValue($where, $condition, 'created_by');
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
    public function getcount($condition = []) {
        $where = $this->_getcondition($condition);


        try {
            $count = $this->where($where)
                    //  ->field('id,user_id,name,email,mobile,status')
                    ->count('id');

            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
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



        $this->where($where);
        if (isset($condition['page']) && isset($condition['countPerPage'])) {
            return $this->limit($condition['page'] . ',' . $condition['countPerPage']);
        }

        $data = $this->field('id,cat_no,parent_cat_no,level_no,lang,name,'
                        . 'status,sort_order,created_at,created_by')
                ->order('sort_order DESC')
                ->select();

        return $data;
    }

    public function get_list($country_bn, $cat_no = '', $lang = 'en') {

        if ($country_bn) {
            $condition['country_bn'] = $country_bn;
        }
        if ($cat_no) {
            $condition['parent_cat_no'] = $cat_no;
        } else {
            $condition['parent_cat_no'] = 0;
        }
        $condition['status'] = self::STATUS_VALID;
        $condition['lang'] = $lang;

        $data = $this->where($condition)
                ->field('id, cat_no, lang, name, status, sort_order')
                ->order('sort_order DESC')
                ->select();


        return $data;
    }

    public function getListByLetter($country_bn, $letter = '', $lang = 'en') {

        if ($country_bn) {
            $condition['country_bn'] = trim($country_bn);
        }
        if ($letter) {
            $condition['name'] = ['like', trim($letter) . '%'];
        }
        $condition['status'] = self::STATUS_VALID;
        $condition['deleted_flag'] = 'N';
        $condition['level_no'] = 3;
        $condition['lang'] = trim($lang);
        $data = $this->where($condition)
                ->field(' cat_no,name')
                ->order('sort_order DESC,id asc')
                ->select();

        return $data;
    }

}
