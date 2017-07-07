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
    //id,cat_no,parent_cat_no,level_no,lang,name,status,sort_order,created_at,created_by
    if (isset($condition['cat_no'])) {
      $where['cat_no'] = $condition['cat_no'];
    }

    if (isset($condition['cat_no3'])) {
      $where['level_no'] = 2;
      $where['cat_no'] = $condition['cat_no3'];
    } elseif (isset($condition['cat_no2'])) {
      $where['level_no'] = 2;
      $where['parent_cat_no'] = $condition['cat_no2'];
    } elseif (isset($condition['cat_no1'])) {
      $where['level_no'] = 1;
      $where['parent_cat_no'] = $condition['cat_no1'];
    } elseif (isset($condition['level_no']) && intval($condition['level_no']) <= 2) {
      $where['level_no'] = intval($condition['level_no']);
    } else {
      $where['level_no'] = 0;
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
      $count = $this->getcount($condition);
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

    $where = $this->getcondition($condition);

    return $this->where($where)
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
  public function info($cat_no = '') {
    $where['cat_no'] = $cat_no;
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
        $cat2 = $this->field('id,cat_no,name,parent_cat_no')
                ->where(['cat_no' => $cat3['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                ->find();
        $cat1 = $this->field('id,cat_no,name,parent_cat_no')
                ->where(['cat_no' => $cat2['parent_cat_no'], 'lang' => $lang, 'status' => 'VALID'])
                ->find();
        return [$cat1['cat_no'], $cat1['name'], $cat2['cat_no'], $cat2['name'], $cat3['cat_no'], $cat3['name']];
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
   * 判断用户是否存在
   * @param  string $name 用户名
   * @param  string$enc_password 密码
   * @param  string $lang 语言
   * @return mix
   * @author zyg
   */
  public function Exist($name, $type = 'name') {
    switch (strtolower($type)) {

      case 'name':
        $where['name'] = $name;
        break;
      default :
        return false;
        break;
    }
    //$where['enc_password'] = md5($enc_password);
    $row = $this->where($where)
            ->field('id')
            ->find();

    var_dump();
    return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
  }

  /**
   * 删除数据
   * @param  int $id id
   * @return bool
   * @author zyg
   */
  public function delete_data($cat_no = '') {

    $where['cat_no'] = $cat_no;
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_DELETED]);
    if ($flag && $cat_no) {
      $es_product_model = new EsproductModel();
      $es_product_model->Updatemeterialcatno($cat_no, null, 'en');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'zh');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'es');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'ru');
    }
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
      $flag = $this->where(['cat_no' => $cat_no])->save(['sort_order' => $sort_order1]);
      if ($flag) {
        $flag1 = $this->where(['cat_no' => $chang_cat_no])->save(['sort_order' => $sort_order]);

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
   * @param  int $id id
   * @return bool
   * @author zyg
   */
  public function approving($cat_no = '') {

    $where['cat_no'] = $cat_no;
    $flag = $this->where($where)
            ->save(['status' => self::STATUS_VALID]);
    if ($flag && $cat_no) {
      $es_product_model = new EsproductModel();
      $es_product_model->Updatemeterialcatno($cat_no, null, 'en');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'zh');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'es');
      $es_product_model->Updatemeterialcatno($cat_no, null, 'ru');
    }
    return $flag;
  }

  /**
   * 更新数据
   * @param  mix $upcondition 更新条件
   * @return bool
   * @author zyg
   */
  public function update_data($upcondition = [], $username = '') {
    $data = [];
    $where = [];
    if ($condition['id']) {
      $where['id'] = $condition['id'];
    }
    if ($condition['cat_no']) {
      $data['cat_no'] = $condition['cat_no'];
    }
    if ($condition['parent_cat_no']) {
      $data['parent_cat_no'] = $condition['parent_cat_no'];
    }
    if ($condition['level_no']) {
      $data['level_no'] = $condition['level_no'];
    }
    if ($condition['lang']) {
      $data['lang'] = $condition['lang'];
    }
    if ($condition['name']) {
      $data['name'] = $condition['name'];
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
    }
    if ($condition['sort_order']) {
      $data['sort_order'] = $condition['sort_order'];
    }
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $username;
    $flag = $this->where($where)->save($data);
    if ($flag && $data['cat_no'] && $data['lang']) {
      $es_product_model = new EsproductModel();
      $es_product_model->Updatemeterialcatno($data['cat_no'], null, $data['lang']);
    }
    return $flag;
  }

  /**
   * 新增数据
   * @param  mix $createcondition 新增条件
   * @return bool
   * @author zyg
   */
  public function create_data($createcondition = [], $username = '') {


    $data = $this->create($createcondition);

    if ($condition['cat_no']) {
      $data['cat_no'] = $condition['cat_no'];
    }
    if ($condition['parent_cat_no']) {
      $data['parent_cat_no'] = $condition['parent_cat_no'];
    }
    if ($condition['level_no']) {
      $data['level_no'] = $condition['level_no'];
    }
    if ($condition['lang']) {
      $data['lang'] = $condition['lang'];
    }
    if ($condition['name']) {
      $data['name'] = $condition['name'];
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
    }
    if ($condition['sort_order']) {
      $data['sort_order'] = $condition['sort_order'];
    }


    return $this->add($data);
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
