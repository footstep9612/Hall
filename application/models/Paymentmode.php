<?php

/**
 * 支付方式
 * User: linkai
 * Date: 2017/6/30
 * Time: 21:33
 */
class PaymentmodeModel extends PublicModel {

  protected $dbName = 'erui_dict'; //数据库名称
  protected $tableName = 'payment_mode'; //数据表表名

  public function __construct() {
    parent::__construct();
  }

  /**
   * 获取支付方式
   * @param string $lang
   * @return array|mixed
   */
  public function getPaymentmode($lang = '') {
    $condition = array();
    if (!empty($lang)) {
      $condition['lang'] = $lang;
    }

    if (redisHashExist('Paymentmode', md5(json_encode($condition)))) {
      return json_decode(redisHashGet('Paymentmode', md5(json_encode($condition))), true);
    }
    try {
      $field = 'lang,bn,name';
      $result = $this->field($field)->where($condition)->order('bn')->select();
      if ($result) {
        redisHashSet('Paymentmode', md5(json_encode($condition)), json_encode($result));
        return $result;
      }
    } catch (Exception $e) {
      return array();
    }
  }

  /*
   * 条件
   */

  function getCondition($condition) {
    $where = [];
    if (isset($condition['id']) && $condition['id']) {
      $where['id'] = $condition['id'];
    }
    if (isset($condition['lang']) && $condition['lang']) {
      $where['lang'] = $condition['lang'];
    }
    if (isset($condition['bn']) && $condition['bn']) {
      $where['bn'] = $condition['bn'];
    }
    if (isset($condition['name']) && $condition['name']) {
      $where['name'] = ['like', '%' . $condition['name'] . '%'];
    }

    return $where;
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
   * 配送时效列表
   * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
   * @param string $to_country 目的国
   * @param string $from_country 起始国
   * @param string $warehouse 起始仓库
   * @return array
   */
  public function getListbycondition($condition = '') {
    $where = $this->getCondition($condition);
    try {
      $field = 'id,bn,name,lang';

      $pagesize = 10;
      $current_no = 1;
      if (isset($condition['current_no'])) {
        $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
      }
      if (isset($condition['pagesize'])) {
        $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
      }
      $from = ($current_no - 1) * $pagesize;
      $result = $this->field($field)
              ->limit($from, $pagesize)
              ->where($where)
              ->select();
      return $result;
    } catch (Exception $e) {
      return array();
    }
  }

}
