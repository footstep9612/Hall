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
  protected $dbName = 'erui_goods'; //数据库名称
  Protected $autoCheckFields = false;

  const STATUS_DRAFT = 'DRAFT'; //草稿
  const STATUS_APPROVING = 'APPROVING'; //审核；
  const STATUS_VALID = 'VALID'; //生效；
  const STATUS_DELETED = 'DELETED'; //DELETED-删除

  public function __construct() {
    parent::__construct();
  }

  public function getcondition($name, $lang = 'en') {

    $where = [];
    var_dump(!empty($name), $name);
    if (!empty($name)) {
      $where['name'] = ['like', '%' . $name . '%'];
    }
    if ($lang) {
      $where['lang'] = $lang;
    }
    return $where;
  }

  /**
   * 获取数据条数
   * @param mix $condition
   * @return mix
   * @author zyg
   */
  public function getcount($name, $lang) {
    $where = $this->getcondition($name, $lang);
    try {
      return $this->where($where)
                      ->count('id');
    } catch (Exception $ex) {
      Log::write($ex->getMessage(), Log::ERR);
      return false;
    }
  }

  /**
   * 获取列表
   * @param mix $condition
   * @return mix
   * @author zyg
   */
  public function getlist($name, $lang = 'en', $current_no = 1, $pagesize = 10) {
    $where = $this->getcondition($name, $lang);
    if (intval($current_no) <= 1) {
      $row_start = 0;
    } else {
      $row_start = (intval($current_no) - 1) * $pagesize;
    }
    if ($pagesize < 1) {
      $pagesize = 10;
    }
    return $this->where($where)
                    ->field('id,lang,name,logo,recommend_flag,mode,sort_order,created_by,'
                            . 'created_at,brand_no')
                    ->order('sort_order desc')
                    ->limit($row_start . ',' . $pagesize)
                    ->select();
  }

  /**
   * 获取列表
   * @param mix $condition
   * @return mix
   * @author zyg
   */
  public function listall($name, $lang = 'zh') {
    $where = $this->getcondition($name, $lang);
    return $this->where($where)
                    ->field('id,name,brand_no')
                    ->order('sort_order desc')
                    ->select();
  }

  /**
   * 获取列表
   * @param  string $code 编码
   * @param  int $id id
   * @param  string $lang 语言
   * @return mix
   * @author zyg
   */
  public function info($brand_no = '', $lang = 'en') {
    if ($brand_no) {
      $where['brand_no'] = $brand_no;
    } else {
      return [];
    }
    if ($lang) {
      $where['lang'] = $lang;
    }
    return $this->where($where)
                    ->find();
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
   * @param  string $brand_no
   * @param  string $lang 语言
   * @return bool
   * @author zyg
   */
  public function delete_data($brand_no = '', $brand_id = 0, $lang = 'en') {
    if (!$brand_no && !$brand_id) {
      return false;
    } elseif ($brand_no) {
      $where['brand_no'] = $brand_no;
      if ($lang) {
        $where['lang'] = $lang;
      }
    } elseif ($brand_id) {
      $where['id'] = $brand_id;
    }

    $this->startTrans();
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_DELETED]);
    if ($flag) {
      return true;
    } else {
      $this->rollback();
      return false;
    }
    $this->commit();
    return $flag;
  }

  /**
   * 删除数据
   * @param  string $brand_nos
   * @param  string $lang 语言
   * @return bool
   * @author zyg
   */
  public function batchdelete_data($brand_nos = '', $brand_ids, $lang = 'en') {
    if (!$brand_nos && !$brand_ids) {
      return false;
    } elseif ($brand_nos) {
      $where['brand_no'] = ['in', $brand_nos];
      if ($lang) {
        $where['lang'] = $lang;
      }
    } elseif ($brand_ids) {
      $where['id'] = ['in', $brand_ids];
    }
    $this->startTrans();
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_DELETED]);
    if ($flag) {
      return true;
    } else {
      $this->rollback();
      return false;
    }
    $this->commit();
    return $flag;
  }

  /**
   * 更新数据
   * @param  mix $upcondition 更新条件
   * @return mix
   * @author zyg
   */
  public function update_data($upcondition = [], $username = '') {
    $data = $this->create($upcondition);
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $username;
    $this->startTrans();
    if (isset($upcondition['en'])) {
      $data['lang'] = 'en';
      $data['name'] = $upcondition['en']['name'];
      $where['lang'] = $data['lang'];
      $exist_flag = $this->Exist($where);
      $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($upcondition['zh'])) {
      $data['lang'] = 'zh';
      $data['name'] = $upcondition['zh']['name'];
      $where['lang'] = $data['lang'];
      $exist_flag = $this->Exist($where);
      $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($upcondition['es'])) {
      $data['lang'] = 'es';
      $data['name'] = $upcondition['es']['name'];
      $where['lang'] = $data['lang'];
      $flag = $this->Exist($where) ? $this->where($where)->save($data) : $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($upcondition['ru'])) {
      $data['lang'] = 'ru';
      $data['name'] = $upcondition['ru']['name'];
      $where['lang'] = $data['lang'];
      $exist_flag = $this->Exist($where);
      $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }

    $this->commit();
    return $flag;
  }

  /**
   * 新增数据
   * @param  mix $createcondition 新增条件
   * @return bool
   * @author zyg
   */
  public function create_data($createcondition = [], $username = '') {

    $data = $condition = $this->create($createcondition);
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $username;
    $data['brand_no'] = $createcondition['en']['name'];
    if (!isset($condition['status'])) {
      $condition['status'] = self::STATUS_APPROVING;
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
      default :
        $data['status'] = self::STATUS_APPROVING;
    }
    if ($condition['sort_order']) {
      $data['sort_order'] = $condition['sort_order'];
    }
    $this->startTrans();
    $maxid = $this->getMaxid();
    if (isset($createcondition['en'])) {
      $data['lang'] = 'en';
      $maxid++;
      $data['id'] = $maxid;
      $data['name'] = $createcondition['en']['name'];
      $flag = $this->add($data);

      if (!$flag) {

        $this->rollback();
        return false;
      }
    }
    if (isset($createcondition['zh'])) {
      $data['lang'] = 'zh';
      $maxid++;
      $data['id'] = $maxid;
      $data['name'] = $createcondition['zh']['name'];
      $flag = $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($createcondition['es'])) {
      $data['lang'] = 'es';
      $maxid++;
      $data['id'] = $maxid;
      $data['name'] = $createcondition['es']['name'];
      $flag = $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($createcondition['ru'])) {
      $data['lang'] = 'ru';
      $maxid++;
      $data['id'] = $maxid;
      $data['name'] = $createcondition['ru']['name'];
      $flag = $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    $this->commit();
    return $flag;
  }

}
