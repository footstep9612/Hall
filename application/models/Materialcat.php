<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class MaterialcatModel extends PublicModel {

  //put your code here
  protected $dbName = 'erui_goods'; //数据库名称
  protected $tableName = 'material_cat'; //数据表表名

  const STATUS_DRAFT = 'DRAFT'; //草稿
  const STATUS_APPROVING = 'APPROVING'; //审核；
  const STATUS_VALID = 'VALID'; //生效；
  const STATUS_DELETED = 'DELETED'; //DELETED-删除

  public function __construct() {
    parent::__construct();
  }

  /**
   * 根据条件获取查询条件
   * @param mix $condition
   * @return mix
   * @author zyg
   *
   */
  protected function getcondition($condition = []) {
    $where = [];
    if (isset($condition['id'])) {
      $where['id'] = $condition['id'];
    }
    if (isset($condition['cat_no'])) {
      $where['cat_no'] = $condition['cat_no'];
    }

    if (isset($condition['cat_no3'])) {
      $where['level_no'] = 3;
      $where['cat_no'] = $condition['cat_no3'];
    } elseif (isset($condition['cat_no2'])) {
      $where['level_no'] = 2;
      $where['parent_cat_no'] = $condition['cat_no2'];
    } elseif (isset($condition['cat_no1'])) {
      $where['level_no'] = 1;
      $where['parent_cat_no'] = $condition['cat_no1'];
    } elseif (isset($condition['level_no']) && intval($condition['level_no']) <= 3) {
      $where['level_no'] = intval($condition['level_no']);
    } else {
      $where['level_no'] = 1;
    }
    if (isset($condition['parent_cat_no'])) {
      $where['parent_cat_no'] = $condition['parent_cat_no'];
    }

    if (isset($condition['mobile'])) {
      $where['mobile'] = ['LIKE', '%' . $condition['mobile'] . '%'];
    }
    if (isset($condition['lang'])) {
      $where['lang'] = $condition['lang'];
    }
    if (isset($condition['name'])) {
      $where['name'] = ['like', '%' . $condition['name'] . '%'];
    }

    if (isset($condition['sort_order'])) {
      $where['sort_order'] = $condition['sort_order'];
    }if (isset($condition['created_at'])) {
      $where['created_at'] = $condition['created_at'];
    }
    if (isset($condition['created_by'])) {
      $where['created_by'] = $condition['created_by'];
    }
    if (isset($condition['status'])) {
      switch ($condition['status']) {

        case self::STATUS_DELETED:
          $where['status'] = $condition['status'];
          break;
        case self::STATUS_DRAFT:
          $where['status'] = $condition['status'];
          break;
        case self::STATUS_APPROVING:
          $where['status'] = $condition['status'];
          break;
        case self::STATUS_VALID:
          $where['status'] = $condition['status'];
          break;
        default : $where['status'] = self::STATUS_VALID;
      }
    } else {
      $where['status'] = self::STATUS_VALID;
    }

    return $where;
  }

  /**
   * 获取数据条数
   * @param mix $condition
   * @return mix
   * @author zyg
   */
  public function getcount($condition = []) {
    $where = $this->getcondition($condition);
    try {
      return $this->where($where)
                      //  ->field('id,user_id,name,email,mobile,status')
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
  public function getlist($condition = []) {
    $where = $this->getcondition($condition);
    if (isset($condition['page']) && isset($condition['countPerPage'])) {
      return $this->where($where)
                      ->limit($condition['page'] . ',' . $condition['countPerPage'])
                      ->order('sort_order DESC')
                      ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by')
                      ->select();
    } else {
      return $this->where($where)
                      ->order('sort_order DESC')
                      ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by')
                      ->select();
    }
  }

  public function get_list($cat_no = '', $lang = 'en') {
    if ($cat_no) {
      $condition['parent_cat_no'] = $cat_no;
    } else {
      $condition['parent_cat_no'] = 0;
    }
    $condition['status'] = self::STATUS_VALID;
    $condition['lang'] = $lang;


    return $this->where($condition)
                    ->field('id,cat_no,lang,name,status,sort_order')
                    ->order('sort_order DESC')
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
  public function info($cat_no = '', $lang = 'en') {
    $where['cat_no'] = $cat_no;
    $where['lang'] = $lang;
    return $this->where($where)
                    ->field('id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by')
                    ->find();
  }

  /*
   * 根据物料分类编码搜索物料分类 和上级分类信息 顶级分类信息
   * @param mix $cat_nos // 物料分类编码数组3f
   * @param string $lang // 语言 zh en ru es
   * @return mix  物料分类及上级和顶级信息
   */

  public function getinfo($cat_no, $lang = 'en') {
    try {
      if ($cat_no) {
        $cat3 = $this->field('id,cat_no,name,parent_cat_no')
                ->where(['cat_no' => $cat_no, 'lang' => $lang, 'status' => 'VALID'])
                ->find();
        if ($cat3) {
          $cat2 = $this->field('id,cat_no,name,parent_cat_no')
                  ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                  ->find();
        } else {
          return [];
        }
        if ($cat2) {
          $cat1 = $this->field('id,cat_no,name,parent_cat_no')
                  ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                  ->find();
        } else {
          return ['cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
        }
        if ($cat1) {
          return ['cat_no1' => $cat1['cat_no'], 'cat_name1' => $cat1['name'], 'cat_no2' => $cat2['cat_no'], 'cat_name2' => $cat2['name'], 'cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
        } else {
          return ['cat_no2' => $cat2['cat_no'], 'cat_name2' => $cat2['name'], 'cat_no3' => $cat3['cat_no'], 'cat_name3' => $cat3['name']];
        }
      } else {
        return [];
      }
    } catch (Exception $ex) {
      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
      LOG::write($ex->getMessage(), LOG::ERR);
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
    $where['cat_no'] = $cat_no;
    if ($lang) {
      $where['lang'] = $lang;
    }
    $info = $this->info($cat_no, 'en');
    if (!$info) {
      $info = $this->info($cat_no, 'zh');
    }
    if (!$info) {
      $info = $this->info($cat_no, 'es');
    }
    if (!$info) {
      $info = $this->info($cat_no, 'ru');
    }
    $this->startTrans();
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_DELETED]);
    if ($flag && $info['level_no'] == 1) {
      $flag = $this->where(['parent_cat_no' => $cat_no])
              ->save(['status' => self::STATUS_DELETED]);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if ($flag && $info['level_no'] == 2) {
      $flag = $this->where(['parent_cat_no' => $cat_no])
              ->save(['status' => self::STATUS_DELETED]);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if ($flag && $cat_no && $info['level_no'] == 3 && !$lang) {
      $es_product_model = new EsproductModel();
      $es_product_model->Updatemeterialcatno($cat_no, null, 'en');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'zh');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'es');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'ru');
    } elseif ($flag && $cat_no && $info['level_no'] == 3 && $lang) {
      $es_product_model = new EsproductModel();
      $es_product_model->Updatemeterialcatno($cat_no, null, $lang);
    } else {
      $this->rollback();
      return false;
    }
    $this->commit();
    return $flag;
  }

  /**
   * 交换分类排序
   * @param string $cat_no 交换的分类编码
   * @return string $chang_cat_no 被交换的分类编码
   * @author zyg
   */
  public function changecat_sort_order($cat_no, $chang_cat_no) {

    try {
      $this->startTrans();
      $sort_order = $this->field('sort_order')->where(['cat_no' => $cat_no])->find();
      $sort_order1 = $this->field('sort_order')->where(['cat_no' => $chang_cat_no])->find();
      $flag = $this->where(['cat_no' => $cat_no])->save(['sort_order' => $sort_order1['sort_order']]);
      if ($flag) {
        $flag1 = $this->where(['cat_no' => $chang_cat_no])->save(['sort_order'
            => $sort_order['sort_order']]);
        if ($flag1) {
          $this->commit();
          return true;
        } else {
          $this->rollback();
          return false;
        }
      } else {
        $this->rollback();
        return false;
      }
      return $flag;
    } catch (Exception $ex) {
      $this->rollback();
      LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
      LOG::write($ex->getMessage(), LOG::ERR);
      return false;
    }
  }

  /**
   * 通过审核
   * @param  string $cat_no
   * @return bool
   * @author zyg
   */
  public function approving($cat_no = '', $lang = '') {

    $where['cat_no'] = $cat_no;
    if ($lang) {
      $condition['lang'] = $lang;
    }
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_VALID]);
    if ($flag && $cat_no && !$lang) {
      $es_product_model = new EsproductModel();
      $es_product_model->Updatemeterialcatno($cat_no, null, 'en');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'zh');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'es');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'ru');
    } elseif ($flag && $cat_no && $lang) {
      $es_product_model->Updatemeterialcatno($cat_no, null, $lang);
    }
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
    if (!$data) {
      return false;
    }
    if (isset($upcondition['cat_no']) && $upcondition['cat_no']) {
      $where['cat_no'] = $upcondition['cat_no'];
    } else {
      return false;
    }
    if (!isset($data['parent_cat_no']) && $data['parent_cat_no'] != $info['parent_cat_no']) {
      $cat_no = $this->getCatNo($data['parent_cat_no'], $data['level_no']);
      if (!$cat_no) {
        return false;
      } else {
        $data['cat_no'] = $cat_no;
      }
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
      $data['name'] = $upcondition['zh']['name'];
      $where['lang'] = $data['lang'];
      $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($upcondition['ru'])) {
      $data['lang'] = 'ru';
      $data['name'] = $upcondition['zh']['name'];
      $where['lang'] = $data['lang'];
      $exist_flag = $this->Exist($where);
      $flag = $exist_flag ? $this->where($where)->save($data) : $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if ($upcondition['level_no'] == 2 && $where['cat_no'] != $data['cat_no']) {

      $childs = $this->get_list($cat_no);
      foreach ($childs as $val) {
        $child_cat_no = $this->getCatNo($data['cat_no'], 3);
        $flag = $this->where(['cat_no' => $val['cat_no']])
                ->save(['cat_no' => $child_cat_no, 'parent_cat_no' => $data['cat_no']]);
        if (!$flag) {
          $this->rollback();
          return false;
        }
        $flag = $this->updateothercat($val['cat_no'], $child_cat_no);
        if (!$flag) {
          $this->rollback();
          return false;
        }
      }
    } elseif ($upcondition['level_no'] == 3 && $where['cat_no'] != $data['cat_no']) {
      $flag = $this->updateothercat($where['cat_no'], $data['cat_no']);
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
  public function getUpdateCondition($upcondition = [], $username = '') {
    $data = [];
    $where = [];
    $info = [];
    if ($upcondition['cat_no']) {
      $where['cat_no'] = $upcondition['cat_no'];
      $info = $this->getinfo($where['cat_no']);
    } else {
      return false;
    }
    if (isset($upcondition['parent_cat_no'])) {
      $data['parent_cat_no'] = $upcondition['parent_cat_no'];
    }
    if (isset($upcondition['level_no']) && $info['level_no'] != $upcondition['level_no']) {
      return false;
    }
    if (isset($upcondition['level_no']) && in_array($upcondition['level_no'], [1, 2, 3]))
      $data['level_no'] = $upcondition['level_no'];


    if ($upcondition['level_no'] == 1) {
      $data['parent_cat_no'] = 0;
    } elseif (isset($upcondition['parent_cat_no'])) {
      $data['parent_cat_no'] = $upcondition['parent_cat_no'];
    }

    switch ($upcondition['status']) {

      case self::STATUS_DELETED:
        $data['status'] = $upcondition['status'];
        break;
      case self::STATUS_DRAFT:
        $data['status'] = $upcondition['status'];
        break;
      case self::STATUS_APPROVING:
        $data['status'] = $upcondition['status'];
        break;
      case self::STATUS_VALID:
        $data['status'] = $upcondition['status'];
        break;
    }
    if ($upcondition['sort_order']) {
      $data['sort_order'] = $upcondition['sort_order'];
    }
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $username;
  }

  public function updateothercat($old_cat_no, $new_cat_no) {
    $flag = $this->table($this->dbName . '.t_product')
            ->where(['meterial_cat_no' => $old_cat_no])
            ->save(['meterial_cat_no' => $new_cat_no]);
    if (!$flag) {
      $this->rollback();
      return false;
    }
    $flag = $this->table($this->dbName . '.t_material_cat_product')
            ->where(['cat_no' => $old_cat_no])
            ->save(['cat_no' => $new_cat_no]);
    if (!$flag) {
      $this->rollback();
      return false;
    }
    $flag = $this->table($this->dbName . '.t_show_material_cat')
            ->where(['material_cat_no' => $old_cat_no])
            ->save(['material_cat_no' => $new_cat_no]);
    if (!$flag) {
      $this->rollback();
      return false;
    }
    $es_product_model = new EsproductModel();
    $es_product_model->Updatemeterialcatno($old_cat_no, null, 'en', $new_cat_no);
    $es_product_model->Updatemeterialcatno($old_cat_no, null, 'zh', $new_cat_no);
    $es_product_model->Updatemeterialcatno($old_cat_no, null, 'es', $new_cat_no);
    $es_product_model->Updatemeterialcatno($old_cat_no, null, 'ru', $new_cat_no);
    return true;
  }

  public function getCatNo($parent_cat_no = '', $level_no = 1) {

    if ($level_no < 1) {
      $level_no = 1;
    } elseif ($level_no >= 3) {

      $level_no = 3;
    }
    if (empty($parent_cat_no) && $level_no == 1) {
      $re = $this->field('max(cat_no) as max_cat_no')->where(['level_no' => 1])->find();
      if ($re) {
        return printf('%02d', intval($re['max_cat_no']) + 1);
      } else {

        return '01';
      }
    } elseif (empty($parent_cat_no)) {
      return false;
    } else {
      $re = $this->field('max(cat_no) as max_cat_no')->where(['parent_cat_no' => $parent_cat_no])->find();
      if ($re) {
        return printf('%0' . ($level_no * 2) . 'd', intval($re['max_cat_no']) + 1);
      } else {
        return printf('%0' . ($level_no * 2) . 'd', intval($parent_cat_no) * 100 + 1);
      }
    }
  }

  /**
   * 新增数据
   * @param  mix $createcondition 新增条件
   * @return bool
   * @author zyg
   */
  public function create_data($createcondition = [], $username = '') {


    $condition = $this->create($createcondition);

    if (isset($condition['cat_no'])) {
      $data['cat_no'] = $condition['cat_no'];
    }
    if (isset($condition['parent_cat_no']) && $condition['level_no'] == 1) {
      $data['parent_cat_no'] = 0;
    } elseif (isset($condition['parent_cat_no'])) {
      $data['parent_cat_no'] = $condition['parent_cat_no'];
    }
    if (isset($condition['level_no']) && in_array($condition['level_no'], [1, 2, 3])) {
      $data['level_no'] = $condition['level_no'];
    }

    if (!isset($data['cat_no'])) {
      $cat_no = $this->getCatNo($data['parent_cat_no'], $data['level_no']);
      if (!$cat_no) {
        return false;
      } else {
        $data['cat_no'] = $cat_no;
      }
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
    if (isset($condition['en'])) {
      $data['lang'] = 'en';
      $data['name'] = $condition['en']['name'];
      $flag = $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($condition['zh'])) {
      $data['lang'] = 'zh';
      $data['name'] = $condition['zh']['name'];
      $flag = $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($condition['es'])) {
      $data['lang'] = 'es';
      $data['name'] = $condition['zh']['name'];
      $flag = $this->add($data);
      if (!$flag) {
        $this->rollback();
        return false;
      }
    }
    if (isset($condition['ru'])) {
      $data['lang'] = 'ru';
      $data['name'] = $condition['zh']['name'];

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
   * 根据cat_no获取所属分类name
   * @param  string $code 编码
   * klp
   */
  protected $data = array();

  public function getNameByCat($code = '') {
    if ($code == '')
      return '';
    $condition = array(
        'cat_no' => $code,
        'status' => self::STATUS_VALID
    );
    $resultTr = $this->field('name,parent_cat_no')->where($condition)->select();

    $this->data[] = $resultTr[0]['name'];
    if ($resultTr) {
      self::getNameByCat($resultTr[0]['parent_cat_no']);
    }
    $nameAll = $this->data[2] . '/' . $this->data[1] . '/' . $this->data[0];
    return $nameAll;
  }

  /**
   * 根据编码获取分类信息
   * @author link 2016-06-15
   * @param string $catNo 分类编码
   * @param string $lang 语言
   * @return array
   */
  public function getMeterialCatByNo($catNo = '', $lang = '') {
    if ($catNo == '' || $lang == '')
      return array();

    //读取缓存
    if (redisHashExist('MeterialCat', $catNo . '_' . $lang)) {
      return (array) json_decode(redisHashGet('MeterialCat', $catNo . '_' . $lang));
    }

    try {
      $field = 'lang,cat_no,parent_cat_no,level_no,name,description,sort_order';
      $condition = array(
          'cat_no' => $catNo,
          'status' => self::STATUS_VALID,
          'lang' => $lang
      );
      $result = $this->field($field)->where($condition)
                      ->order('sort_order DESC')->find();
      if ($result) {
        redisHashSet('MeterialCat', $catNo . '_' . $lang, json_encode($result));
        return $result;
      }
    } catch (Exception $e) {
      return array();
    }
    return array();
  }

  /**
   * 根据分类名称获取分类编码
   * 模糊查询
   * @author link 2017-06-26
   * @param string $cat_name 分类名称
   * @return array
   */
  public function getCatNoByName($cat_name = '') {
    if (empty($cat_name))
      return array();

    if (redisHashExist('Material', md5($cat_name))) {
      return (array) json_decode(redisHashGet('Material', md5($cat_name)));
    }
    try {
      $result = $this->field('cat_no')->where(array('name' => array('like', $cat_name)))->order('sort_order DESC')->select();
      if ($result)
        redisHashSet('Material', md5($cat_name), json_encode($result));

      return $result ? $result : array();
    } catch (Exception $e) {
      return array();
    }
  }

}
