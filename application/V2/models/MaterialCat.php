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
class MaterialCatModel extends PublicModel {

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
    public function tree($condition = [], $is_two = false) {
        $where = $this->_getcondition($condition);
        if ($is_two) {
            $where['level_no'] = ['in', [1, 2]];
        }


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
                        $item['label'] = $item['label'] . '-' . $item['value'];
                        $ret3[$item['parent_cat_no']][$item['value']] = $item;
                        break;
                }
            }
            foreach ($ret1 as $cat_no1 => $item1) {
                if (!$is_two) {
                    unset($item1['parent_cat_no'], $item1['level_no']);
                }

                if (!empty($ret2[$cat_no1])) {
                    foreach ($ret2[$cat_no1] as $cat_no2 => $item2) {

                        if (!empty($ret3[$cat_no2]) && !$is_two) {
                            foreach ($ret3[$cat_no2] as $item3) {
                                unset($item3['parent_cat_no'], $item3['level_no']);
                                $item2['children'][] = $item3;
                            }
                        }

                        if (!$is_two) {
                            unset($item2['parent_cat_no'], $item2['level_no']);
                        }
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

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {
        try {
            $where = $this->_getcondition($condition);
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

    /**
     * 分类联动
     * @param string $cat_no 分类编码
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function get_list($cat_no = '', $lang = 'en', $name = null) {
        if ($cat_no) {
            $condition['parent_cat_no'] = $cat_no;
        } else {
            $condition['parent_cat_no'] = '';
        }
        $condition['status'] = self::STATUS_VALID;
        $condition['lang'] = $lang;
        if ($name) {
            $condition['name'] = ['like', '%' . trim($name) . '%'];
        }
        try {
            return $this->where($condition)
                            ->field('id,cat_no,lang,name')
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
     * @param  string $cat_no 分类编码
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
                            ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by,updated_at,updated_by')
                            ->find();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * 判断物料分类名称是否重复
     */

    public function MaterialcatExist($name, $lang, $level_no = null, $cat_no = null) {
        try {
            $where = [];

            if ($cat_no) {
                $where['cat_no'] = ['neq', $cat_no];
            }
            $where['level_no'] = ['eq', $level_no];
            $where['deleted_flag'] = 'N';
            $where['lang'] = $lang;
            $where['name'] = $name;
            $flag = $this->field('id')->where($where)
                    ->find();
            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
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
            return false;
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
                    ->save(['status' => self::STATUS_DELETED, 'deleted_flag' => 'Y',]);

            $es_product_model = new EsProductModel();

            if ($lang) {
                $es_product_model->Updatemeterialcatno($cat_no, null, $lang);
            } else {
                foreach ($this->langs as $lan) {
                    $es_product_model->Updatemeterialcatno($cat_no, null, $lan);
                }
            }
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
                $flag1 = $this->where(['cat_no' => $chang_cat_no])
                        ->save(['sort_order' => $sort_order['sort_order'],
                    'updated_by' => defined('UID') ? UID : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
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
                    ->save([
                'status' => self::STATUS_VALID,
                'checked_by' => defined('UID') ? UID : 0,
                'checked_at' => date('Y-m-d H:i:s'),
                'deleted_flag' => 'N'
            ]);

            if ($flag !== false && $cat_no && !$lang) {
                $es_product_model = new EsProductModel();
                $es_product_model->Updatemeterialcatno($cat_no, null, 'en');
                $es_product_model->Updatemeterialcatno($cat_no, null, 'zh');
                $es_product_model->Updatemeterialcatno($cat_no, null, 'es');
                $es_product_model->Updatemeterialcatno($cat_no, null, 'ru');
            } elseif ($flag !== false && $cat_no && $lang) {
                $es_product_model->Updatemeterialcatno($cat_no, null, $lang);
            }

            return $flag !== false;
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
    public function update_data($upcondition = []) {



        $data = $this->getUpdateCondition($upcondition, defined('UID') ? UID : 0);
        //   $data['created_by'] = defined('UID') ? UID : 0;
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
            $data['updated_by'] = defined('UID') ? UID : 0;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $old_info = [];
            foreach ($this->langs as $lang) {
                if (isset($upcondition[$lang]) && $upcondition[$lang]['name']) {
                    $old_info[$lang] = $this->where(['cat_no' => $where['cat_no'], 'lang' => $lang])->field('id,cat_no,name')->find();
                    $data['lang'] = $lang;
                    $data['name'] = $upcondition[$lang]['name'];

                    $where['lang'] = $lang;

                    $exist_flag = $this->Exist($where);
                    $add = $data;
                    $add['cat_no'] = $data['cat_no'];
                    $add['status'] = self::STATUS_VALID;
                    $add['created_by'] = defined('UID') ? UID : 0;
                    $add['created_at'] = date('Y-m-d H:i:s');
                    $data = $this->create($data);
                    $add = $this->create($add);
                    $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($add);
                    if (!$flag) {
                        $this->rollback();
                        return false;
                    }
                }
            }

            $flag = $this->_updateOther($upcondition, $where, $data, $cat_no, $old_info);
            if (!$flag) {
                $this->rollback();
                return false;
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
     * 更新子分类数据和 产品商品物料分类信息
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    private function _updateOther(&$upcondition, &$where, &$data, &$cat_no, &$old_info) {

        if (isset($upcondition['level_no']) && $upcondition['level_no'] == 2 && $where['cat_no'] != $data['cat_no']) {

            $childs = $this->get_list($cat_no);
            foreach ($childs as $val) {
                $child_cat_no = $this->getCatNo($data['cat_no'], 3);
                $flag = $this->where(['cat_no' => $val['cat_no']])
                        ->save(['cat_no' => $child_cat_no, 'parent_cat_no' => $data['cat_no']]);
                if (!$flag) {

                    return false;
                }
                $flag = $this->updateothercat($val['cat_no'], $child_cat_no);
                if (!$flag) {


                    return false;
                }
            }
        } elseif (isset($upcondition['level_no']) && $upcondition['level_no'] == 3 && $where['cat_no'] != $data['cat_no']) {
            $flag = $this->updateothercat($where['cat_no'], $data['cat_no']);
            if (!$flag) {

                return false;
            }
        } else {
            $es_product_model = new EsProductModel();
            foreach ($this->langs as $lang) {
                $info = $old_info[$lang];
                if (isset($upcondition[$lang]['name']) && isset($info['name']) && $info['name'] != $upcondition[$lang]['name']) {
                    $es_product_model->Updatemeterialcatno($where['cat_no'], null, $lang, $where['cat_no']);
                } elseif ($old_info['zh']['name'] != $upcondition['zh']['name']) {
                    $es_product_model->Updatemeterialcatno($where['cat_no'], null, $lang, $where['cat_no']);
                }
            }
        }
        return true;
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    public function getUpdateCondition(&$upcondition = []) {
        $data = [];
        $where = [];
        $info = [];
        if (isset($upcondition['cat_no']) && $upcondition['cat_no']) {
            $data['cat_no'] = $where['cat_no'] = $upcondition['cat_no'];
            $info = $this->info($where['cat_no'], 'zh');
            $upcondition['level_no'] = $info['level_no'];
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
            $data['parent_cat_no'] = '';
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

        return $data;
    }

    public function updateothercat($old_cat_no, $new_cat_no) {
        try {

            $model = new Model($this->dbName . '.product');
            $flag = $model
                    ->where(['meterial_cat_no' => $old_cat_no])
                    ->save(['meterial_cat_no' => $new_cat_no]);


            if ($flag === false) {
                $this->rollback();
                return false;
            }

            $show_material_cat_model = new Model($this->dbName . '.show_material_cat');
            $flag = $show_material_cat_model
                    ->where(['material_cat_no' => $old_cat_no])
                    ->save(['material_cat_no' => $new_cat_no]);


            if ($flag === false) {
                $this->rollback();
                return false;
            }

            $es_product_model = new EsProductModel();
            foreach ($this->langs as $lang) {
                $es_product_model->Updatemeterialcatno($old_cat_no, null, $lang, $new_cat_no);
            }
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

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {
        $condition = $this->create($createcondition);
        if (isset($condition['parent_cat_no']) && $condition['parent_cat_no']) {
            $info = $this->info($condition['parent_cat_no'], null);
            $condition['level_no'] = $info['level_no'] + 1;
        } else {
            $data['parent_cat_no'] = '';
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

        $cat_no = $this->getCatNo($data['parent_cat_no'], $data['level_no']);
        if (!$cat_no) {
            return false;
        } else {
            $data['cat_no'] = $cat_no;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;
        if (!isset($condition['status'])) {
            $condition['status'] = self::STATUS_VALID;
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
                $data['status'] = self::STATUS_VALID;
        }
        if ($condition['sort_order']) {
            $data['sort_order'] = $condition['sort_order'];
        } else {
            $data['sort_order'] = 0;
        }
        $this->startTrans();
        $this->data = null;
        foreach ($this->langs as $lang) {

            if (isset($createcondition[$lang]) && $createcondition[$lang]['name']) {
                $data['lang'] = $lang;
                $data['name'] = $createcondition[$lang]['name'];
                $data = $this->create($data);
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

    public function getNameByCat($cat_no = '') {
        if ($cat_no == '')
            return '';
        $condition = array(
            'cat_no' => $cat_no,
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

    /**
     * 根据分类名称获取分类编码
     * 模糊查询
     * @author link 2017-06-26
     * @param string $cat_name 分类名称
     * @return array
     */
    public function getCatNoByRealName($cat_name = '') {
        if (empty($cat_name)) {
            return '';
        }
        if (redisGet('Material_real', md5($cat_name))) {
            return redisGet('Material_real', md5($cat_name));
        }
        try {
            $cat_no = $this->where(['name' => $cat_name,
                                'deletd_flag' => 'N', 'level_no' => 3])
                            ->order('sort_order DESC')->getfield('cat_no');
            if ($cat_no) {
                redisSet('Material_real', md5($cat_name), $cat_no, 180);
            }

            return $cat_no ? $cat_no : '';
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return '';
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 和上级分类信息 顶级分类信息
     * @param mix $cat_no // 物料分类编码数组3f
     * @param string $lang // 语言 zh en ru es
     * @return mix  物料分类及上级和顶级信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getmaterial_cat($cat_no, $lang = 'en') {
        try {
            $cat3 = $this->field('id,cat_no,name')
                    ->where(['cat_no' => $cat_no, 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            $cat2 = $this->field('id,cat_no,name')
                    ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            $cat1 = $this->field('id,cat_no,name')
                    ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                    ->find();
            return [$cat1['cat_no'], $cat1['name'], $cat2['cat_no'], $cat2['name'], $cat3['cat_no'], $cat3['name']];
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 及上级分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  物料分类及上级和顶级信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getmaterial_cats($cat_nos, $lang = 'en') {
        if (!$cat_nos) {
            return[];
        }
        try {
            $cat3s = $this->field('id,cat_no,name,parent_cat_no')
                            ->where(['cat_no' => ['in', $cat_nos], 'lang' => $lang, 'status' => 'VALID'])->select();

            if (!$cat3s) {
                return [];
            }
            $cat1_nos = $cat2_nos = [];
            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }
            $cat2s = $this->field('id,cat_no,name,parent_cat_no')
                    ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            if (!$cat2s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no1' => '',
                        'cat_name1' => '',
                        'cat_no2' => '',
                        'cat_name2' => '',
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name']
                    ];
                }
                return $newcat3s;
            }
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }

            $cat1s = $this->field('id,cat_no,name')
                    ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $newcat1s = [];
            if (!$cat1s) {
                $newcat3s = [];
                $newcat2s = [];
                foreach ($cat2s as $val) {
                    $newcat2s[$val['cat_no']] = $val;
                }
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no1' => '',
                        'cat_name1' => '',
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => $val['name'],
                        'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                        'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    ];
                }
                return $newcat3s;
            }
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = ['cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
                    'cat_name1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['name'],
                    'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                    'cat_name2' => $newcat2s[$val['parent_cat_no']]['name'],
                    'cat_no3' => $val['cat_no'],
                    'cat_name3' => $val['name']];
            }

            return $newcat3s;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 及上级分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  物料分类及上级和顶级信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getNameByCatNos($cat_nos, $lang = 'en') {
        if (!$cat_nos) {
            return[];
        }
        try {
            $cats = $this->field('cat_no,name')
                            ->where(['cat_no' => ['in', $cat_nos], 'lang' => $lang, 'status' => 'VALID', 'deleted_flag' => 'N'])->select();
            $re = [];
            foreach ($cats as $cat) {
                $re[$cat['cat_no']] = $cat['name'];
            }

            return $re;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据物料分类编码搜索物料分类 及上级分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  物料分类及上级和顶级信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function setNamesByList(&$list, $lang = 'en') {
        if (empty($list)) {
            return[];
        }
        $cat_nos = [];
        foreach ($list as $item) {
            if (!empty($item['material_cat_no'])) {
                $cat_nos[] = trim($item['material_cat_no']);
            }
        }
        if (empty($cat_nos)) {
            return[];
        }
        try {
            $cats = $this->field('cat_no,name')
                            ->where(['cat_no' => ['in', $cat_nos],
                                'lang' => $lang,
                                'status' => 'VALID',
                                'deleted_flag' => 'N'])->select();
            $re = [];
            foreach ($cats as $cat) {

                $re[$cat['cat_no']] = $cat['name'];
            }
            foreach ($list as $key => $item) {
                if (!empty($item['material_cat_no']) && !empty($re[$item['material_cat_no']])) {
                    $item['category'] = $re[$item['material_cat_no']];
                }
                $list[$key] = $item;
            }
            return $re;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 导出品牌
     * @param  mix $input 导出条件
     * @return bool
     * @author zyg
     */
    public function export($input, $userInfo) {
        set_time_limit(0);  # 设置执行时间最大值
        ini_set("memory_limit", "1024M"); // 设置php可使用内存

        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($userInfo['name']);
        $objPHPExcel->getProperties()->setTitle("material_cat List");
        $objPHPExcel->getProperties()->setLastModifiedBy($userInfo['name']);
        $objPHPExcel->createSheet();    //创建工作表
        $objPHPExcel->setActiveSheetIndex(0);    //设置工作表
        $objSheet = $objPHPExcel->getActiveSheet();    //当前sheet
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        $objSheet->getStyle("A1:H1")
                ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle("A1:G1")->getFont()->setSize(12)->setBold(true);    //粗体
        $column_width_25 = ['A', 'B', 'C', 'E', 'F', 'G', 'H'];
        foreach ($column_width_25 as $column) {
            $objSheet->getColumnDimension($column)->setWidth(25);
        }
        $objPHPExcel->getActiveSheet(0)->getStyle('A')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet(0)->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objPHPExcel->getActiveSheet(0)->getStyle('C')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by
        $objSheet->setTitle('物料分类');
        $objSheet->setCellValue("A1", "序号");
        $objSheet->setCellValue("B1", "分类编码");
        $objSheet->setCellValue("C1", "父类编码");
        $objSheet->setCellValue("D1", "分类等级");
        $objSheet->setCellValue("E1", "分类名称(中)");
        $objSheet->setCellValue("F1", "分类名称(英)");
        $objSheet->setCellValue("G1", "分类名称(西)");
        $objSheet->setCellValue("H1", "分类名称(俄)");
        $j = 2;    //excel控制输出
        $result = $this->listall($input);

        if ($result) {
            foreach ($result as $cat_no => $item) {

                $objSheet->setCellValue("A" . $j, $j - 1, PHPExcel_Cell_DataType::TYPE_STRING);
                $objSheet->setCellValue("B" . $j, $cat_no, PHPExcel_Cell_DataType::TYPE_STRING);
                $objSheet->setCellValue("C" . $j, $item['parent_cat_no'] ? $item['parent_cat_no'] : '', PHPExcel_Cell_DataType::TYPE_STRING);
                $objSheet->setCellValue("D" . $j, $item['level_no']);
                $objSheet->setCellValue("E" . $j, isset($item['zh']['name']) ? $item['zh']['name'] : '');

                $objSheet->setCellValue("F" . $j, isset($item['en']['name']) ? $item['en']['name'] : '');

                $objSheet->setCellValue("G" . $j, isset($item['es']['name']) ? $item['es']['name'] : '');

                $objSheet->setCellValue("H" . $j, isset($item['ru']['name']) ? $item['ru']['name'] : '');

                $j++;
            }
        }
        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];

        $objSheet->getStyle('A1:G' . $j)->applyFromArray($styleArray);
        $objSheet->freezePaneByColumnAndRow(2, 2);
        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $localDir = ExcelHelperTrait::createExcelToLocalDir($objWriter, 'MaterialCat_' . date('YmdHis') . '.xls');

        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/mall/Uploadfile/upload';
        $data['tmp_name'] = $localDir;
        $data['type'] = 'application/xls';
        $data['name'] = pathinfo($localDir, PATHINFO_BASENAME);

        $fileId = postfile($data, $url);
        if (!empty($fileId['fileId'])) {
            unlink($localDir);
            return array('url' => $fastDFSServer . $fileId['fileId'] . '?filename=' . $fileId['file']['name'], 'name' => $fileId['name']);
        }
        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localDir . ' 上传到FastDFS失败', Log::INFO);
        return false;
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function listall($condition, $lang = '') {
        $where = $this->_getcondition($condition, $lang);
        try {
            $data = $this->where($where)
                    ->order('cat_no DESC')
                    ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,'
                            . 'sort_order,created_at,created_by')
                    ->select();
            $ret = [];
            $this->_setUserName($data);
            foreach ($data as $item) {
                $ret[$item['cat_no']]['parent_cat_no'] = $item['parent_cat_no'];
                $ret[$item['cat_no']]['status'] = $item['status'];
                $ret[$item['cat_no']]['created_at'] = $item['created_at'];
                $ret[$item['cat_no']]['created_by'] = $item['created_by'];
                $ret[$item['cat_no']]['level_no'] = $item['level_no'];
                $ret[$item['cat_no']]['created_by_name'] = $item['created_by_name'];
                $ret[$item['cat_no']][$item['lang']]['name'] = $item['name'];
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    private function _setUserName(&$arr) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                $userids[] = $val['created_by'];
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                    $val['created_by_name'] = $usernames[$val['created_by']];
                } else {
                    $val['created_by_name'] = '';
                }


                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取物料分类名称
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setMaterialCat(&$arr, $lang) {
        if ($arr) {

            $catnos = [];
            foreach ($arr as $key => $val) {
                if (!empty($val['material_cat_no'])) {
                    $catnos[] = $val['material_cat_no'];
                }
            }
            $catnames = $this->getNameByCatNos($catnos, $lang);
            foreach ($arr as $key => $val) {
                if ($val['category'] && !empty($val['material_cat_no']) && !empty($catnames[$val['material_cat_no']])) {
                    $val['material_cat_name'] = $catnames[$val['material_cat_no']];
                } else {
                    $val['material_cat_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取物料分类名称
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setMaterialCatAndNo(&$arr, $lang) {
        if ($arr) {

            $catnos = [];
            foreach ($arr as $key => $val) {
                if (!empty($val['material_cat_no'])) {
                    $catnos[] = $val['material_cat_no'];
                }
            }
            $catnames = $this->getNameByCatNos($catnos, $lang);
            foreach ($arr as $key => $val) {
                if (!empty($val['material_cat_no']) && !empty($catnames[$val['material_cat_no']])) {
                    $val['material_cat_no'] = $catnames[$val['material_cat_no']] . '-' . $val['material_cat_no'];
                }
                $arr[$key] = $val;
            }
        }
    }

}
