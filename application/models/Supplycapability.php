<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Supplycapability
 *
 * @author zhongyg
 */
class SupplycapabilityModel extends PublicModel {

  //put your code here
  protected $tableName = 'supply_capability';
  protected $dbName = 'erui_goods'; //数据库名称

  public function __construct($str = '') {
    parent::__construct($str);
  }

  function getcondition($condistion, $lang = 'en') {
    $data = [];

    if ($lang) {
      $data['lang'] = $lang;
    } else {
      $data['lang'] = 'en';
    }
    if (isset($condistion['cat_no']) && is_array($condistion['cat_no'])) {
      $data['cat_no'] = ['in', $condistion['cat_no']];
    } elseif (isset($condistion['cat_no'])) {
      $data['cat_no'] = $condistion['cat_no'];
    }
    if (isset($condistion['cat_nos']) && is_array($condistion['cat_nos'])) {
      $data['cat_no'] = ['in', $condistion['cat_nos']];
    }
    if (isset($condistion['status']) && in_array($condistion['status'], ['DRAFT', 'APPROVING', 'VALID', 'DELETED'])) {
      $data['status'] = $condistion['status'];
    } else {
      $data['status'] = 'VALID';
    }
    if (isset($condistion['created_by'])) {
      $data['created_by'] = $condistion['created_by'];
    }
    if (isset($condistion['created_at']) && is_string($condistion['created_at'])) {
      $data['created_at'] = $condistion['created_at'];
    }

    if (isset($condistion['created_at_start']) && isset($condistion['created_at_end'])) {
      $data['created_at'] = ['between', $condistion['created_at_start'], $condistion['created_at_end']];
    } elseif (isset($condistion['created_at_start'])) {

      $data['created_at'] = ['egt', $condistion['created_at_start']];
    } elseif (isset($condistion['created_at_end'])) {

      $data['created_at'] = ['elt', $condistion['created_at_end']];
    }
    if (isset($condistion['ability_name'])) {
      $data['ability_name'] = ['like', '%' . $condistion['ability_name'] . '%'];
    }
    if (isset($condistion['ability_value'])) {
      $data['ability_value'] = ['like', '%' . $condistion['ability_value'] . '%'];
    }
    return $data;
  }

  function getlist($condistion, $lang = 'en') {

    $where = $this->getcondition($condistion, $lang);
    try {

      return $this->field('id,cat_no,ability_name,ability_value')
                      ->where($where)->order('sort_order desc')->select();
    } catch (Exception $ex) {
      Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
      Log::write($ex->getMessage());
      return [];
    }
  }

  function getlistbycat_nos($cat_nos, $lang = 'en') {
    $condistion['cat_nos'] = $cat_nos;
    $where = $this->getcondition($condistion, $lang);
    try {

      $rows = $this->field('id,cat_no,ability_name,ability_value')
                      ->where($where)->order(' sort_order desc')->select();

      if ($rows) {
        $data = [];
        foreach ($rows as $key => $val) {
          $data[$val['cat_no']][] = $val;
        }
        return $data;
      } else {

        return [];
      }
    } catch (Exception $ex) {
      Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
      Log::write($ex->getMessage());
      return [];
    }
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
   * 获取列表
   * @param  string $code 编码
   * @param  int $id id
   * @param  string $lang 语言
   * @return mix
   * @author zyg
   */
  public function info($cat_no = '', $lang = 'en') {
    if ($cat_no) {
      $where['cat_no'] = $cat_no;
    } else {
      return [];
    }
    if ($lang) {
      $where['lang'] = $lang;
    }
    return $this->where($where)
                    ->find();
  }

  public function getMaxid() {
    $row = $this->field('max(id) as maxid')->find();
    return intval($row['maxid']);
  }

  /**
   * 新增数据
   * @param  mix $createcondition 新增条件
   * @return bool
   * @author zyg
   */
  public function create_data($createcondition = [], $username = '') {

    $condition = $this->create($createcondition);
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $username;
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

  /**
   * 更新数据
   * @param  mix $upcondition 更新条件
   * @return mix
   * @author zyg
   */
  public function update_data($upcondition = [], $username = '') {
    $data = $this->getUpdateCondition($upcondition, $username);

    if (isset($upcondition['cat_no']) && $upcondition['cat_no']) {
      $where['cat_no'] = $upcondition['cat_no'];
    } else {
      return false;
    }

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
   * 删除数据
   * @param  string $cat_no
   * @param  string $lang 语言
   * @return bool
   * @author zyg
   */
  public function delete_data($cat_no = '', $lang = '') {

    if (!$cat_no) {
      return false;
    }
    if (is_array($cat_no)) {
      $where['cat_no'] = ['in', $cat_no];
    } else {
      $where['cat_no'] = $cat_no;
    }
    $this->startTrans();
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_DELETED]);
    if ($flag) {
      $this->commit();
      return $flag;
    } else {
      $this->rollback();
      return false;
    }
  }

}
