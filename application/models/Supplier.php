<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 供应商管理
 *
 * @author zyg
 */
class SupplierModel extends PublicModel {

  protected $tableName = 'supplier';
  protected $dbName = 'erui_supplier'; //数据库名称

  public function __construct($str = '') {

    parent::__construct();
  }

  /**
   * 通过生产商ID或名称获取供应商信息
   * @param array $condition 生产商ID或名称
   * @param string $lang 语言
   * @return array 
   * @author zyg
   */
  public function getlist($condition, $lang = 'en') {
    $where = array('lang' => $lang);

    if (isset($condition['keyword']) && !empty($condition['keyword'])) {
      $keyword = $condition['keyword'];
      $where1['supplier_id'] = $keyword;
      $where1['name'] = ['like', '%' . $keyword . '%'];
      $where1['_logic'] = 'or';
      $where['_complex'] = $where1;
    }
    $current_no = isset($condition['current_no']) ? $condition['current_no'] : 1;
    $pagesize = isset($condition['pagesize']) ? $condition['pagesize'] : 10;
    $data = $this->where($where)->limit(($current_no - 1) * $pagesize, $pagesize)->select();
    $count = $this->where($where)->count();
    return ['current_no' => $current_no, 'pagesize' => $pagesize, 'count' => $count, 'data' => $data];
  }

}
