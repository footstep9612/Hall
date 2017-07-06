<?php

/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel {

  //状态
  const STATUS_VALID = 'VALID'; //有效
  const STATUS_TEST = 'TEST'; //测试；
  const STATUS_INVALID = 'INVALID';  //无效
  const STATUS_DELETED = 'DELETED'; //DELETED-删除
  const STATUS_CHECKING = 'CHECKING'; //审核中；

//  const STATUS_PENDING = 'PENDING'; //待提交；
//  const STATUS_CHECKING = 'CHECKING'; //待审核；
//  const STATUS_CHECKING = 'CHECKING'; //通过；
//  const STATUS_CHECKING = 'CHECKING'; //不通过；

  public function __construct() {
    //动态读取配置中的数据库配置   便于后期维护
    $config_obj = Yaf_Registry::get("config");
    $config_db = $config_obj->database->config->goods->toArray();
    $this->dbName = $config_db['name'];
    $this->tablePrefix = $config_db['tablePrefix'];
    $this->tableName = 'goods';

    parent::__construct();
  }

  /**
   * 商品基本信息    -- 公共方法
   * @author link 2017-06-26
   * @param array $condition
   * @return array
   */
  public function getInfoBase($condition = []) {
    if (!isset($condition['sku']))
      return array();

    $where = array(
        'sku' => trim($condition['sku']),
    );
    if (isset($condition['lang'])) {
      $where['lang'] = strtolower($condition['lang']);
    }
    if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
      $where['status'] = strtoupper($condition['status']);
    }
    if(redisHashExist('Sku',md5(json_encode($where)))){
      return json_decode(redisHashGet('Sku',md5(json_encode($where))),true);
    }

    $field = 'sku,spu,lang,name,show_name,qrcode,model,description,status';
    try {
      $result = $this->field($field)->where($where)->select();
      $data = array();
      if ($result) {
        foreach ($result as $item) {
          //获取供应商与品牌
          $product = new ProductModel();
          $productInfo = $product->getInfo($item['spu'], $item['lang'], $product::STATUS_VALID);
          $item['brand'] = $item['spec'] = $item['supplier_id'] = $item['supplier_name'] = $item['meterial_cat_no'] = '';
          if ($productInfo) {
            if (isset($productInfo[$item['lang']])) {
              $productInfo = (array) $productInfo[$item['lang']];
              $item['brand'] = $productInfo['brand'];
              $item['supplier_id'] = $productInfo['supplier_id'];
              $item['supplier_name'] = $productInfo['supplier_name'];
              $item['meterial_cat_no'] = $productInfo['meterial_cat_no'];
            }
          }

          //获取商品规格
          $gattr = new GoodsAttrModel();
          $spec = $gattr->getSpecBySku($item['sku'], $item['lang']);
          $spec_str = '';
          if ($spec) {
            foreach ($spec as $r) {
              $spec_str .= $r['attr_name'] . ':' . $r['attr_value'] . $r['value_unit'] . ';';
            }
          }
          $item['spec'] = $spec_str;

          //按语言分组
          $data[$item['lang']] = $item;
        }
        redisHashSet('Sku',md5(json_encode($where)),json_encode($data));
      }
      return $data;
    } catch (Exception $e) {
      return array();
    }
  }

  /**
   * pc-sku商品详情
   * klp
   */
  public function getGoodsInfo($sku, $lang = '') {
    $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
    $field = 'id,sku,lang,spu,qrcode,name,show_name,model,description';
    $condition = array(
        'sku' => $sku,
        'status' => self::STATUS_VALID
    );

    try {
      //缓存数据redis
      $key_redis = md5(json_encode($condition));
      if (redisExist($key_redis)) {
        $result = redisGet($key_redis);
        return $result ? json_decode($result) : array();
      } else {
        $result = $this->field($field)->where($condition)->select();
        if ($result) {
          $data = array(
              'lang' => $lang
          );
          //语言分组
          foreach ($result as $k => $v) {
            $data[$v['lang']] = $v;
          }
          //查询商品附件(未分语言)
          $skuAchModel = new GoodsAchModel();
          $attach = $skuAchModel->getInfoByAch($sku);
          $data['attachs'] = $attach ? $attach : array();

          redisSet($key_redis, json_encode($data));
          return $data;
        } else {
          return array();
        }
      }
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * SKU基本信息
   */
  public function getInfo($sku, $lang = '') {
    $ProductModel = new ProductModel();
    $proTable = $ProductModel->getTableName();
    $thisTable = $this->getTableName();
    if ($lang != '') {
      $condition["$thisTable.lang"] = $lang;
      $condition["$proTable.lang"] = $lang;
    }
    $field = "$thisTable.id,$thisTable.lang,$thisTable.sku,$thisTable.spu,$thisTable.name,$thisTable.show_name,$thisTable.model,$proTable.brand,$proTable.meterial_cat_no,$proTable.supplier_name";
    $condition = array(
        "$thisTable.sku" => $sku,
        "$thisTable.status" => self::STATUS_VALID,
        "$proTable.status" => $ProductModel::STATUS_VALID
    );
    try {
      //缓存数据的判断读取
      $redis_key = md5(json_encode($condition));
      if (redisExist($redis_key)) {
        $result = redisGet($redis_key);
        return $result ? json_decode($result) : false;
      } else {
        $result = $this->field($field)->where($condition)->find();
        if ($result) {
          $data = array(
              'lang' => $lang
          );

          //查找所属分类
          $cat_no = $result[0]['meterial_cat_no'];
          $material = new MaterialcatModel();
          $nameAll = $material->getNameByCat($cat_no);
          //查找spu英文名称
          $spu = $result[0]['spu'];
          $nameEn = $ProductModel->getNameBySpu($spu, 'en');

          //语言分组
          foreach ($result as $k => $v) {
            $data[$v['lang']] = $v;
            $data[$v['lang']]['cat_name'] = $nameAll;
            $data[$v['lang']]['en_name'] = $nameEn[0]['name'];
          }
          //查询属性
          $skuAttrModel = new GoodsAttrModel();
          $attrs = $skuAttrModel->getAttrBySku($sku, $lang);
          $data['attrs'] = $attrs;
          $result['attrs'] = $attrs;
          redisSet($redis_key, $result);
          return $result;
        } else {
          return array();
        }
      }
    } catch (Exception $e) {
      return false;
    }
  }

  /**
   * 根据spu获取sku数
   * @param string $spu spu编码
   * @param string $lang 语言
   * @retrun int
   */
  public function getCountBySpus($spus = '', $lang = '') {
    $condition = array(
        'status' => array('neq', self::STATUS_DELETED)
    );
    if (!empty($spus)) {
      $condition['spu'] = ['in', $spus];
    }
    if ($lang != '') {
      $condition['lang'] = $lang;
    }


    try {
      //redis 操作
      $redis_key = md5(json_encode($condition));

      if (redisExist($redis_key)) {
        return json_decode(redisGet($redis_key), true);
      } else {
        $count = $this->field('count(id) as skunum,spu')->where($condition)
                        ->group('spu')->select();
        redisSet($redis_key, json_encode($count), 1800);
        return $count ? $count : [];
      }
    } catch (Exception $e) {
      print_r($e);
      return [];
    }
  }
  /**
   * 根据spu获取sku数   (这里不包括删除的)
   * @author link
   * @param string $spu spu编码
   * @param string $lang 语言
   * @retrun int
   */
  public function getCountBySpu($spu = '', $lang = '') {
    $condition = array(
        'status' => array('neq', self::STATUS_DELETED)
    );
    if ($spu != '') {
      $condition['spu'] = $spu;
    }
    if ($lang != '') {
      $condition['lang'] = $lang;
    }
    try {
      //redis 操作
      $redis_key = md5(json_encode($condition));
      if (redisExist($redis_key)) {
        return redisGet($redis_key);
      } else {
        $count = $this->where($condition)->count('id');
        redisSet($redis_key, $count);
        return $count ? $count : 0;
      }
    } catch (Exception $e) {
      return 0;
    }
  }

  /**
   * sku 列表 （admin）
   */
  public function getList($condition = []) {
    //取product表名
    $productModel = new ProductModel();
    $ptable = $productModel->getTableName();

    //获取当前表名
    $thistable = $this->getTableName();

    $field = "$thistable.pricing_flag,$ptable.meterial_cat_no,$ptable.source,$ptable.supplier_name,$ptable.brand,$ptable.name as spu_name,$thistable.lang,$thistable.id,$thistable.sku,$thistable.spu,$thistable.status,$thistable.name,$thistable.model,$thistable.created_by,$thistable.created_at";

    $where = array();
    $current_no = isset($condition['currentPage']) ? $condition['currentPage'] : 1;
    $pagesize = isset($condition['pagesize']) ? $condition['pagesize'] : 10;
    //spu 编码
    if (isset($condition['spu']) && !empty($condition['spu'])) {
      $where["$thistable.spu"] = $condition['spu'];
    }

    //审核状态
    if (isset($condition['status']) && !empty($condition['status'])) {
      $where["$thistable.status"] = $condition['status'];
    }

    //语言
    $lang = '';
    if (isset($condition['lang']) && !empty($condition['status'])) {
      $where["$thistable.lang"] = $lang = strtolower($condition['lang']);
      $where["$ptable.lang"] = strtolower($condition['lang']);
    }

    //型号
    if (isset($condition['model']) && !empty($condition['model'])) {
      $where["$thistable.model"] = $condition['model'];
    }

    //来源
    if (isset($condition['source']) && !empty($condition['source'])) {
      $where["$ptable.source"] = $condition['source'];
    }

    //按供应商
    if (isset($condition['supplier_name']) && !empty($condition['supplier_name'])) {
      $where["$ptable.supplier_name"] = array('like', $condition['supplier_name']);
    }
    //按品牌
    if (isset($condition['brand']) && !empty($condition['brand'])) {
      $where["$ptable.brand"] = $condition['brand'];
    }

    //按分类名称
    if (isset($condition['cat_name']) && !empty($condition['cat_name'])) {
      $material = new MaterialcatModel();
      $m_cats = $material->getCatNoByName($condition['cat_name']);
      if ($m_cats) {
        $mstr = '';
        foreach ($m_cats as $item) {
          $item = (array) $item;
          $mstr .= ',' . $item['cat_no'];
        }
        $mstr = strlen($mstr) > 1 ? substr($mstr, 1) : '';
        $where["$ptable.meterial_cat_no"] = array('in', $mstr);
      } else {
        $data = array(
            'lang' => $lang,
            'count' => 0,
            'current_no' => $current_no,
            'pagesize' => $pagesize,
            'data' => array(),
        );
        return $data;
      }
    }

    //是否已定价
    if (isset($condition['pricing_flag']) && !empty($condition['pricing_flag'])) {
      $where["$thistable.pricing_flag"] = $condition['pricing_flag'];
    }

    //sku_name
    if (isset($condition['name']) && !empty($condition['name'])) {
      $where["$thistable.name"] = array('like', $condition['name']);
    }

    //sku id  这里用sku编号
    if (isset($condition['sku']) && !empty($condition['sku'])) {
      $where["$thistable.sku"] = $condition['sku'];
    }

    try {
     // $count = $this->field("$thistable.id")->join($ptable . " On $thistable.spu = $ptable.spu AND $thistable.lang =$ptable.lang", 'LEFT')->where($where)->count();
      $result = $this->field($field)->join($ptable . " On $thistable.spu = $ptable.spu AND $thistable.lang =$ptable.lang", 'LEFT')->where($where)->page($current_no, $pagesize)->select();
      $data = array(
          'lang' => $lang,
          'count' => 0,
          'current_no' => $current_no,
          'pagesize' => $pagesize,
          'data' => array(),
      );
      if ($result) {
        //foreach($result as $k=> $item){
        // $result[$k]['cat_name']
        // }
        $data['count'] = count($result);//$count;
        $data['data'] = $result;
      }
      return $data;
    } catch (Exception $e) {
      return false;
    }
  }

  /**

   * 新增数据
   * @param  mix $createcondition 新增条件
   * @return bool
   * @author klp
   */
  public function create_data($createcondition) {
    $where = [];
    $data = $this->condition($createcondition);
    return $this->where($where)->save($data);
  }

  //公共部分处理
  public function condition($condition, $username = '') {
    if ($condition['id']) {
      $where['id'] = $condition['id'];
    }
    if ($condition['lang']) {
      $data['lang'] = $condition['lang'];
    }
    if ($condition['spu']) {
      $data['spu'] = $condition['spu'];
    }
    if ($condition['sku']) {
      $data['sku'] = $condition['sku'];
    }
    if ($condition['cat_no']) {
      $data['cat_no'] = $condition['cat_no'];
    }
    if ($condition['attr_value_type']) {
      $data['attr_value_type'] = $condition['attr_value_type'];
    }
    if ($condition['attr_group']) {
      $data['attr_group'] = $condition['attr_group'];
    }
    if ($condition['sort_order']) {
      $data['sort_order'] = $condition['sort_order'];
    }
    switch ($condition['status']) {
      case self::STATUS_DELETED:
        $data['status'] = $condition['status'];
        break;
      case self::STATUS_VALID:
        $data['status'] = $condition['status'];
        break;
      case self::STATUS_INVALID:
        $data['status'] = $condition['status'];
        break;
    }
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['created_by'] = $username;

    $attrs = array();
    if ($condition['goods_flag']) {
      foreach ($condition['goods_flag'] as $v) {
        $v['goods_flag'] = 'Y';
        $v['spec_flag'] = 'N';
        $v['logi_flag'] = 'N';
        $v['hs_flag'] = 'N';
        $r = array_merge($data, $v);
        $attrs[] = $r;
      }
    } elseif ($condition['spec_flag']) {
      foreach ($condition['spec_flag'] as $v) {
        $v['goods_flag'] = 'N';
        $v['spec_flag'] = 'Y';
        $v['logi_flag'] = 'N';
        $v['hs_flag'] = 'N';
        $r = array_merge($data, $v);
        $attrs[] = $r;
      }
    } elseif ($condition['logi_flag']) {
      foreach ($condition['logi_flag'] as $v) {
        $v['goods_flag'] = 'N';
        $v['spec_flag'] = 'N';
        $v['logi_flag'] = 'Y';
        $v['hs_flag'] = 'N';
        $r = array_merge($data, $v);
        $attrs[] = $r;
      }
    } elseif ($condition['hs_flag']) {
      foreach ($condition['hs_flag'] as $v) {
        $v['goods_flag'] = 'N';
        $v['spec_flag'] = 'N';
        $v['logi_flag'] = 'N';
        $v['hs_flag'] = 'Y';
        $r = array_merge($data, $v);
        $attrs[] = $r;
      }
    }
  }

  /**
   * 根据sku获取spu
   * @param string $sku sku编码
   * @return bool
   */
  public function getSpubySku($sku = '', $lang = '') {
    if (empty($sku) || empty($lang))
      return false;

    $result = $this->field('spu')->where(array('sku' => $sku, 'lang' => $lang, 'status' => self::STATUS_VALID))->find();
    if ($result) {
      return $result['spu'];
    }
    return false;
  }

  /**
   * 获取spu下的规格商品（用于门户产品详情页）
   * @param string $spu
   * @param string $lang
   * @return array
   */
  public function getSpecGoodsBySpu($spu = '', $lang = '') {
    if (empty($spu))
      return array();

    if (redisHashExist('Sku', $spu . '_' . $lang)) {
      return json_decode(redisHashGet('Sku', $spu . '_' . $lang), true);
    }
    try {
      $field = "sku,lang,qrcode,name,show_name,model,package_quantity,exw_day,status,purchase_price1,purchase_price2,purchase_price_cur,purchase_unit";
      $condition = array(
          "spu" => $spu,
          "lang" => $lang,
          "status" => self::STATUS_VALID
      );
      $result = $this->field($field)->where($condition)->select();
      if ($result) {
        foreach ($result as $k => $item) {
          //获取商品规格
          $gattr = new GoodsAttrModel();
          $spec = $gattr->getSpecBySku($item['sku'], $item['lang']);
          $spec_str = '';
          if ($spec) {
            foreach ($spec as $r) {
              $spec_str .= $r['attr_name'] . ':' . $r['attr_value'] . $r['value_unit'] . ';';
            }
          }
          $result[$k]['spec'] = $spec_str;
        }
        redisHashSet('Sku', $spu . '_' . $lang, json_encode($result));
        return $result;
      }
    } catch (Exception $e) {
      return array();
    }
    return array();
  }
  /**
   * sku参数处理（门户后台）
   * @author klp
   * @return array
   */
    public function check_data($data=[])
    {
      $condition['lang'] = $data['lang'] ? $data['lang']: 'en';
      $condition['spu'] = $data['spu'] ? $data['spu']: '';
      $condition['sku'] = $data['sku'] ? $data['sku']: '';
      $condition['qrcode'] = $data['qrcode'] ? $data['qrcode']: '';
      $condition['model'] = $data['model'] ? $data['model']: '';
      $condition['description'] = $data['description'] ? $data['description']: '';
      $condition['package_quantity'] = $data['package_quantity'] ? $data['package_quantity']: '';
      $condition['exw_day'] = $data['exw_day'] ? $data['exw_day']: '';
      $condition['purchase_price1'] = $data['purchase_price1'] ? $data['purchase_price1']: '';
      $condition['purchase_price2'] = $data['purchase_price2'] ? $data['purchase_price2']: '';
      $condition['purchase_price_cur'] = $data['purchase_price_cur'] ? $data['purchase_price_cur']: '';
      $condition['purchase_unit'] = $data['purchase_unit'] ? $data['purchase_unit']: '';
      $condition['pricing_flag'] = $data['pricing_flag'] ? $data['pricing_flag']: 'N';
      $condition['created_by'] = $data['created_by'] ? $data['created_by']: '';
      $condition['created_at'] = $data['created_at'] ? $data['created_at']: date('Y-m-d H:i:s');
      if (isset($data['name'])) {
        $condition['name'] = $data['name'];
      } else {
        JsonReturn('','-1001','商品名称不能为空');
      }
      if (isset($data['show_name'])) {
        $condition['show_name'] = $data['show_name'];
      } else {
        JsonReturn('','-1001','商品展示名称不能为空');
      }
      if(isset($data['status'])){
          switch ($data['status']) {
            case self::STATUS_VALID:
              $condition['status'] = $data['status'];
              break;
            case self::STATUS_INVALID:
              $condition['status'] = $data['status'];
              break;
            case self::STATUS_DELETED:
              $condition['status'] = $data['status'];
              break;
          }
      } else {
        $condition['status'] = self::STATUS_VALID;
      }
      return $condition;
    }

    /**
     * sku新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createSku($data)
    {
      $condition = $this->check_data($data);

      $res = $this->add($condition);
      if($res){
        return true;
      } else{
        return false;
      }
    }

    /**
     * sku更新（门户后台）
     * @author klp
     * @return bool
     */
    public function updateSku($data,$where)
    {
      $condition = $this->check_data($data);
      if(!empty($where)){
        return $this->where($where)->save($condition);
      } else {
        JsonReturn('','-1001','条件不能为空');
      }
    }
    /**
     * sku删除（门户后台）
     * @author klp
     * @return bool
     */
    public function deleteSku($where)
    {
      if(!empty($where)){
        return $this->where($where)->delete();
      } else {
        JsonReturn('','-1001','条件不能为空');
      }
    }
}
