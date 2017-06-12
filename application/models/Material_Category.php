<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Index\Model;

/**
 * Description of 物料分类
 *
 * @author zyg
 */
class Material_CategoryModel extends PublicModel {

    //表名
    protected $tableName = 'material_category';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取物料分类联动
     * @param int $code 分类code
     * @return array
     * @author Wen
     */
    public function getlist($code) {

    }

    /**
     * 获取物料分类
     * @param int $code 分类code
     * @return array
     * @author Wen
     */
    public function getinfo($code) {

    }

    /*
     * 分类条件解析
     */

    public function getcondition($condition) {

    }

    /**
     * 获取物料分类列表及子分类
     * @param array $condition //获取条件
     * @return array
     * @author Wen
     */
    public function getlistanchild($condition) {

    }

    /**
     * 获取分类数量
     * @param array $condition //获取条件
     * @return array
     * @author Wen
     */
    public function getCount($condition) {

    }

    /**
     * 更新物料分类
     * @param array $data //需要更新的数据
     * @return array
     * @author Wen
     */
    public function update($data, $code) {

    }

    /**
     * 新增物料分类
     * @param array $data //需要新增的数据
     * @return array
     * @author Wen
     */
    public function insert($data) {

    }

    /**
     * 删除物料分类
     * @param int $code //分类code
     * @return array
     * @author Wen
     */
    public function delete($code) {

    }

}
