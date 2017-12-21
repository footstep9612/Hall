<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SPU关联
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class ProductRelationModel extends PublicModel {

    //put your code here
    protected $tableName = 'product_relation';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    private function _getCondition($spu, $lang, $condition = null) {
        $where = ['pr.spu' => $spu,
            'pr.lang' => $lang,
            'pr.deleted_flag' => 'N',
            'p.deleted_flag' => 'N'];

        if (!empty($condition['cat_no3'])) {
            $where['p.material_cat_no'] = trim($condition['cat_no3']);
        } elseif (!empty($condition['cat_no2'])) {
            $where['p.material_cat_no'] = ['like', trim($condition['cat_no2']) . '%'];
        } elseif (!empty($condition['cat_no1'])) {
            $where['p.material_cat_no'] = ['like', trim($condition['cat_no1']) . '%'];
        }
        $this->_getValue($where, $condition, 'relation_spu', 'string', 'pr.spu');
        $this->_getValue($where, $condition, 'status', 'string', 'p.status');
        $this->_getValue($where, $condition, 'name', 'string', 'p.name');

        if (!empty($condition['brand'])) {
            $where['p.brand'] = ['like', '%' . trim($condition['brand']) . '%'];
        }
        return $where;
    }

    /**
     * Description of 获取SPU关联列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  SPU关联
     */
    public function getList($spu, $lang, $offset, $size, $condition = null) {
        $product_model = new ProductModel();
        $product_table = $product_model->getTableName();
//        $show_cat_product_model = new ShowCatProductModel();
//        $show_cat_product_table = $show_cat_product_model->getTableName();
        $where = ['pr.spu' => $spu,
            'pr.lang' => $lang,
            'pr.deleted_flag' => 'N',
            'p.deleted_flag' => 'N'];
        return $this->alias('pr')
                        ->field('p.id,p.lang,p.spu,p.name,p.brand,p.material_cat_no,p.status')
                        ->join($product_table . ' p on p.spu=pr.relation_spu and p.lang=\'zh\' ')
                        //  ->join($show_cat_product_table . ' sp on sp.spu=pr.relation_spu and sp.lang=\'zh\' ')
                        ->where($where)
                        ->limit($offset, $size)
                        ->select();
    }

    /**
     * Description of 判断国家现货是否存在
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExit($spu, $relation_spu, $lang) {
        $where['spu'] = $spu;
        $where['lang'] = $lang;
        $where['relation_spu'] = $relation_spu;
        return $this->where($where)->getField('id');
    }

    /**
     * Description of 获取SPU关联列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  SPU关联
     */
    public function getCont($spu, $lang) {
        $where = ['spu' => $spu, 'lang' => $lang];
        return $this->where($where)
                        ->count();
    }

    /**
     * Description of 新加SPU关联
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  SPU关联
     */
    public function createData($spu, $spus, $lang) {
        $where['lang'] = $lang;
        $where['spu'] = $spu;
        $product_model = new ProductModel();

        $this->where($where)->save(['deleted_flag' => 'Y']);
        $this->startTrans();
        $product_model->where($where)->save(['relation_flag' => 'N']);
        foreach ($spus as $relation_spu) {
            $data['lang'] = $lang;
            $data['spu'] = $spu;
            $data['relation_spu'] = $relation_spu;

            $flag = false;
            if ($id = $this->getExit($spu, $relation_spu, $lang)) {
                $data['updated_by'] = defined('UID') ? UID : 0;
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['deleted_flag'] = 'N';
                $flag = $this->where(['id' => $id])->save($data);
            } else {
                $data['created_by'] = defined('UID') ? UID : 0;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['deleted_flag'] = 'N';
                $flag = $this->add($data);
            }
            if (!$flag) {
                $this->rollback();
                return false;
            }
        }
        $flag = $product_model->where($where)->save(['relation_flag' => 'Y']);
        if (!$flag) {
            $this->rollback();
            return false;
        }
        $es = new ESClient();
        $es->update_document('erui_goods', 'product_' . $lang, ['relation_flag' => 'Y'], $spu);
        $this->commit();
        return true;
    }

    /**
     * Description of 更新SPU关联
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  SPU关联
     */
    public function deletedData($id) {


        return $this->where(['id' => $id])->save(['deleted_flag' => 'Y']);
    }

}
