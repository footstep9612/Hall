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
class MaterialcatModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'material_cat'; //数据表表名

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
    protected function getcondition($condition = []) {
        $where = [];
        getValue($where, $condition, 'id', 'string');
        getValue($where, $condition, 'cat_no', 'string');
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
        getValue($where, $condition, 'parent_cat_no', 'string');
        getValue($where, $condition, 'mobile', 'like');
        getValue($where, $condition, 'lang', 'string');
        getValue($where, $condition, 'name', 'like');
        getValue($where, $condition, 'sort_order', 'string');
        getValue($where, $condition, 'created_at', 'string');
        getValue($where, $condition, 'created_by', 'string');
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
        $where = $this->getcondition($condition);
        try {
            return $this->where($where)
                            //  ->field('id,user_id,name,email,mobile,status')
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
        $where = $this->getcondition($condition);
        try {
            return $this->where($where)
                            ->order('sort_order DESC')
                            ->field('cat_no as value,name as label,parent_cat_no')
                            ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 分页处理
     * @param array $condition 条件
     * @return null
     * @author zyg
     *
     */
    private function _getPage($condition) {
        $pagesize = 10;
        $start_no = 0;
        if (isset($condition['pagesize'])) {
            $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        if (isset($condition['current_no'])) {
            $start_no = intval($condition['current_no']) > 0 ? (intval($condition['current_no']) * $pagesize - $pagesize) : 0;
        }
        return [$start_no, $pagesize];
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {
        try {
            $where = $this->getcondition($condition);
            list($start_no, $pagesize) = $this->_getPage($condition);
            return $this->where($where)
                            ->limit($start_no, $pagesize)
                            ->order('sort_order DESC')
                            ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by')
                            ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    public function get_list($cat_no = '', $lang = 'en') {
        if ($cat_no) {
            $condition['parent_cat_no'] = $cat_no;
        } else {
            $condition['parent_cat_no'] = 0;
        }
        $condition['status'] = self::STATUS_VALID;
        $condition['lang'] = $lang;

        try {
            return $this->where($condition)
                            ->field('id,cat_no,lang,name,status,sort_order')
                            ->order('sort_order DESC')
                            ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($cat_no = '', $lang = 'en') {
        if ($cat_no) {
            $where['cat_no'] = $cat_no;
        } else {
            return [];
        }
        if ($lang) {
            $where['lang'] = $lang;
        }
        try {
            return $this->where($where)
                            ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by')
                            ->find();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 和上级分类信息 顶级分类信息
     * @param mix $cat_nos // 物料分类编码数组3f
     * @param string $lang // 语言 zh en ru es
     * @return mix  物料分类及上级和顶级信息
     */

    public function getinfo($cat_no, $lang = 'en') {
        try {
            if ($cat_no) {
                $cat3 = $this->field('id,cat_no,name,parent_cat_no')
                        ->where(['cat_no' => $cat_no, 'lang' => $lang, 'status' => 'VALID'])
                        ->find();
                if ($cat3) {
                    $cat2 = $this->field('id,cat_no,name,parent_cat_no')
                            ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                            ->find();
                } else {
                    return [];
                }
                if ($cat2) {
                    $cat1 = $this->field('id,cat_no,name,parent_cat_no')
                            ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                            ->find();
                } else {
                    return ['cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
                }
                if ($cat1) {
                    return ['cat_no1' => $cat1['cat_no'], 'cat_name1' => $cat1['name'], 'cat_no2' => $cat2['cat_no'], 'cat_name2' => $cat2['name'], 'cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
                } else {
                    return ['cat_no2' => $cat2['cat_no'], 'cat_name2' => $cat2['name'], 'cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
                }
            } else {
                return [];
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 判断是否存在
     * @param  mix $where 搜索条件
     * @return mix
     * @author zyg
     */
    public function Exist($where) {
        try {
            $row = $this->where($where)
                    ->field('id')
                    ->find();
            return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 删除数据
     * @param  string $cat_no
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($cat_no = '', $lang = '') {
        if (!$cat_no) {

            return false;
        }
        $where['cat_no'] = ['like', $cat_no . '%'];
        if ($lang) {
            $where['lang'] = $lang;
        }
        try {

            $flag = $this->where($where)
                    ->save(['status' => self::STATUS_DELETED]);

            return $flag;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 交换分类排序
     * @param string $cat_no 交换的分类编码
     * @return string $chang_cat_no 被交换的分类编码
     * @author zyg
     */
    public function changecat_sort_order($cat_no, $chang_cat_no) {

        try {
            $this->startTrans();
            $sort_order = $this->field('sort_order')->where(['cat_no' => $cat_no])->find();
            $sort_order1 = $this->field('sort_order')->where(['cat_no' => $chang_cat_no])->find();
            $flag = $this->where(['cat_no' => $cat_no])->save(['sort_order' => $sort_order1['sort_order']]);
            if ($flag) {
                $flag1 = $this->where(['cat_no' => $chang_cat_no])->save(['sort_order'
                    => $sort_order['sort_order']]);
                if ($flag1) {
                    $this->commit();
                    return true;
                } else {
                    $this->rollback();
                    return false;
                }
            } else {
                $this->rollback();
                return false;
            }
            return $flag;
        } catch (Exception $ex) {
            $this->rollback();
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 通过审核
     * @param  string $cat_no
     * @return bool
     * @author zyg
     */
    public function approving($cat_no = '', $lang = '') {

        $where['cat_no'] = $cat_no;
        if ($lang) {
            $where['lang'] = $lang;
        }
        try {
            $flag = $this->where($where)
                    ->save(['status' => self::STATUS_VALID]);
            if ($flag && $cat_no && !$lang) {
                $es_product_model = new EsproductModel();
                $es_product_model->Updatemeterialcatno($cat_no, null, 'en');
                $es_product_model->Updatemeterialcatno($cat_no, null, 'zh');
                $es_product_model->Updatemeterialcatno($cat_no, null, 'es');
                $es_product_model->Updatemeterialcatno($cat_no, null, 'ru');
            } elseif ($flag && $cat_no && $lang) {
                $es_product_model->Updatemeterialcatno($cat_no, null, $lang);
            }
            return $flag;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    public function update_data($upcondition = [], $username = '') {
        $data = $this->getUpdateCondition($upcondition, $username);
        $data['created_by'] = $username;
        try {
            $info = $this->info($upcondition['cat_no'], null);
            if (!$data) {
                return false;
            }
            if (isset($upcondition['cat_no']) && $upcondition['cat_no']) {
                $data['cat_no'] = $where['cat_no'] = $upcondition['cat_no'];
            } else {
                return false;
            }
            if (isset($data['parent_cat_no']) && $data['parent_cat_no'] != $info['parent_cat_no']) {
                $cat_no = $this->getCatNo($data['parent_cat_no'], $data['level_no']);
                if (!$cat_no) {
                    return false;
                } else {
                    $data['cat_no'] = $cat_no;
                }
            }
            $this->startTrans();
            $langs = ['en', 'es', 'zh', 'ru'];
            foreach ($langs as $lang) {
                if (isset($upcondition[$lang])) {
                    $data['lang'] = $lang;
                    $data['name'] = $upcondition[$lang]['name'];
                    $where['lang'] = $lang;
                    $exist_flag = $this->Exist($where);
                    $add = $data;
                    $add['cat_no'] = $data['cat_no'];
                    $add['status'] = self::STATUS_APPROVING;
                    $add['id'] = $this->getMaxid() + 1;
                    $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($add);

                    if (!$flag) {
                        $this->rollback();
                        return false;
                    }
                }
            }

            if (isset($upcondition['level_no']) && $upcondition['level_no'] == 2 && $where['cat_no'] != $data['cat_no']) {

                $childs = $this->get_list($cat_no);
                foreach ($childs as $val) {
                    $child_cat_no = $this->getCatNo($data['cat_no'], 3);
                    $flag = $this->where(['cat_no' => $val['cat_no']])
                            ->save(['cat_no' => $child_cat_no, 'parent_cat_no' => $data['cat_no']]);
                    if (!$flag) {
                        $this->rollback();
                        return false;
                    }
                    $flag = $this->updateothercat($val['cat_no'], $child_cat_no);
                    if (!$flag) {
                        $this->rollback();
                        return false;
                    }
                }
            } elseif (isset($upcondition['level_no']) && $upcondition['level_no'] == 3 && $where['cat_no'] != $data['cat_no']) {
                $flag = $this->updateothercat($where['cat_no'], $data['cat_no']);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
            }

            $this->commit();
            return $flag;
        } catch (Exception $ex) {
            $this->rollback();
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    public function getUpdateCondition($upcondition = [], $username = '') {
        $data = [];
        $where = [];
        $info = [];
        if (isset($upcondition['cat_no']) && $upcondition['cat_no']) {
            $data['cat_no'] = $where['cat_no'] = $upcondition['cat_no'];
            $info = $this->info($where['cat_no'], 'zh');
        } else {

            return false;
        }
        if (isset($upcondition['parent_cat_no'])) {
            $data['parent_cat_no'] = $upcondition['parent_cat_no'];
        }
        if (isset($upcondition['level_no']) && $info['level_no'] != $upcondition['level_no']) {

            return false;
        }
        if (isset($upcondition['level_no']) && in_array($upcondition['level_no'], [1, 2, 3])) {
            $data['level_no'] = $upcondition['level_no'];
        }
        if (isset($upcondition['level_no']) && $upcondition['level_no'] == 1) {
            $data['parent_cat_no'] = 0;
        } elseif (isset($upcondition['parent_cat_no']) && $upcondition['parent_cat_no']) {
            $data['parent_cat_no'] = $upcondition['parent_cat_no'];
        }

        if (isset($upcondition['status'])) {
            switch ($upcondition['status']) {

                case self::STATUS_DELETED:
                    $data['status'] = $upcondition['status'];
                    break;
                case self::STATUS_DRAFT:
                    $data['status'] = $upcondition['status'];
                    break;
                case self::STATUS_APPROVING:
                    $data['status'] = $upcondition['status'];
                    break;
                case self::STATUS_VALID:
                    $data['status'] = $upcondition['status'];
                    break;
                default:
                    $data['status'] = self::STATUS_APPROVING;
                    break;
            }
        }
        if ($upcondition['sort_order']) {
            $data['sort_order'] = $upcondition['sort_order'];
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $username;
        return $data;
    }

    public function updateothercat($old_cat_no, $new_cat_no) {
        try {

            $model = new Model($this->dbName . '.product', 't_');
            $flag = $model
                    ->where(['meterial_cat_no' => $old_cat_no])
                    ->save(['meterial_cat_no' => $new_cat_no]);


            if ($flag === false) {
                $this->rollback();
                return false;
            }
            $material_cat_product_model = new Model($this->dbName . '.material_cat_product', 't_');
            $flag = $material_cat_product_model
                    ->where(['cat_no' => $old_cat_no])
                    ->save(['cat_no' => $new_cat_no]);


            if ($flag === false) {
                $this->rollback();

                return false;
            }
            $show_material_cat_model = new Model($this->dbName . '.show_material_cat', 't_');
            $flag = $show_material_cat_model
                    ->where(['material_cat_no' => $old_cat_no])
                    ->save(['material_cat_no' => $new_cat_no]);


            if ($flag === false) {
                $this->rollback();
                return false;
            }

            $es_product_model = new EsproductModel();
            $es_product_model->Updatemeterialcatno($old_cat_no, null, 'en', $new_cat_no);
            $es_product_model->Updatemeterialcatno($old_cat_no, null, 'zh', $new_cat_no);
            $es_product_model->Updatemeterialcatno($old_cat_no, null, 'es', $new_cat_no);
            $es_product_model->Updatemeterialcatno($old_cat_no, null, 'ru', $new_cat_no);
            return true;
        } catch (Exception $ex) {
            $this->rollback();
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    public function getCatNo($parent_cat_no = '', $level_no = 1) {

        if ($level_no < 1) {
            $level_no = 1;
        } elseif ($level_no >= 3) {
            $level_no = 3;
        }
        if (empty($parent_cat_no) && $level_no == 1) {
            $re = $this->field('max(cat_no) as max_cat_no')->where(['level_no' => 1])->find();
            if (!empty($re['max_cat_no'])) {
                return sprintf('%02d', intval($re['max_cat_no']) + 1);
            } else {
                return '01';
            }
        } elseif (empty($parent_cat_no)) {
            return false;
        } else {
            $re = $this->field('max(cat_no) as max_cat_no')->where(['parent_cat_no' => $parent_cat_no])->find();
            $format = '%0' . ($level_no * 2) . 'd';

            if (!empty($re['max_cat_no'])) {
                return sprintf($format, (intval($re['max_cat_no']) + 1));
            } else {

                return sprintf($format, (intval($parent_cat_no) * 100 + 1));
            }
        }
    }

    public function getcatnosbyparentcatno($catno, $lang = 'en') {
        if (is_array($catno)) {
            $where['parent_cat_no'] = ['in', $catno];
        } else {
            $where['parent_cat_no'] = $catno;
        }
        $where['lang'] = $lang;
        $where['status'] = self::STATUS_VALID;
        $rows = $this->field('cat_no')->select();
        return $rows;
    }

    public function getMaxid() {
        $row = $this->field('max(id) as maxid')->find();
        return intval($row['maxid']);
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = [], $username = '') {

        $condition = $this->create($createcondition);
        if (isset($condition['parent_cat_no']) && $condition['parent_cat_no']) {
            $info = $this->info($condition['parent_cat_no'], null);
            $condition['level_no'] = $info['level_no'] + 1;
        } else {
            $data['parent_cat_no'] = 0;
            $condition['level_no'] = 1;
        }
        if (isset($condition['cat_no'])) {
            $data['cat_no'] = $condition['cat_no'];
        }
        if (isset($condition['parent_cat_no']) && $condition['level_no'] == 1) {
            $data['parent_cat_no'] = 0;
        } elseif (isset($condition['parent_cat_no']) && $condition['parent_cat_no']) {
            $data['parent_cat_no'] = $condition['parent_cat_no'];
        }
        if (isset($condition['level_no']) && in_array($condition['level_no'], [1, 2, 3])) {
            $data['level_no'] = $condition['level_no'];
        } else {
            $data['level_no'] = 1;
        }
        if (!isset($data['cat_no'])) {
            $cat_no = $this->getCatNo($data['parent_cat_no'], $data['level_no']);
            if (!$cat_no) {
                return false;
            } else {
                $data['cat_no'] = $cat_no;
            }
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $username;
        if (!isset($condition['status'])) {
            $condition['status'] = self::STATUS_APPROVING;
        }
        switch ($condition['status']) {
            case self::STATUS_DELETED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_DRAFT:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_APPROVING:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_VALID:
                $data['status'] = $condition['status'];
                break;
            default :
                $data['status'] = self::STATUS_APPROVING;
        }
        if ($condition['sort_order']) {
            $data['sort_order'] = $condition['sort_order'];
        }
        $this->startTrans();
        $maxid = $this->getMaxid();

        $langs = ['en', 'es', 'zh', 'ru'];
        foreach ($langs as $lang) {
            if (isset($createcondition[$lang])) {
                $data['lang'] = 'en';
                $maxid++;
                $data['id'] = $maxid;
                $data['name'] = $createcondition['en']['name'];
                $flag = $this->add($data);

                if (!$flag) {

                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return $flag;
    }

    /**
     * 根据cat_no获取所属分类name
     * @param  string $code 编码
     * klp
     */
    protected $data = array();

    public function getNameByCat($code = '') {
        if ($code == '')
            return '';
        $condition = array(
            'cat_no' => $code,
            'status' => self::STATUS_VALID
        );
        $resultTr = $this->field('name,parent_cat_no')->where($condition)->select();

        $this->data[] = $resultTr[0]['name'];
        if ($resultTr) {
            self::getNameByCat($resultTr[0]['parent_cat_no']);
        }
        $nameAll = $this->data[2] . '/' . $this->data[1] . '/' . $this->data[0];
        return $nameAll;
    }

    /**
     * 根据编码获取分类信息
     * @author link 2016-06-15
     * @param string $catNo 分类编码
     * @param string $lang 语言
     * @return array
     */
    public function getMeterialCatByNo($catNo = '', $lang = '') {
        if ($catNo == '' || $lang == '')
            return array();

        //读取缓存
        if (redisHashExist('MeterialCat', $catNo . '_' . $lang)) {
            return (array) json_decode(redisHashGet('MeterialCat', $catNo . '_' . $lang));
        }

        try {
            $field = 'lang,cat_no,parent_cat_no,level_no,name,description,sort_order';
            $condition = array(
                'cat_no' => $catNo,
                'status' => self::STATUS_VALID,
                'lang' => $lang
            );
            $result = $this->field($field)->where($condition)
                            ->order('sort_order DESC')->find();
            if ($result) {
                redisHashSet('MeterialCat', $catNo . '_' . $lang, json_encode($result));
                return $result;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
        return array();
    }

    /**
     * 根据分类名称获取分类编码
     * 模糊查询
     * @author link 2017-06-26
     * @param string $cat_name 分类名称
     * @return array
     */
    public function getCatNoByName($cat_name = '') {
        if (empty($cat_name))
            return array();

        if (redisHashExist('Material', md5($cat_name))) {
            return (array) json_decode(redisHashGet('Material', md5($cat_name)));
        }
        try {
            $result = $this->field('cat_no')->where(array('name' => array('like', $cat_name)))->order('sort_order DESC')->select();
            if ($result)
                redisHashSet('Material', md5($cat_name), json_encode($result));

            return $result ? $result : array();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}