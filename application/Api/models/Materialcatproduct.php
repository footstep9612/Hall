<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Materialcatproduct
 *
 * @author zhongyg
 */
class Materialcatproduct extends PublicModel {

  //put your code here
  //put your code here
  protected $dbName = 'erui_goods'; //数据库名称
  protected $tableName = 'material_cat_product'; //数据表表名

  const STATUS_DRAFT = 'DRAFT'; //草稿
  const STATUS_APPROVING = 'APPROVING'; //审核；
  const STATUS_VALID = 'VALID'; //生效；
  const STATUS_DELETED = 'DELETED'; //DELETED-删除

  public function __construct() {
    parent::__construct();
  }

  public function getcatnobyspu($spu) {
    try {
      $where = [
          'spu' => $spu,
          'status' => self::STATUS_VALID
      ];
      return $this->where($where)->field('cat_no')->find();
    } catch (Exception $ex) {
      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
      LOG::write($ex->getMessage(), LOG::ERR);
      return false;
    }
  }

}
