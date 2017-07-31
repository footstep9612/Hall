<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class MarketAreaModel extends PublicModel {

  //put your code here
  protected $dbName = 'erui_dict';
  protected $tableName = 'market_area';

  public function __construct($str = '') {
    parent::__construct($str = '');
  }

  private function getCondition($condition) {
    $data = [];
    if (isset($condition['lang']) && $condition['lang']) {
      $data['lang'] = $condition['lang'];
    }
    if (isset($condition['bn']) && $condition['bn']) {
      $data['bn'] = ucwords($condition['bn']);
    }
    if (isset($condition['parent_bn']) && $condition['parent_bn']) {
      $data['parent_bn'] = $condition['parent_bn'];
    }

    if (isset($condition['group_id']) && $condition['group_id']) {
      $data['group_id'] = $condition['group_id'];
    }
    if (isset($condition['name']) && $condition['name']) {
      $data['name'] = ['like', '%' . $condition['name'] . '%'];
    }
    if (isset($condition['url']) && $condition['url']) {
      $data['url'] = ['like', '%' . $condition['url'] . '%'];
    }
    return $data;
  }

  /**
   * 获取列表
   * @param data $data;
   * @return array
   * @author jhw
   */
  public function getlistBycodition($condition, $order = 'id desc', $type = true) {
    try {
      $data = $this->getCondition($condition);

      if ($type) {
        $pagesize = 10;
        $current_no = 1;
        if (isset($condition['current_no']) && $condition['current_no']) {
          $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
        }
        if (isset($condition['pagesize']) && $condition['pagesize']) {
          $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        $from = ($current_no - 1) * $pagesize;
      }
      $this->field('id,lang,bn,parent_bn,name as zh_name,url,group_id,'
                      . '(select ma.name from erui_dict.t_market_area as ma where ma.bn=bn) as en_name ')
              ->where($data);
      if ($type) {
        $this->limit($from . ',' . $pagesize);
      }
      return $this->order($order)
                      ->select();
    } catch (Exception $ex) {
      print_r($ex);
      return [];
    }
  }

  /*
   * 获取数据
   */

  public function getCount($condition) {
    try {
      $data = $this->getCondition($condition);
      return $this->where($data)->count();
    } catch (Exception $ex) {

      return 0;
    }
  }

  /**
   * 获取列表
   * @param data $data;
   * @return array
   * @author jhw
   */
  public function getlist($data, $limit, $order = 'id desc') {
    try {
      if (!empty($limit)) {
        return $this->field('id,lang,bn,parent_bn,name,url,group_id')
                        ->where($data)
                        ->limit($limit['page'] . ',' . $limit['num'])
                        ->order($order)
                        ->select();
      } else {
        return $this->field('id,lang,bn,parent_bn,name,url,group_id')
                        ->where($data)
                        ->order($order)
                        ->select();
      }
    } catch (Exception $ex) {

      print_r($ex);
    }
  }

  /**
   * 获取列表
   * @param  string  $bn
   * @param  string  $lang
   * @return array
   * @author jhw
   */
  public function info($bn = '', $lang = 'en') {
    $where['bn'] = $bn;
    $where['lang'] = $lang;
    if (!empty($where)) {
      $row = $this->where($where)
              ->field('id,lang,bn,name,url,group_id')
              ->find();
      return $row;
    } else {
      return false;
    }
  }

  /**
   * 获取列表
   * @param  int  $id
   * @return array
   * @author jhw
   */
  public function detail($id = '') {
    $where['id'] = $id;
    if (!empty($where['id'])) {
      $row = $this->where($where)
              ->field('id,lang,bn,name,url,group_id')
              ->find();
      return $row;
    } else {
      return false;
    }
  }

  /**
   * 删除数据
   * @param  int  $id
   * @return bool
   * @author jhw
   */
  public function delete_data($id = '') {
    $where['id'] = $id;
    if (!empty($where['id'])) {
      return $this->where($where)
                      ->delete();
    } else {
      return false;
    }
  }

  /**
   * 修改数据
   * @param  int $id id
   * @return bool
   * @author jhw
   */
  public function update_data($data, $where) {
    if (!isset($data['bn']) || !$data['bn']) {
      return false;
    }
    $where['bn'] = $data['bn'];

    $arr['bn'] = ucwords($data['en']['name']);
    $data['en']['name'] = ucwords($data['en']['name']);
    $this->startTrans();
    foreach ($data as $key => $name) {
      $arr['lang'] = $key;
      $arr['name'] = $name;
      $where['lang'] = $key;
      if ($this->Exits($where)) {
        $flag = $this->where($where)->save($arr);
        if (!$flag) {
          $this->rollback();
          return false;
        }
      } else {
        $flag = $this->add($arr);
        if (!$flag) {
          $this->rollback();
          return false;
        }
      }
    }
    $this->commit();
    return true;
  }

  public function Exits($where) {

    return $this->where($where)->find();
  }

  /**
   * 新增数据
   * @param  mix $create 新增条件
   * @return bool
   * @author jhw
   */
  public function create_data($create = []) {
    if (isset($create['en']['name']) && isset($create['zh']['name'])) {
      $datalist = [];
      $arr['bn'] = ucwords($create['en']['name']);
      $create['en']['name'] = ucwords($create['en']['name']);
      foreach ($create as $key => $name) {
        $arr['lang'] = $key;
        $arr['name'] = $name;
        $datalist[] = $arr;
      }
      return $this->addAll($datalist);
    } else {
      return false;
    }
  }

}
