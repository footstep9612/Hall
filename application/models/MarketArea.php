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
      $data['bn'] = $condition['bn'];
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
      $this->field('id,lang,bn,parent_bn,name,url,group_id,'
                      . '(select name from erui_sys.t_group where erui_sys.t_group.id=group_id) as group_name ')
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
    if (isset($data['lang'])) {
      $arr['lang'] = $data['lang'];
    }
    if (isset($data['bn'])) {
      $arr['bn'] = $data['bn'];
    }
    if (isset($data['name'])) {
      $arr['name'] = $data['name'];
    }

    if (isset($data['group_id']) && $data['group_id']) {
      $arr['group_id'] = $data['group_id'];
    } else {
      $arr['group_id'] = 0;
    }
    if (isset($data['url']) && $data['url']) {
      $arr['url'] = $data['url'];
    } else {
      $arr['url'] = '';
    }
    if (isset($data['parent_bn']) && $data['parent_bn']) {
      $arr['parent_bn'] = $data['parent_bn'];
    } else {
      $arr['parent_bn'] = '';
    }
    if (!empty($where)) {
      return $this->where($where)->save($arr);
    } else {
      return false;
    }
  }

  /**
   * 新增数据
   * @param  mix $create 新增条件
   * @return bool
   * @author jhw
   */
  public function create_data($create = []) {
    if (isset($create['lang']) && $create['lang']) {
      $arr['lang'] = $create['lang'];
    } else {
      return false;
    }
    if (isset($create['bn']) && $create['bn']) {
      $arr['bn'] = $create['bn'];
    } else {
      $create['bn'] = '';
    }
    if (isset($create['name']) && $create['name']) {
      $arr['name'] = $create['name'];
    } else {
      return false;
    }

    if (isset($create['group_id']) && $create['group_id']) {
      $arr['group_id'] = $create['group_id'];
    } else {
      $arr['group_id'] = 0;
    }
    if (isset($create['url']) && $create['url']) {
      $arr['url'] = $create['url'];
    } else {
      $arr['url'] = '';
    }
    if (isset($create['parent_bn']) && $create['parent_bn']) {
      $arr['parent_bn'] = $create['parent_bn'];
    } else {
      $arr['parent_bn'] = '';
    }

    $data = $this->create($arr);
    return $this->add($data);
  }

}
