<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:54
 */
class LogiPeriodModel extends Model {

  protected $dbName = 'erui_dict'; //数据库名称
  protected $tableName = 't_logi_period';

  const STATUS_VALID = 'VALID';

  function getCondition($condition) {
    $where = [];
    if (isset($condition['id']) && $condition['id']) {
      $where['id'] = $condition['id'];
    }
    if (isset($condition['lang']) && $condition['lang']) {
      $where['lang'] = $condition['lang'];
    }
    if (isset($condition['logi_no']) && $condition['logi_no']) {
      $where['logi_no'] = $condition['logi_no'];
    }
    if (isset($condition['trade_terms']) && $condition['trade_terms']) {
      $where['trade_terms'] = $condition['trade_terms'];
    }
    if (isset($condition['trans_mode']) && $condition['trans_mode']) {
      $where['trans_mode'] = $condition['trans_mode'];
    }
    if (isset($condition['warehouse']) && $condition['warehouse']) {
      $where['warehouse'] = $condition['warehouse'];
    }
    if (isset($condition['from_country']) && $condition['from_country']) {
      $where['from_country'] = $condition['from_country'];
    }
    if (isset($condition['from_port']) && $condition['from_port']) {
      $where['from_port'] = $condition['from_port'];
    }
    if (isset($condition['to_country']) && $condition['to_country']) {
      $where['to_country'] = $condition['to_country'];
    }
    if (isset($condition['clearance_loc']) && $condition['clearance_loc']) {
      $where['clearance_loc'] = $condition['clearance_loc'];
    }
    if (isset($condition['to_port']) && $condition['to_port']) {
      $where['to_port'] = $condition['to_port'];
    }
     if (isset($condition['status']) && $condition['status']) {
      $where['status'] = $condition['status'];
    }
     if (isset($condition['created_by']) && $condition['created_by']) {
      $where['created_by'] = $condition['created_by'];
    }
     if (isset($condition['created_at']) && $condition['created_at']) {
      $where['created_at'] = $condition['created_at'];
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
      $field = 'id,lang,logi_no,trade_terms,trans_mode,warehouse,from_country,'
              . 'from_port,to_country,clearance_loc,to_port,packing_period_min,'
              . 'packing_period_max,collecting_period_min,collecting_period_max,'
              . 'declare_period_min,declare_period_max,loading_period_min,'
              . 'loading_period_max,int_trans_period_min,int_trans_period_max,'
              . 'logi_notes,period_min,period_max,description,status,created_by,created_at';

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

  /**
   * 配送时效列表
   * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
   * @param string $to_country 目的国
   * @param string $from_country 起始国
   * @param string $warehouse 起始仓库
   * @return array
   */
  public function getList($lang = '', $to_country = '', $from_country = '', $warehouse = '') {
    if (empty($lang) || empty($to_country)) {
      return array();
    }

    $countryModel = new CountryModel();
    $cityModel = new CityModel();
    //库中中国状态暂为无效
    $from_country = $from_country ? $from_country : $countryModel->getCountryByBn('China', $lang);
    //city库中暂无东营,暂时写死以为效果
    $warehouse = $warehouse ? $warehouse : $cityModel->getCityByBn('Dongying', $lang);

    $condition = array(
        'status' => self::STATUS_VALID,
        'lang' => $lang,
        'to_country' => $to_country,
        'from_country' => $from_country,
        'warehouse' => $warehouse
    );
    if (redisHashExist('LogiPeriod', md5(json_encode($condition)))) {
      return json_decode(redisHashGet('LogiPeriod', md5(json_encode($condition))), true);
    }
    try {
      $field = 'id,lang,logi_no,trade_terms,trans_mode,warehouse,from_country,'
              . 'from_port,to_country,clearance_loc,to_port,packing_period_min,'
              . 'packing_period_max,collecting_period_min,collecting_period_max,'
              . 'declare_period_min,declare_period_max,loading_period_min,'
              . 'loading_period_max,int_trans_period_min,int_trans_period_max,'
              . 'logi_notes,period_min,period_max,description';
      $result = $this->field($field)->where($condition)->select();
      $data = array();
      if ($result) {
        foreach ($result as $item) {
          $data[$item['trade_terms']][] = $item;
        }
        redisHashSet('LogiPeriod', md5(json_encode($condition)), json_encode($data));
      }
      return $data;
    } catch (Exception $e) {
      return array();
    }
  }

  /**
   * 根据条件获取物流时效信息
   */
  public function getInfo($field, $where) {
    if (empty($field) || empty($where))
      return array();

    if (redisHashExist('LogiPeriod', md5(json_encode($where)))) {
      return json_decode(redisHashGet('LogiPeriod', md5(json_encode($where))), true);
    }
    try {
      $result = $this->field($field)->where($where)->select();
      $data = array();
      if ($result) {
        $data = $result;
        redisHashSet('LogiPeriod', md5(json_encode($where)), json_encode($data));
      }
      return $data;
    } catch (Exception $e) {
      return array();
    }
  }

}
