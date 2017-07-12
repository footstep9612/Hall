<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Showmaterialcat
 *
 * @author zhongyg
 */
class ShowmaterialcatModel extends PublicModel {

  //put your code here
  //put your code here
  protected $tableName = 'show_material_cat';
  protected $dbName = 'erui_goods'; //数据库名称

  public function __construct($str = '') {
    parent::__construct($str);
  }

  /*
   * 获取物料分类
   * 
   */

  public function getmaterialcatnobyshowcatno($showcatno, $lang = 'en') {

    try {
      return $this->alias('ms')
                      ->join('erui_goods.t_show_cat s on s.cat_no=ms.show_cat_no ')
                      ->where(['ms.show_cat_no' => $showcatno
                          , 'ms.status' => 'VALID',
                          's.status' => 'VALID',
                          's.lang' => $lang,
                      ])
                      ->field('ms.material_cat_no,ms.show_cat_no,ms.status,s.name,s.parent_cat_no')
                      ->find();
    } catch (Exception $ex) {
      Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
      Log::write($ex->getMessage());
      return [];
    }
  }

  /*
   * 根据物料分类编号和语言获取展示分类信息
   * @param array $material_cat_nos 物料分类编码
   * @param string  $lang 语言
   * return array
   */

  public function getshowcatsBymaterialcatno($material_cat_nos, $lang = 'en') {

    try {
      $material_cat_nos = array_values($material_cat_nos);
      $real_material_cat_nos = $this->table('erui_goods.t_material_cat')
              ->where(['cat_no' => ['in', $material_cat_nos],
                  'status' => 'VALID',
                  'lang' => $lang,
              ])
              ->select();

      $flag = $this->alias('ms')
              ->join('erui_goods.t_show_cat s on s.cat_no=ms.show_cat_no ', 'left')
              ->where(['ms.material_cat_no' => ['in', $real_material_cat_nos]
                  , 'ms.status' => 'VALID',
                  's.status' => 'VALID',
                  's.lang' => $lang,
              ])
              ->field('ms.material_cat_no,ms.show_cat_no as cat_no,'
                      . 'ms.status,s.name,s.parent_cat_no')
              ->select();
      echo $this->_sql();
      return $flag;
    } catch (Exception $ex) {
      Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
      Log::write($ex->getMessage());
      return [];
    }
  }

  /*
   * 获取展示分类编号
   * 
   */

  public function getshowcatnosBymatcatno($material_cat_no, $lang = 'en') {

    try {
      return $this->alias('ms')
                      ->join('erui_goods.t_show_cat s on s.cat_no=ms.show_cat_no ', 'left')
                      ->where(['ms.material_cat_no' => $material_cat_no
                          , 'ms.status' => 'VALID',
                          's.status' => 'VALID',
                          's.lang' => $lang,
                      ])
                      ->field('ms.show_cat_no as cat_no')
                      ->select();
    } catch (Exception $ex) {
      Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
      Log::write($ex->getMessage());
      return [];
    }
  }

  /*
   * 获取物料分类
   * 
   */

  public function getshowcatsBycatno($show_cat_nos, $lang = 'en') {

    try {

      if (!$show_cat_nos) {

        return [];
      }

      return $this->Table('erui_goods.t_show_cat')
                      ->where(['cat_no' => ['in', $show_cat_nos]
                          , 'status' => 'VALID',
                          'lang' => $lang,
                      ])
                      ->field('name,cat_no,parent_cat_no')
                      ->select();
    } catch (Exception $ex) {
      Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
      Log::write($ex->getMessage());
      return [];
    }
  }

}
