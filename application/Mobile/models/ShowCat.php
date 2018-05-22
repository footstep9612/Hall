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
//
//            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
//            Log::write($ex->getMessage());
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

        //$this->_updateSpuCount();
        try {
            $where['spu_count'] = ['gt', 0];
            $result = $this
                    ->where($where)
                    ->order('sort_order DESC')
                    ->field('cat_no as value,name as label,parent_cat_no,small_icon')
                    ->select();

            return $result;
        } catch (Exception $ex) {
//            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
//            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    private function _updateSpuCount() {
        $time = redisHashGet('show_cat', 'spu_count');

        $where = ['spu_count' => ['gt', 0], 'deleted_flag' => 'N', 'level_no' => 1];

        $count = $this->where($where)->count();

        $show_cat_product_model = new ShowCatProductModel();
        $show_cat_product_teble = $show_cat_product_model->getTableName();
        $show_cat_table = $this->getTableName();
        if (empty($time) || $time + 3600 < time() || $count == 0) {
            $this->startTrans();
            $sql = 'UPDATE ' . $show_cat_table . ' set spu_count=0';
            $sql1 = 'UPDATE ' . $show_cat_table . ' ,(SELECT count(id) as spu_count ,scp.cat_no,scp.lang from '
                    . $show_cat_product_teble . ' as scp GROUP BY scp.cat_no,scp.lang) temp '
                    . 'set show_cat.spu_count= temp.spu_count where show_cat.lang=temp.lang and show_cat.cat_no=temp.cat_no';
            $sql2 = 'UPDATE ' . $show_cat_table . ' ,(SELECT sum(sc.spu_count) as spu_count ,sc.parent_cat_no,sc.lang from '
                    . $show_cat_table . ' as sc where sc.level_no=3 GROUP BY sc.parent_cat_no,sc.lang) temp '
                    . 'set show_cat.spu_count= temp.spu_count where show_cat.lang=temp.lang '
                    . 'and show_cat.cat_no=temp.parent_cat_no  and show_cat.level_no=2';
            $sql3 = 'UPDATE ' . $show_cat_table . ' ,(SELECT sum(sc.spu_count) as spu_count ,sc.parent_cat_no,sc.lang from '
                    . $show_cat_table . ' as sc where sc.level_no=2 GROUP BY sc.parent_cat_no,sc.lang) temp '
                    . 'set show_cat.spu_count= temp.spu_count where show_cat.lang=temp.lang '
                    . 'and show_cat.cat_no=temp.parent_cat_no  and show_cat.level_no=1';
            $this->execute($sql);
            $this->execute($sql1);
            $this->execute($sql2);
            $this->execute($sql3);
            $this->commit();
            redisHashSet('show_cat', 'spu_count', time());
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
                ->field('cat_no,name ,parent_cat_no,level_no')
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
        $where['deleted_flag'] = 'N';
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
//            Log::write($ex->getMessage(), Log::ERR);
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

        $where['deleted_flag'] = 'N';

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
        $this->_updateSpuCount();
        if ($country_bn) {
            $condition['country_bn'] = $country_bn;
        }
        if ($cat_no) {
            $condition['parent_cat_no'] = $cat_no;
        } else {
            $condition['parent_cat_no'] = 0;
        }
        $condition['spu_count'] = ['gt', 0];
        $condition['deleted_flag'] = 'N';
        $condition['status'] = self::STATUS_VALID;
        $condition['lang'] = $lang;

        $data = $this->where($condition)
                ->field('id, cat_no, lang, name, status, sort_order')
                ->order('sort_order DESC')
                ->select();


        return $data;
    }

    public function getListByLetter($country_bn, $letter = '', $lang = 'en') {
        $this->_updateSpuCount();
        if ($country_bn) {
            $condition['country_bn'] = trim($country_bn);
        }
        if ($letter) {
            $condition['name'] = ['like', trim($letter) . '%'];
        }
        $condition['status'] = self::STATUS_VALID;
        $condition['deleted_flag'] = 'N';
        $condition['level_no'] = 3;
        $condition['spu_count'] = ['gt', 0];
        $condition['lang'] = trim($lang);
        $data = $this->where($condition)
                ->field(' cat_no,name')
                ->order('sort_order DESC,id asc')
                ->select();

        return $data;
    }

    public function getListByLetterExit($country_bn, $letter = '', $lang = 'en') {
        $this->_updateSpuCount();
        if ($country_bn) {
            $condition['country_bn'] = trim($country_bn);
        }
        if ($letter) {
            $condition['name'] = ['like', trim($letter) . '%'];
        }
        $condition['status'] = self::STATUS_VALID;
        $condition['deleted_flag'] = 'N';
        $condition['level_no'] = 3;
        $condition['spu_count'] = ['gt', 0];
        $condition['lang'] = trim($lang);
        $data = $this
                ->field('id')
                ->where($condition)
                ->find();

        return isset($data['id']) ? true : false;
    }

    /**
     * Description of 判断国家是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($country_bn, $lang = 'en') {
        $where = ['deleted_flag' => 'N'];
        $where['country_bn'] = $country_bn;
        if ($lang) {
            $where['lang'] = $lang;
        }

        return $this->where($where)->field('id,country_bn')->find();
    }

}
