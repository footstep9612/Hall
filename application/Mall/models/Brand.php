<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Brand
 *
 * @author zhongyg
 */
class BrandModel extends PublicModel {

//put your code here

    protected $tableName = 'brand';
    protected $dbName = 'erui_dict'; //数据库名称

    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function __construct() {
        parent::__construct();
    }

    /*
     * 自动完成
     */

    protected $_auto = array(
        array('status', 'VALID'),
        array('created_at', 'getDate', 1, 'callback'),
    );
    /*
     * 自动表单验证
     */
    protected $_validate = array(
        array('brand', 'require', '品牌信息不能为空'),
    );

    /*
     * 获取当前时间
     */

    function getDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    private function _getcondition($condition, $lang = '') {

        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'id', 'string');

        $brand_table = $this->getTableName();
        $this->_getValue($where, $condition, 'status', 'string', 'status', 'VALID');

        if (!empty($condition['name']) && $lang) {
            $name = trim($condition['name']);
            $map1[$brand_table . '.`brand`'] = ['like', '%"lang":"' . $lang . '"%'];
            $map1['brand'] = ['like', '%"lang": "' . $lang . '"%'];
            $map1['_logic'] = 'or';
            $where[]['_complex'] = $map1;
            $map2[$brand_table . '.`brand`'] = ['like', '%"name":"' . $name . '"%'];
            $map2['brand'] = ['like', '%"name": "' . $name . '"%'];
            $map2['_logic'] = 'or';
            $where[]['_complex'] = $map2;
        } elseif ($lang) {
            $map1['brand.brand'] = ['like', '%"lang":"' . $lang . '"%'];
            $map1['brand'] = ['like', '%"lang": "' . $lang . '"%'];
            $map1['_logic'] = 'or';
            $where[]['_complex'] = $map1;
        } elseif (!empty($condition['name'])) {
            $name = trim($condition['name']);
            $map2['brand.brand'] = ['like', '%"name":"' . $name . '"%'];
            $map2['brand'] = ['like', '%"name": "' . $name . '"%'];
            $map2['_logic'] = 'or';
            $where[]['_complex'] = $map2;
        }
        return $where;
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getlist($condition, $lang = '', $field = 'brand') {
        $where = $this->_getcondition($condition, $lang);

        $redis_key = md5(json_encode($where) . $lang . $row_start . $pagesize);
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        try {
            $item = $this->where($where)
                    ->field($field)
                    ->order('id desc')
                    ->select();
            redisHashSet('Brand', $redis_key, json_encode($item));
            return $item;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getbrand($condition, $lang = '', $field = 'brand') {
        $where = $this->_getcondition($condition, $lang);

        try {
            $item = $this->where($where)
                    ->field($field)
                    ->order('id desc')
                    ->find();
            $brand_name = '';
            if (!empty($item['brand']) && $item['brand']) {
                $brand_langs = json_decode($item['brand'], true);
                foreach ($brand_langs as $brand_lang) {
                    if ($brand_lang['lang'] === $lang && $brand_lang['name']) {
                        $brand_name = $brand_lang['name'];
                    }
                }
            }
            return $brand_name;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

}
