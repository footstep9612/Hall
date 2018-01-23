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
     * 自动表单验证
     */

    protected $_validate = array(
        array('lang', 'require', '语言不能为空'),
        array('cat_no', 'require', '分类编码不能为空'),
        array('level_no', 'number', '层级不能为空'),
        array('name', 'require', '名称不能为空'),
        array('status', 'require', '状态不能为空'),
    );

    /**
     * 分类树形
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function tree($condition = [], $limit = null) {
        $where = $this->_getcondition($condition);
        try {
            $this->where($where)
                    ->order('sort_order DESC')
                    ->field('cat_no as value,name as label,parent_cat_no');
            if ($limit) {
                $this->limit(0, 10);
            }
            $result = $this->select();
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 展示分类列表
     * @param array $condition  条件
     * @param string $field     检索字段
     * @return array|bool
     */
    public function getListbyfield($condition = [], $field = '') {
        $field = empty($field) ? 'cat_no,name' : $field;
        if (empty($condition)) {
            $condition['parent_cat_no'] = 0;
        }
        //语言默认取en 统一小写
        $condition['lang'] = isset($condition['lang']) ? strtolower($condition['lang']) : ( browser_lang() ? browser_lang() : 'en');
        $condition['status'] = self::STATUS_VALID;

        try {
            //后期优化缓存的读取
            //这里需要注意排序的顺序（注意与后台一致）
            $resouce = $this->field($field)->where($condition)->order('sort_order DESC')->select();
            $data = array(
                'count' => 0,
                'data' => array()
            );
            if ($resouce) {
                $data['data'] = $resouce;
                $data['count'] = count($resouce);
            }

            return $data;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 展示分类列表
     * @param array $condition  条件
     * @param string $lang     语言
     * @return array
     */
    public function getListByconandlang($condition = [], $lang = 'en') {
        if (isset($condition['cat_no']) && $condition['cat_no']) {
            $where['cat_no'] = $condition['cat_no'];
        }
        if (isset($condition['market_area_bn']) && $condition['market_area_bn']) {
            $where['market_area_bn'] = $condition['market_area_bn'];
        }
        if (isset($condition['country_bn']) && $condition['country_bn']) {
            $where['country_bn'] = $condition['country_bn'];
        }
        $where['lang'] = $lang ? strtolower($lang) : 'en';
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

        try {
            $data = $this->field(['cat_no'])->where($where)->order('sort_order DESC')
                    ->group('cat_no')
                    ->select();

            return $data;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return [];
        }
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
        getValue($where, $condition, 'market_area_bn');
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

    public function get_list($market_area_bn, $country_bn, $cat_no = '', $lang = 'en') {
        if ($market_area_bn) {
            $where['market_area_bn'] = $market_area_bn;
        } else {
            //  return [];
        }
        if ($country_bn) {
            $where['country_bn'] = $country_bn;
        } else {
            //return [];
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

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($cat_no = '', $lang = 'en') {
        $where['cat_no'] = $cat_no;
        if ($lang) {
            $where['lang'] = $lang;
        }

        $data = $this->where($where)
                ->field('id, cat_no, parent_cat_no, level_no, lang, name, status, '
                        . 'sort_order, created_at, created_by, big_icon, middle_icon, '
                        . 'small_icon, market_area_bn, country_bn,updated_at,updated_by')
                ->find();

        return $data;
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
                $cat3 = $this->field('id, cat_no, name, market_area_bn, country_bn')
                        ->where(['cat_no' => $cat_no, 'lang' => $lang, 'status' => 'VALID'])
                        ->find();
                if ($cat3) {
                    $cat2 = $this->field('id, cat_no, name')
                            ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                            ->find();
                } else {
                    return [];
                }
                if ($cat2) {
                    $cat1 = $this->field('id, cat_no, name')
                            ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                            ->find();
                } else {
                    return ['cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
                }
                if ($cat1) {
                    return ['cat_no1' => $cat1['cat_no'], 'cat_name1' => $cat1['name'], 'cat_no1' => $cat2['cat_no'], 'cat_name2' => $cat2['name'], 'cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
                } else {
                    return ['cat_no1' => $cat2['cat_no'], 'cat_name2' => $cat2['name'], 'cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
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

        $row = $this->where($where)
                ->field('id')
                ->find();
        return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
    }

    /**
     * 删除数据
     * @param  string $cat_no 分类编码
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($cat_no = '', $lang = '') {
        if (!$cat_no) {
            return false;
        } else {
            $where['cat_no'] = $cat_no;
        }
        if ($lang) {
            $where['lang'] = $lang;
        }
        $show_material_cat_model = new ShowMaterialCatModel();
        $show_cat_goods_model = new ShowCatGoodsModel();
        $show_cat_product_model = new ShowCatProductModel();
        $info = $this->where($where)->find();
        if ($info['level_no'] == 3) {
            $flag = $this->where($where)
                    ->save(['status' => self::STATUS_DELETED,
                'deleted_flag' => 'Y',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => defined('UID') ? UID : 0]);

            $show_material_cat_model->where([
                        'show_cat_no' => $cat_no])
                    ->delete();
            $show_cat_goods_model->where($where)
                    ->delete();
            $show_cat_product_model->where($where)
                    ->delete();
        } else {
            if ($info['level_no'] == 2) {
                $where['cat_no'] = ['like', substr($cat_no, 0, 4) . '%'];
                $pwhere['show_cat_no'] = ['like', substr($cat_no, 0, 4) . '%'];
            } elseif ($info['level_no'] == 1) {
                $where['cat_no'] = ['like', substr($cat_no, 0, 2) . '%'];
                $pwhere['show_cat_no'] = ['like', substr($cat_no, 0, 2) . '%'];
            }

            $flag = $this->where($where)
                    ->save(['status' => self::STATUS_DELETED,
                'deleted_flag' => 'Y',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => defined('UID') ? UID : 0]);

            $show_material_cat_model->where($pwhere)
                    ->delete();

            $show_cat_goods_model->where($where)
                    ->delete();

            $show_cat_product_model->where($where)
                    ->delete();
        }
        $es_product_model = new EsProductModel();
        if ($lang) {
            $es_product_model->update_showcats($cat_no, null, $lang);
        } else {
            foreach ($this->langs as $lan) {
                $es_product_model->update_showcats($cat_no, null, $lan);
            }
        }
        return $flag;
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
                $flag1 = $this->where(['cat_no' => $chang_cat_no])->save(['sort_order' => $sort_order['sort_order']]);
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
     * @param  string $cat_no 分类编码
     * @return bool
     * @author zyg
     */
    public function approving($cat_no = '', $lang = '') {

        $where['cat_no'] = $cat_no;
        if ($lang) {
            $where['lang'] = $lang;
        }
        $es_product_model = new EsProductModel();


        $flag = $this->where($where)
                ->save(['status' => self::STATUS_VALID]);
        if ($flag && !$lang) {
            $cat_new_en = $this->getinfo($cat_no, 'en');
            if ($cat_new_en) {
                $es_product_model->Replaceshowcats($where['cat_no'], ']', ', ' . json_encode($cat_new_en, 256), 'en');
            }
            $cat_new_zh = $this->getinfo($cat_no, 'en');
            if ($cat_new_zh) {
                $es_product_model->Replaceshowcats($where['cat_no'], ']', ', ' . json_encode($cat_new_zh, 256), 'zh');
            }
            $cat_new_es = $this->getinfo($cat_no, 'es');
            if ($cat_new_es) {
                $es_product_model->Replaceshowcats($where['cat_no'], ']', ', ' . json_encode($cat_new_es, 256), 'es');
            }
            $cat_new_ru = $this->getinfo($cat_no, 'ru');
            if ($cat_new_ru) {
                $es_product_model->Replaceshowcats($where['cat_no'], ']', json_encode($cat_new_ru, 256), 'ru');
            }
            return $flag;
        } elseif ($flag && $lang) {
            $cat_new = $this->getinfo($cat_no, $lang);
            if ($cat_new) {
                $es_product_model->Replaceshowcats($where['cat_no'], ']', json_encode($cat_new, 256), $lang);
            }
            return $flag;
        } else {
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($upcondition = []) {
        $condition = $upcondition;
        list($data, $where, $cat_no) = $this->getUpdateCondition($upcondition, defined('UID') ? UID : 0);
        $this->startTrans();
        $langs = ['en', 'es', 'zh', 'ru'];
        $data['updated_by'] = defined('UID') ? UID : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $old_info = [];
        foreach ($langs as $lang) {
            if (isset($condition[$lang]) && !empty($condition[$lang]['name'])) {
                $old_info[$lang] = $this->where(['cat_no' => $where['cat_no'], 'lang' => $lang])->field('id,cat_no,name')->find();
                $data['lang'] = $lang;
                $data['name'] = trim($condition[$lang]['name']);
                $where['lang'] = $lang;
                $add = $data;
                $add['cat_no'] = $cat_no;
                $add['status'] = self::STATUS_VALID;
                $add['created_by'] = defined('UID') ? UID : 0;
                $add['created_at'] = date('Y-m-d H:i:s');
                $flag = $this->Exist($where) ? $this->where($where)->save($data) : $this->add($add);
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
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function updateico_data($cat_no, $upcondition = []) {
        $data = [];
        if (!empty($upcondition['small_icon'])) {
            $data['small_icon'] = trim($upcondition['small_icon']);
        }
        if (!empty($upcondition['middle_icon'])) {
            $data['middle_icon'] = trim($upcondition['middle_icon']);
        }
        if (!empty($upcondition['big_icon'])) {
            $data['big_icon'] = trim($upcondition['big_icon']);
        }
        $data['updated_by'] = defined('UID') ? UID : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        $flag = $this->where(['cat_no' => $cat_no])->save($data);
        return $flag;
    }

    /**
     * 更新子分类数据和 产品商品展示分类信息
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    private function _updateOther(&$upcondition, &$where, &$data, &$cat_no, &$old_info) {
        $condition = $upcondition;
        $langs = ['en', 'es', 'zh', 'ru'];

        if ($upcondition['level_no'] == 2 && $where['cat_no'] != $data['cat_no']) {

            $childs = $this->get_list($cat_no);
            foreach ($childs as $key => $val) {
                $child_cat_no = $this->getCatNo($data['cat_no'], 3);
                $flag = $this->where(['cat_no' => $val['cat_no']])
                        ->save(['cat_no' => $child_cat_no, 'parent_cat_no' => $data['cat_no']]);
                if ($flag === false) {

                    return false;
                }
                $flag = $this->updateothercat($val['cat_no'], $child_cat_no);
                if ($flag === false) {
                    return false;
                }
                if (isset($condition['material_cat_nos']) && $condition['material_cat_nos']) {
                    $show_material_cat_model = new ShowMaterialCatModel();
                    $show_material_cat_model->where(['show_cat_no' => $val['cat_no']])
                            ->delete();
                    $dataList = [];
                    foreach ($condition['material_cat_nos'] as $key => $material_cat_no) {
                        $dataList[] = ['show_cat_no' => $child_cat_no,
                            'material_cat_no' => $material_cat_no,
                            'status' => 'VALID',
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => defined('UID') ? UID : 0,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'updated_by' => defined('UID') ? UID : 0
                        ];
                    }

                    $show_material_cat_model->addAll($dataList);
                }
            }
        } elseif ($upcondition['level_no'] == 3 && $where['cat_no'] != $data['cat_no']) {

            $flag = $this->updateothercat($where['cat_no'], !empty($cat_no) ? $cat_no : $where['cat_no']);
            if (isset($condition['material_cat_nos']) && $condition['material_cat_nos']) {
                $show_material_cat_model = new ShowMaterialCatModel();
                $show_material_cat_model->where(['show_cat_no' => $where['cat_no']])
                        ->delete();
                $dataList = [];
                $condition['material_cat_nos'] = array_unique($condition['material_cat_nos']);
                foreach ($condition['material_cat_nos'] as $key => $material_cat_no) {
                    $dataList[] = ['show_cat_no' => !empty($cat_no) ? $cat_no : $where['cat_no'],
                        'material_cat_no' => $material_cat_no,
                        'status' => 'VALID',
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => defined('UID') ? UID : 0,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => defined('UID') ? UID : 0
                    ];
                }
                $falg = $show_material_cat_model->addAll($dataList);
            }
            if (!$flag) {

                return false;
            }
        } elseif ($upcondition['level_no'] == 3) {

            $show_material_cat_model = new ShowMaterialCatModel();
            $show_material_cat_model->where(['show_cat_no' => $where['cat_no']])
                    ->delete();
            $dataList = [];
            $condition['material_cat_nos'] = array_unique($condition['material_cat_nos']);
            foreach ($condition['material_cat_nos'] as $key => $material_cat_no) {
                $dataList[] = ['show_cat_no' => $cat_no,
                    'material_cat_no' => $material_cat_no,
                    'status' => 'VALID',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => defined('UID') ? UID : 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'updated_by' => defined('UID') ? UID : 0
                ];
            }

            $flag = $show_material_cat_model->addAll($dataList);

            $es_product_model = new EsProductModel();
            foreach ($langs as $lang) {
                $info = $old_info[$lang];

                if (isset($upcondition[$lang]['name']) && isset($info['name']) && $info['name'] != $upcondition[$lang]['name']) {

                    $es_product_model->update_showcats($where['cat_no'], $lang);
                }
            }
        } else {

            $es_product_model = new EsProductModel();
            foreach ($langs as $lang) {
                $info = $old_info[$lang];
                if (isset($upcondition[$lang]['name']) && isset($info['name']) && $info['name'] != $upcondition[$lang]['name']) {
                    $es_product_model->update_showcats($where['cat_no'], $lang);
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
        $cat_no = '';
        $condition = $upcondition;
        if (isset($condition['market_area_bn']) && $condition['market_area_bn']) {
            $where['market_area_bn'] = $condition['market_area_bn'];
            $data['market_area_bn'] = $condition['market_area_bn'];
        }
        if (isset($condition['country_bn']) && $condition['country_bn']) {
            $where['country_bn'] = $condition['country_bn'];
            $data['country_bn'] = $condition['country_bn'];
        }

        if ($condition['cat_no']) {
            $where['cat_no'] = $condition['cat_no'];
            $info = $this->info($where['cat_no']);
            $upcondition['level_no'] = $info['level_no'];
        } else {
            return false;
        }
        if (isset($condition['level_no']) && $info['level_no'] != $condition['level_no']) {
            return false;
        }
        if (isset($upcondition['parent_cat_no']) && $upcondition['level_no'] == 1) {
            $data['parent_cat_no'] = 0;
        } elseif (isset($upcondition['parent_cat_no'])) {
            $data['parent_cat_no'] = $upcondition['parent_cat_no'];
        }
        if (isset($upcondition['level_no']) && in_array($upcondition['level_no'], [1, 2, 3])) {
            $data['level_no'] = $upcondition['level_no'];
        }
        if (isset($upcondition['top_no']) && in_array($upcondition['top_no'], [1, 2, 3])) {
            $data['top_no'] = $upcondition['top_no'];
        }

        if (!isset($data['parent_cat_no']) || $data['parent_cat_no'] != $info['parent_cat_no']) {
            $cat_no = $this->getCatNo($data['parent_cat_no'], $data['level_no']);
            if (!$cat_no) {
                return false;
            } else {
                $data['cat_no'] = $cat_no;
            }
        } else {
            $cat_no = $where['cat_no'];
            $data['cat_no'] = $cat_no;
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
        }
        if ($condition['sort_order']) {
            $data['sort_order'] = $condition['sort_order'];
        }
        if ($condition['small_icon']) {
            $data['small_icon'] = $condition['small_icon'];
        }
        if ($condition['middle_icon']) {
            $data['middle_icon'] = $condition['middle_icon'];
        } if ($condition['big_icon']) {
            $data['big_icon'] = $condition['big_icon'];
        }

        return [$data, $where, $cat_no];
    }

    public function updateothercat($old_cat_no, $new_cat_no) {
        $show_material_cat_model = new ShowMaterialCatModel();
        $flag_show_material = $show_material_cat_model
                ->where(['show_cat_no' => $old_cat_no])
                ->save(['show_cat_no' => $new_cat_no]);
        if ($flag_show_material === false) {
            $this->rollback();
            return false;
        }
        $show_cat_product_model = new ShowCatProductModel();
        $es_product_model = new EsProductModel();
        $flag_cat_product = $show_cat_product_model
                ->where(['cat_no' => $old_cat_no])
                ->save(['cat_no' => $new_cat_no]);
        if ($flag_cat_product === false) {
            $this->rollback();
            return false;
        }

        foreach ($this->langs as $lan) {
            $es_product_model->update_showcats($old_cat_no, $lan);
        }
        return true;
    }

    public function getCatNo($parent_cat_no = '', $level_no = 1) {

        if ($level_no < 1) $level_no = 1;
        if ($level_no >= 3) $level_no = 3;

        //一级分类编码
        if (empty($parent_cat_no) && $level_no == 1) {
            $re = $this->field('cat_no')->where(['level_no' => 1])->order('id DESC')->find();
            if (!empty($re['cat_no'])) {
                // 00+1:00:00
                $cat_no_seeds = explode(':',$re['cat_no']);
                $cat_no_seeds[0] = $cat_no_seeds[0]+1;

                if ($cat_no_seeds[0] < 10){
                    $cat_no_seeds[0] = str_pad($cat_no_seeds[0], 2, "0", STR_PAD_LEFT);
                }

                return implode(':',$cat_no_seeds);

            } else {

                return '01:00:00';
            }
        } elseif (empty($parent_cat_no)) {
            return false;
        } else {
            $re = $this->field('cat_no,parent_cat_no')
                    ->where(['parent_cat_no' => $parent_cat_no])
                    ->order('id DESC')
                    ->find();
            //p($re);
            //p($this->getLastSql());

            if (!empty($re['cat_no']) && $level_no == 3) {

                //三级分类编码
                $parent_cat_seeds = explode(':',$re['cat_no']);

                $parent_cat_seeds[2] = $parent_cat_seeds[2] + 1;

                if ($parent_cat_seeds[2] < 10){
                    $parent_cat_seeds[2] = str_pad($parent_cat_seeds[2], 2, "0", STR_PAD_LEFT);
                }

                return implode(':',$parent_cat_seeds);

            } elseif ($level_no == 3) {

                //三级分类编码
                $parent_cat_seeds = explode(':',$parent_cat_no);
                $parent_cat_seeds[2] = $parent_cat_seeds[2] + 1;

                if ($parent_cat_seeds[2] < 10){
                    $parent_cat_seeds[2] = str_pad($parent_cat_seeds[2], 2, "0", STR_PAD_LEFT);
                }

                return implode(':',$parent_cat_seeds);

            } elseif (!empty($re['cat_no']) && $level_no == 2) {

                //二级分类编码
                $parent_cat_seeds = explode(':',$re['cat_no']);
                $parent_cat_seeds[1] = $parent_cat_seeds[1] + 1;

                if ($parent_cat_seeds[1] < 10){
                    $parent_cat_seeds[1] = str_pad($parent_cat_seeds[1], 2, "0", STR_PAD_LEFT);
                }

                return implode(':',$parent_cat_seeds);

            } elseif ($level_no == 2) {
                //二级分类编码
                $parent_cat_seeds = explode(':',$parent_cat_no);
                $parent_cat_seeds[1] = $parent_cat_seeds[1] + 1;

                if ($parent_cat_seeds[1] < 10){
                    $parent_cat_seeds[1] = str_pad($parent_cat_seeds[1], 2, "0", STR_PAD_LEFT);
                }

                return implode(':',$parent_cat_seeds);
            }
        }
    }

    public function create_data($createcondition = []) {
        $data = $condition = $this->create($createcondition);

        if (isset($condition['cat_no'])) {
            $data['cat_no'] = $condition['cat_no'];
        }
        if (isset($condition['parent_cat_no']) && $condition['parent_cat_no']) {
            $info = $this->info($condition['parent_cat_no'], null);
            $condition['level_no'] = $info['level_no'] + 1;
        } else {
            $data['parent_cat_no'] = 0;
            $condition['level_no'] = 1;
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
        $data['created_by'] = defined('UID') ? UID : 0;
        $data['created_at'] = date('Y-m-d H:i:s');
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
        }
        $this->startTrans();
        $langs = ['en', 'es', 'zh', 'ru'];
        foreach ($langs as $lang) {
            if (isset($createcondition[$lang]) && !empty($createcondition[$lang]['name'])) {
                $data['lang'] = trim($lang);
                $data['name'] = trim($createcondition[$lang]['name']);
                $flag = $this->add($data);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
            }
        }


        if ($data['level_no'] == 3 && isset($createcondition['material_cat_nos']) && $createcondition['material_cat_nos']) {
            $dataList = [];
            $show_material_cat_model = new ShowMaterialCatModel();
            foreach ($createcondition['material_cat_nos'] as $material_cat_no) {
                $data = [
                    'show_cat_no' => $cat_no,
                    'material_cat_no' => $material_cat_no,
                    'status' => 'VALID',
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => defined('UID') ? UID : 0
                ];
                $dataList[] = $data = $show_material_cat_model->create($data);
            }

            $flag = $show_material_cat_model->addAll($dataList);
        }
        $this->commit();
        return $cat_no;
    }

    /**
     * 根据cat_no获取所属分类name
     * @param  string $code 编码
     * klp
     */
    protected $data = array();

    public function getNameByCat($cat_no = '', $lang = 'en') {
        if ($code == '')
            return '';
        $condition = array(
            'cat_no' => $cat_no,
            'status' => self::STATUS_VALID
        );
        if ($lang) {

            $condition['lang'] = $lang;
        }
        $resultTr = $this->field('name, parent_cat_no')->where($condition)->select();

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
    public function getShowCatByNo($catNo = '', $lang = '') {
        if ($catNo == '' || $lang == '')
            return array();

        //读取缓存
        if (redisHashExist('Show_cat', $catNo . '_' . $lang)) {
            return (array) json_decode(redisHashGet('Show_cat', $catNo . '_' . $lang));
        }

        try {
            $field = 'lang, cat_no, parent_cat_no, level_no, name, description, sort_order';
            $condition = array(
                'cat_no' => $catNo,
                'status' => self::STATUS_VALID,
                'lang' => $lang
            );
            $result = $this->field($field)->where($condition)->order('sort_order DESC')->find();
            if ($result) {
                redisHashSet('MeterialCat', $catNo . '_' . $lang, json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
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

        if (redisHashExist('Show_cat', md5($cat_name))) {
            return (array) json_decode(redisHashGet('Show_cat', md5($cat_name)));
        }
        try {
            $result = $this->field('cat_no')->where(array('name' => array('like', $cat_name)))->order('sort_order DESC')->select();
            if ($result)
                redisHashSet('Show_cat', md5($cat_name), json_encode($result));

            return $result ? $result : array();
        } catch (Exception $e) {
            return array();
        }
    }

    /*
     * 根据展示分类编码数组获取展示分类信息
     * @param mix $show_cat_nos // 展示分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getshow_cats($show_cat_nos, $lang = 'en') {

        try {
            if ($show_cat_nos) {
                $cat3s = $this->field('market_area_bn, country_bn, parent_cat_no, cat_no, name')
                        ->where(['cat_no' => ['in', $show_cat_nos], 'lang' => $lang, 'status' => 'VALID'])
                        ->select();
                $cat1_nos = $cat2_nos = [];
            } else {
                return [];
            }
            if (!$cat3s) {
                return [];
            }

            foreach ($cat3s as $cat) {
                $cat2_nos[] = $cat['parent_cat_no'];
            }
            if ($cat2_nos) {
                $cat2s = $this->field('id, cat_no, name, parent_cat_no')
                                ->where(['cat_no' => ['in', $cat2_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            }
            if (!$cat2s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no1' => '',
                        'cat_name1' => '',
                        'cat_no2' => '',
                        'cat_name2' => '',
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => trim($val['name']),
                        'market_area_bn' => $val['market_area_bn'],
                        'country_bn' => $val['country_bn']
                    ];
                }
                return $newcat3s;
            }
            foreach ($cat2s as $cat2) {
                $cat1_nos[] = $cat2['parent_cat_no'];
            }
            if ($cat1_nos) {
                $cat1s = $this->field('id, cat_no, name')
                                ->where(['cat_no' => ['in', $cat1_nos], 'lang' => $lang, 'status' => 'VALID'])->select();
            }

            $newcat2s = [];
            foreach ($cat2s as $val) {
                $newcat2s[$val['cat_no']] = $val;
            }
            if (!$cat1s) {
                $newcat3s = [];
                foreach ($cat3s as $val) {
                    $newcat3s[$val['cat_no']] = [
                        'cat_no3' => $val['cat_no'],
                        'cat_name3' => trim($val['name']),
                        'market_area_bn' => $val['market_area_bn'],
                        'country_bn' => $val['country_bn'],
                        'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                        'cat_name2' => trim($newcat2s[$val['parent_cat_no']]['name']),
                        'cat_no1' => '',
                        'cat_name1' => '',
                    ];
                }
                return $newcat3s;
            }
            $newcat1s = [];
            foreach ($cat1s as $val) {
                $newcat1s[$val['cat_no']] = $val;
            }
            foreach ($cat3s as $val) {
                $newcat3s[$val['cat_no']] = [
                    'cat_no1' => $newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['cat_no'],
                    'cat_name1' => trim($newcat1s[$newcat2s[$val['parent_cat_no']]['parent_cat_no']]['name']),
                    'cat_no2' => $newcat2s[$val['parent_cat_no']]['cat_no'],
                    'cat_name2' => trim($newcat2s[$val['parent_cat_no']]['name']),
                    'cat_no3' => $val['cat_no'],
                    'market_area_bn' => $val['market_area_bn'],
                    'country_bn' => $val['country_bn'],
                    'cat_name3' => trim($val['name'])];
            }
            return $newcat3s;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 判断展示分类名称是否重复
     */

    public function showCatExist($name, $lang, $market_area_bn, $country_bn, $level_no = null, $cat_no = null) {
        try {
            $where = [];

            if ($cat_no) {
                $where['cat_no'] = ['neq', $cat_no];
            }
            $where['level_no'] = ['eq', $level_no];
            $where['deleted_flag'] = 'N';
            $where['lang'] = $lang;
            $where['name'] = trim($name);
            $where['market_area_bn'] = $market_area_bn;
            $where['country_bn'] = $country_bn;
            $flag = $this->field('id')->where($where)
                    ->find();
            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /*
     * 导入展示分类
     */

    public function import() {
        $data = $this->get_excel();

        $show_cats = [];
        foreach ($data as $item) {
            if ($show_cats[$item['show_cat_name1_zh']]) {
                if (!isset($show_cats[$item['show_cat_name1_zh']]['childs'][$item['show_cat_name2_zh']])) {
                    $show_cats[$item['show_cat_name1_zh']]['childs'][$item['show_cat_name2_zh']] = [
                        'show_cat_name2_zh' => $item['show_cat_name2_zh'],
                        'show_cat_name2_en' => $item['show_cat_name2_en'],
                    ];
                }
                $material_cat_no3s = [];
                if (!isset($show_cats[$item['show_cat_name1_zh']]['childs'][$item['show_cat_name2_zh']]['childs'][$item['show_cat_name3_zh']]['material_cat_no3'])) {
                    $material_cat_no3s = $show_cats[$item['show_cat_name1_zh']]
                            ['childs'][$item['show_cat_name2_zh']]
                            ['childs'][$item['show_cat_name3_zh']]['material_cat_no3'];
                    $material_cat_no3s[] = $item['material_cat_no3'];
                } else {
                    $material_cat_no3s[] = $item['material_cat_no3'];
                }
                $show_cats[$item['show_cat_name1_zh']]['childs'][$item['show_cat_name2_zh']]['childs']
                        [$item['show_cat_name3_zh']] = [
                    'show_cat_name3_zh' => $item['show_cat_name3_zh'],
                    'show_cat_name3_en' => $item['show_cat_name3_en'],
                    'material_cat_no3' => $material_cat_no3s,
                ];
            } else {
                $show_cats[$item['show_cat_name1_zh']] = [
                    'show_cat_name1_zh' => $item['show_cat_name1_zh'],
                    'show_cat_name1_en' => $item['show_cat_name1_en'],
                    'childs' => [
                        $item['show_cat_name2_zh'] => [
                            'show_cat_name2_zh' => $item['show_cat_name2_zh'],
                            'show_cat_name2_en' => $item['show_cat_name2_en'],
                            'childs' => [
                                $item['show_cat_name3_zh'] => [
                                    'show_cat_name3_zh' => $item['show_cat_name3_zh'],
                                    'show_cat_name3_en' => $item['show_cat_name3_en'],
                                    'material_cat_no3' => [$item['material_cat_no3']],
                                ]]
                        ]]
                ];
            }
        }

        $market_area_bn = 'South America';
        $country_bn = 'Colombia';

        $ShowCatProductModel = new ShowCatProductModel();
        foreach ($show_cats as $show_cat1s) {
            $cat_no1 = null;
            $data1 = [];
            $data1['sort_order'] = 1;
            $data1['level_no'] = 1;
            $data1['en']['name'] = $show_cat1s['show_cat_name1_en'];
            $data1['zh']['name'] = $show_cat1s['show_cat_name1_zh'];
            $data1['parent_cat_no'] = null;
            $data1['material_cat_nos'] = [];
            $data1['market_area_bn'] = $market_area_bn;
            $data1['country_bn'] = $country_bn;
            $cat_no1 = $this->create_data($data1);
            if ($cat_no1) {
                foreach ($show_cat1s['childs'] as $show_cat2s) {
                    $data2 = [];
                    $cat_no2 = null;
                    $data2['sort_order'] = 1;
                    $data2['level_no'] = 2;
                    $data2['en']['name'] = $show_cat2s['show_cat_name2_en'];
                    $data2['zh']['name'] = $show_cat2s['show_cat_name2_zh'];
                    $data2['parent_cat_no'] = $cat_no1;
                    $data2['material_cat_nos'] = [];
                    $data2['market_area_bn'] = $market_area_bn;
                    $data2['country_bn'] = $country_bn;
                    $cat_no2 = $this->create_data($data2);
                    if ($cat_no2) {
                        foreach ($show_cat2s['childs'] as $show_cat3) {
                            $this->InsrtIntoShowCat3($show_cat3, $market_area_bn, $country_bn, $cat_no2);
                        }
                    } else {
                        Log::write('二级分类:导入失败,一级分类编码为:' . $cat_no1 . PHP_EOL . var_export($show_cat2s, true));
                    }
                }
            } else {
                Log::write(var_export($show_cat1s, true));
            }
        }
        return true;
    }

    public function InsrtIntoShowCat3($show_cat3, $market_area_bn, $country_bn, $cat_no2) {
        $data3 = [];
        $cat_no3 = null;
        $data3['sort_order'] = 1;
        $data3['level_no'] = 3;
        $data3['en']['name'] = $show_cat3['show_cat_name3_en'];
        $data3['zh']['name'] = $show_cat3['show_cat_name3_zh'];
        $data3['parent_cat_no'] = $cat_no2;
        $data3['material_cat_nos'] = $show_cat3['material_cat_no3'];
        $data3['market_area_bn'] = $market_area_bn;
        $data3['country_bn'] = $country_bn;
        $cat_no3 = $this->create_data($data3);
        if (!$cat_no3) {
            Log::write('三级级分类:导入失败,二级分类编码为:' . $cat_no2 . PHP_EOL . '三级分类编码:' . $cat_no3 . PHP_EOL . var_export($show_cat3, true));
        } else {
            $ShowCatProductModel = new ShowCatProductModel();
            $ShowCatProductModel->UpdateShowCatProductByMaterialCatNos($show_cat3['material_cat_no3'], $cat_no3);
            return $cat_no3;
        }
    }

    /*
     * 获取EXCEL信息
     */

    public function get_excel() {


        // $PHPExcel = new PHPExcel();
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));

        $localFile = MYPATH . DS . 'public' . DS . 'file' . DS . 'showcat.xlsx';
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        if (!$objReader->canRead($localFile)) {
            echo 'no Excel';
            return;
        }
        $PHPExcel = $objReader->load($localFile);    //加载文件

        $currentSheet = $PHPExcel->getSheet(0);
        $allColumn = $currentSheet->getHighestColumn();
        $allColumn_num = 0;
        for ($i = 0, $j = strlen($allColumn); $i < $j; $i++) {
            $allColumn_num += $i * 26 + ord($allColumn[$i]) - 65;
        }
        $allRow = $currentSheet->getHighestRow();
        $result = array();
        $val = $currentSheet->getCellByColumnAndRow(0, 1)->getValue();
        if ($val == '展示分类一级') {
            $row_name = array(
                'A' => 'show_cat_name1_zh', //展示分类一级(中文)
                'B' => 'show_cat_name1_en', //	展示分类一级(英文)
                'C' => 'show_cat_name2_zh', //	展示分类二级(中文)
                'D' => 'show_cat_name2_en', //	展示分类二级(英文)
                'E' => 'show_cat_name3_zh', //	展示分类三级(中文)
                'F' => 'show_cat_name3_en', //	展示分类三级(英文)
                'G' => 'material_cat_name1_zh', //	物料分类一级(中文)
                'H' => 'material_cat_name2_zh', //	物料分类二级(中文)
                'I' => 'material_cat_name3_zh', //	物料分类三级(中文)
                'J' => 'material_cat_no3',
            );
        } else {
            $row_name = array(
                'A' => 'market_area_bn', //营销区域
                'B' => 'country_bn', //国家
                'C' => 'show_cat_name1_zh', //展示分类一级(中文)
                'D' => 'show_cat_name1_en', //	展示分类一级(英文)
                'E' => 'show_cat_name2_zh', //	展示分类二级(中文)
                'F' => 'show_cat_name2_en', //	展示分类二级(英文)
                'G' => 'show_cat_name3_zh', //	展示分类三级(中文)
                'H' => 'show_cat_name3_en', //	展示分类三级(英文)
                'I' => 'material_cat_name1_zh', //	物料分类一级(中文)
                'J' => 'material_cat_name2_zh', //	物料分类二级(中文)
                'K' => 'material_cat_name3_zh', //	物料分类三级(中文)
                'L' => 'material_cat_no3',
            );
        }
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            for ($currentColumn = 0; $currentColumn <= $allColumn_num; $currentColumn++) {
                $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                $val = str_replace('_x000D_', '', $val);
                $quotient = intval($currentColumn / 26);
                $remainder = $currentColumn % 26;
                $f = $quotient > 0 ? chr(65 + $quotient - 1) : '';
                $s = $remainder >= 0 ? chr(65 + $remainder) : '';
                $newcurrentColumn = $f . $s;
                $result[$currentRow][$row_name[$newcurrentColumn]] = trim($val);
            }
        }

        unset($currentSheet, $PHPExcel, $objReader);
        //unlink($localFile);
        return $result;
    }

    public function UpdateSpuCountByShowCatNo($cat_no, $lang = 'en') {

        $data = $this->field('cat_no,parent_cat_no,spu_count')->where(['deleted_flag' => 'N', 'lang' => $lang, 'cat_no' => $cat_no])->find();
        if ($data) {
            $product_model = new ShowCatProductModel();
            $count = $product_model->where(['onshelf_flag' => 'Y', 'cat_no' => $cat_no])->count();

            $flag = $this->where(['deleted_flag' => 'N', 'lang' => $lang, 'cat_no' => $cat_no])->save(['spu_count' => $count]);
            if ($flag && $data['parent_cat_no']) {
                $num = intval($count) - intval($data['spu_count']);
                $this->UpdateSpuCountByCatno($data['parent_cat_no'], $lang, $num);
            }
        }
    }

    public function UpdateSpuCountByCatno($cat_no, $lang = 'en') {

        $data = $this->field('cat_no,parent_cat_no')->where(['deleted_flag' => 'N', 'lang' => $lang, 'cat_no' => $cat_no])->find();
        if ($data) {

            $spu_count = $this->where(['deleted_flag' => 'N', 'lang' => $lang, 'parent_cat_no' => $cat_no])->sum('spu_count');
            $flag = $this->where(['deleted_flag' => 'N', 'lang' => $lang, 'cat_no' => $cat_no])->save(['spu_count' => $spu_count]);
            if ($flag && $data['parent_cat_no']) {
                $flag = $this->UpdateSpuCountByCatno($data['parent_cat_no'], $lang, $num);
            }
        }
        return $flag;
    }

}
