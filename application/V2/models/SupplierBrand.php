<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author klp
 */
class SupplierBrandModel extends PublicModel {

    protected $tableName = 'supplier_brand';
    protected $dbName = 'erui_supplier'; //数据库名称

    public function __construct($str = '') {
        parent::__construct();
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    private function _getcondition($condition) {

        $where = [];
        $this->_getValue($where, $condition, 'supplier_id', 'string', 'B.supplier_id'); //按供应商ID 搜索
        $this->_getValue($where, $condition, 'brand_id', 'string', 'B.brand_id'); //按品牌ID 搜索
        $this->_getValue($where, $condition, 'status', 'string', 'B.status', 'VALID');
        $this->_getValue($where, $condition, 'supplier_name', 'string', 'S.name');
        if (!empty($condition['brand_name'])) {
            $brand_name = trim($condition['brand_name']);
            $where[] = 'B.brand_zh like \'%' . $brand_name . '%\' or '
                    . 'B.brand_en like \'%' . $brand_name . '%\' or '
                    . 'B.brand_es like \'%' . $brand_name . '%\' or '
                    . 'B.brand_ru like \'%' . $brand_name . '%\'  ';
        }
        return $where;
    }

    /**
     * 根据条件获取供应商品牌列表
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function getList($condition) {

        $where = $this->_getcondition($condition);
        try {
            list($row_start, $pagesize) = $this->_getPage($condition);
            $result = $this->alias('B')
                    ->join('erui_supplier.supplier S on S.id=B.supplier_id', 'left')
                    ->field('S.name,B.brand_zh,B.brand_en,B.brand_es,B.brand_ru')
                    ->where($where)
                    ->limit($row_start, $pagesize)
                    ->select();
            return $result;
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 根据条件获取供应商品牌列表
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function getBrands($brand_name, $supplier_id) {

//    const STATUS_DRAFT = 'DRAFT'; //草稿
//    const STATUS_APPROVING = 'APPROVING'; //审核；
//    const STATUS_VALID = 'VALID'; //生效；
//    const STATUS_DELETED = 'DELETED'; //DELETED-删除

        $where = ['B.deleted_flag' => 'N',
            'B.status' => BrandModel::STATUS_VALID
        ];
        $table = $this->getTableName();
        if ($supplier_id) {
            $where[] = 'B.id not in (select brand_id from ' . $table . ' where `status`=\'VALID\' AND supplier_id=\'' . $supplier_id . '\')';
        }
        if ($brand_name) {
            $where['brand'] = ['like', '\'%"name":"%' . trim($brand_name) . '%\''];
        }

        try {
            $brandModel = new BrandModel();
            $result = $brandModel->alias('B')
                    ->field('B.brand,B.id')
                    ->where($where)
                    ->order('id desc')
                    ->select();
            $ret = null;
            foreach ($result as $brandinfo) {
                $brand = json_decode($brandinfo['brand'], true);
                foreach ($brand as $brand_lang) {

                    if ($brand_lang['lang'] === 'zh') {
                        $ret[] = ['brand_name' => $brand_lang['name'], 'brand_id' => $brandinfo['id'],];
                        break;
                    }
                }
            }
            return $ret;
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 根据条件获取供应商品牌列表
     * @param int $supplier_id 搜索条件
     * @return mix
     * @author zyg
     */
    public function listBySupplierId($supplier_id) {

        try {

            $result = $this->alias('B')
                    ->field('B.brand_id,B.brand_zh')
                    ->where(['supplier_id' => $supplier_id, 'status' => 'VALID'])
                    ->select();
            return $result;
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 根据条件获取供应商数量
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function getSupplierCount($condition = null) {


        try {

            $supplier_model = new SupplierChainModel();
            return $supplier_model->getCount();
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return 0;
        }
    }

    /**
     * 根据条件获取品牌数量
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function getBrandsCount($condition) {

        $where = $this->_getcondition($condition);
        try {

            $result = $this->alias('B')
                    ->join('erui_supplier.supplier S on S.id=B.supplier_id', 'left')
                    ->field('S.name,B.brand_zh,B.brand_en,B.brand_es,B.brand_ru')
                    ->where($where)
                    ->group('B.brand_id')
                    ->select();
            return count($result);
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return 0;
        }
    }

    /**
     * 根据条件获取品牌数量
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function getBrandCount($condition) {

        $brand_model = new BrandModel();
        return $brand_model->getCount(null);
//        $where = $this->_getcondition($condition);
//        try {
//
//            $result = $this->alias('B')
//                    ->join('erui_supplier.supplier S on S.id=B.supplier_id', 'left')
//                    ->field('S.name,B.brand_zh,B.brand_en,B.brand_es,B.brand_ru')
//                    // ->where($where)
//                    ->group('B.brand_id')
//                    ->select();
//            return count($result);
    }

    /**
     * 根据条件获取品牌数量
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function create_data($condition) {

        $brand_id = $condition['brand_id'];
        $supplier_id = $condition['supplier_id'];
        try {
            $supplier_brand = $this->field('id')->where(['brand_id' => $brand_id, 'supplier_id' => $supplier_id])->find();
            $brand_model = new BrandModel();
            $brand = $brand_model->field('brand')->where(['id' => $brand_id])->find();
            $brandlang = json_decode($brand['brand'], true);
            $data['supplier_id'] = $supplier_id;
            $data['brand_id'] = $brand_id;

            foreach ($brandlang as $brand) {
                if (isset($brand['lang']) && in_array($brand['lang'], ['en', 'es', 'zh', 'ru'])) {
                    $data['brand_' . $brand['lang']] = isset($brand['name']) ? $brand['name'] : '';
                }
            }
            if ($supplier_brand) {
                $data['updated_by'] = defined('UID') ? UID : 0;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['status'] = 'VALID';
                $flag = $this->where(['brand_id' => $brand_id, 'supplier_id' => $supplier_id])->save($data);
            } else {
                $data['created_by'] = defined('UID') ? UID : 0;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['status'] = 'VALID';
                $flag = $this->add($data);
            }


            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage());
            return false;
        }
    }

    /**
     * 更新供应商品牌
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function update_data($condition) {

        $this->create_data($condition);
    }

    /**
     * 批量更新供应商品牌
     * @param mix $condition 搜索条件
     * @return mix
     * @author zyg
     */
    public function updateAndCreates($condition) {
        $brand_ids = $condition['brand_ids'];
        $supplier_id = $condition['supplier_id'];
        $this->startTrans();
        $where = ['supplier_id' => $supplier_id];
        if ($brand_ids) {
            $where['brand_id'] = ['notin', $brand_ids];
        }
        $this->where($where)->save(['status' => 'DELETED']);
        if ($brand_ids) {
            foreach ($brand_ids as $brand_id) {
                $flag = $this->create_data(['brand_id' => $brand_id, 'supplier_id' => $supplier_id]);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
            }
        }
        $this->commit();
        return true;
    }

}
