<?php

/**
 * 商品附件
 * User: linkai
 * Date: 2017/6/24
 * Time: 15:26
 */
class GoodsAttachModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attach'; //数据表表名

//    public function __construct() {
//        //动态读取配置中的数据库配置   便于后期维护
//        $config_obj = Yaf_Registry::get("config");
//        $config_db = $config_obj->database->config->goods->toArray();
//        $this->dbName = $config_db['name'];
//        $this->tablePrefix = $config_db['tablePrefix'];
//        $this->tableName = 'goods_attach';
//
//        parent::__construct();
//    }
    //状态--INVALID,CHECKING,VALID,DELETED

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const STATUS_CHECKING = 'CHECKING'; //审核；
    const STATUS_DRAFT = 'DRAFT';       //草稿
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    //定义校验规则

    protected $field = array(
        'attach_url' => array('required')
    );

    /**
     * 获取商品附件
     * @param array $condition
     * @return array|mixed
     */
    public function getAttach($condition = []) {
        $sku = isset($condition['sku']) ? $condition['sku'] : '';
        if (empty($sku)) {
            jsonReturn('', 1000, '[sku]不可以为空');
        }
        $where = array(
            'sku' => $sku,
        );
        $type = isset($condition['attach_type']) ? strtoupper($condition['attach_type']) : '';
        if ($type) {
            if (!in_array($type, array('SMALL_IMAGE', 'MIDDLE_IMAGE', 'BIG_IMAGE', 'DOC'))) {
                jsonReturn('', 1000, '[type]不正确');
            }
            $where['attach_type'] = $type;
        }
        $status = isset($condition['status']) ? strtoupper($condition['status']) : '';
        if ($status) {
            if ($status != '' && !in_array($status, array('VALID', 'INVALID', 'DELETED'))) {
                jsonReturn('', 1000, '[status]不正确');
            }
            $where['status'] = $status;
        }

        //读取redis缓存
        if (redisHashExist('Attach', $sku . '_' . $type . '_' . $status)) {
//            return (array) json_decode(redisHashGet('Attach', $sku . '_' . $type . '_' . $status));
        }

        try {
            $field = 'id,attach_type,attach_name,attach_url,status,created_at';
            $result = $this->field($field)->where($where)->select();
            if ($result) {
                $data = array();
                //按类型分组
                if (empty($type)) {
                    foreach ($result as $item) {
                        $data[$item['attach_type']][] = $item;
                    }
                    $result = $data;
                }
                //添加到缓存
//                redisHashSet('Attach', $sku . '_' . $type . '_' . $status, json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
        return array();
    }

    /**
     * sku附件新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createAttachSku($data) {
        if (empty($data)) {
            return false;
        }
        $arr = $this->check_data($data);

        $res = $this->addAll($arr);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * sku附件更新（门户后台）
     * @author klp
     * @return bool
     */
    /*    public function updateAttachSku($data){

      $condition = $this->check_up($data);
      if($condition){
      try{
      foreach($condition as $v){
      $this->where("id =". $v['id'])->save($v);
      }
      return true;
      } catch(\Kafka\Exception $e){
      return false;
      }
      } else{
      return false;
      }
      } */

    /**
     * sku附件参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data = []) {
        if (empty($data)) {
            return false;
        }
        if (isset($data['sku']) && !empty($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('', '-1001', 'sku编号不能为空');
        }
        $condition['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        if (isset($data['status'])) {
            switch (strtoupper($data['status'])) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        } else {
            $condition['status'] = self::STATUS_VALID;
        }
        //附件组处理
        $attachs = array();
        foreach ($data['attachs'] as $k => $v) {
            $condition['attach_type'] = isset($v['attach_type']) ? $v['attach_type'] : 'BIG_IMAGE'; //默认
            $condition['attach_name'] = isset($v['attach_name']) ? $v['attach_name'] : '';
            $condition['attach_url'] = $v['attach_url'];
            $condition['sort_order'] = isset($v['sort_order']) ? $v['sort_order'] : 0;
            $attachs[] = $condition;
        }
        return $attachs;
    }

    /**
     * sku附件更新参数处理（门户后台）
     * @author klp
     * @return arr
     */
    public function check_up($data) {
        if (empty($data))
            return false;

        $condition = [];
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('', '-1001', 'sku编号不能为空');
        }
        if (isset($data['sort_order'])) {
            $condition['sort_order'] = $data['sort_order'];
        }
        if (isset($data['status'])) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        }
        //附件组处理
        $attachs = array();
        foreach ($data['attachs'] as $k => $v) {
            if (!isset($v['id'])) {
                JsonReturn('', '-1003', '附件[id]不能为空');
            }
            $condition['id'] = $v['id'];
            if (isset($v['attach_type'])) {
                $condition['attach_type'] = $v['attach_type'];
            }
            if (isset($v['attach_name'])) {
                $condition['attach_name'] = $v['attach_name'];
            }
            if (isset($v['attach_url'])) {
                $condition['attach_url'] = $v['attach_url'];
            }
            if (isset($v['sort_order'])) {
                $condition['sort_order'] = $v['sort_order'];
            }
            $attachs[] = $condition;
        }
        return $attachs;
    }

//-----------------------------------BOS.V2-----------------------------------------------------------//
    /**
     * sku附件查询 -- 公共
     * @author klp
     * @return array
     */
    public function getSkuAttachsInfo($condition) {
        if (!isset($condition)) {
            return false;
        }
        if (isset($condition['sku']) && !empty($condition['sku'])) {
            $where = array('sku' => trim($condition['sku']));
        } else {
            jsonReturn('', MSG::MSG_FAILED, MSG::ERROR_PARAM);
        }
        if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        } else {
            $where['status'] = array('neq', self::STATUS_DELETED);
        }
        if (!empty($condition['attach_type']) && !in_array($condition['attach_type'], array('SMALL_IMAGE', 'MIDDLE_IMAGE', 'BIG_IMAGE', 'DOC'))) {
            $where['status'] = strtoupper($condition['attach_type']);
        }
        $where['deleted_flag'] = self::DELETE_N;
        //redis
        /* if (redisHashExist('SkuAttachs', md5(json_encode($where)))) {
          $data = json_decode(redisHashGet('SkuAttachs', md5(json_encode($where))), true);
          if ($data) {
          return $data;
          }
          } */
        $field = 'id, sku, supplier_id, attach_type, attach_name, attach_url, default_flag, sort_order, status, created_by,  created_at, updated_by, updated_at, checked_by, checked_at';
        try {
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if ($result) {
                //按照类型分组
                foreach ($result as $item) {
                    $data[$item['attach_type']][] = $item;
                }
//                redisHashSet('SkuAttachs', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * sku附件新增/编辑 -- 公共
     * @author klp
     * @return array
     */
    public function editSkuAttach($input, $sku = '', $admin = '') {
        if (empty($input) || empty($sku)) {
            return false;
        }
        $results = array();
        if ($input && is_array($input)) {
            try {
                //暂时新增/编辑时都为删除旧数据新增新数据,前端操作处理改为如此,个人感觉不太好
                $where = array('sku' => $sku);
                $resach = $this->field('sku')->where($where)->find();
                if ($resach) {
                    $resOut = $this->where($where)->save(['status' => self::STATUS_DELETED, 'deleted_flag' => 'Y']);
                    if (!$resOut) {
                        return false;
                    }
                }
                foreach ($input as $key => $value) {
                    $data = $this->checkParam($value);
                    //存在sku编辑,反之新增,后续扩展性
                    if (isset($data['id']) && !empty($data['id'])) {
                        $data['updated_by'] = $admin;
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                        $where = [
                            'sku' => trim($sku),
                            'id' => $data['id']
                        ];
                        $res = $this->where($where)->save($data);
                        if (!$res) {
                            return false;
                        }
                    } else {
                        $data['status'] = self::STATUS_VALID;
                        $data['sku'] = $sku;
                        $data['created_by'] = $admin;
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $res = $this->add($data);
                        if (!$res) {
                            return false;
                        }
                    }
                }
                if ($res) {
                    $es_goods_model = new EsGoodsModel();
                    $es_goods_model->Update_Attachs($sku);
                    $results['code'] = '1';
                    $results['message'] = '成功！';
                } else {
                    $results['code'] = '-101';
                    $results['message'] = '失败!';
                }
                return $results;
            } catch (Exception $e) {
                $results['code'] = $e->getCode();
                $results['message'] = $e->getMessage();
                return $results;
            }
        }
        return false;
    }

    /**
     * sku附件[状态更改]
     * @author klp
     * @return bool
     */
    public function modifyAttach($data, $status) {
        if (empty($data) || empty($status)) {
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $results = array();
        try {
            foreach ($data as $sku) {
                if (self::STATUS_CHECKING == $status) {
                    $where = [
                        'sku' => $sku
                    ];
                    $resach = $this->field('sku')->where($where)->find();
                    if ($resach) {
                        $res = $this->where($where)->save(['status' => $status, 'updated_by' => defined('UID') ? UID : 0, 'updated_at' => date('Y-m-d H:i:s')]);
                        if (!$res) {
                            return false;
                        }
                    }
                } else {
                    $where = [
                        'sku' => $sku
                    ];
                    $save = [
                        'status' => $status,
                        'checked_by' => $userInfo['id'],
                        'checked_at' => date('Y-m-d H:i:s', time())
                    ];
                    $resach = $this->field('sku')->where($where)->find();
                    if ($resach) {
                        $res = $this->where($where)->save($save);
                        if (!$res) {
                            return false;
                        }
                    }
                }
            }
            return array('code' => 1, 'message' => '成功！');
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * sku附件删除
     * @author klp
     * @return bool
     */
    public function deleteSkuAttach($skus) {
        if (empty($skus)) {
            return false;
        }
        $results = array();
        try {

            if ($skus && is_array($skus)) {
                $where = [
                    "sku" => ['in', $skus],
                ];
                $res = $this->where($where)->save(['deleted_flag' => 'Y']);

                if ($res === false) {
                    return false;
                }
            } else {
                $where = [
                    "sku" => $skus
                ];
                $res = $this->where($where)->save(['deleted_flag' => 'Y']);
                if ($res === false) {
                    return false;
                }
            }

            if ($res !== false) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $es_goods_model = new EsGoodsModel();
                $es_goods_model->BatchUpdate_Attachs($skus, null);
            } else {
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }

            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 参数校验    注：没有参数或没有规则，默认返回true（即不做验证）
     * @param array $param  参数
     * @param array $field  校验规则
     * @return
     *
     */
    private function checkParam($param = []) {
        if (empty($param)) {
            jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
        }
        $data = $results = [];
        if (isset($param['supplier_id']) && !empty($param['supplier_id'])) {
            $data['supplier_id'] = $param['supplier_id'];
        }
        if (isset($param['attach_type']) && !empty($param['attach_type'])) {
            $data['attach_type'] = $param['attach_type'];
        }
        if (isset($param['attach_name']) && !empty($param['attach_name'])) {
            $data['attach_name'] = $param['attach_name'];
        } else {
            if (isset($param['attach_url']) && !empty($param['attach_url'])) {
                $data['attach_name'] = $param['attach_url'];
            }
        }
        if (isset($param['attach_url']) && !empty($param['attach_url'])) {
            $data['attach_url'] = $param['attach_url'];
        } else {
            $results['code'] = -101;
            $results['message'] = '[attach_url]参数缺少!';
        }
        if (isset($param['default_flag']) && !empty($param['default_flag'])) {
            $data['default_flag'] = $param['default_flag'];
        }
        if (isset($param['supplier_id']) && !empty($param['sort_order'])) {
            $data['sort_order'] = $param['sort_order'];
        }
        if ($results) {
            jsonReturn($results);
        }
        return $data;
    }

    /* 通过SKU获取数据商品文件列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getgoods_attachsbyskus($skus, $lang = 'en') {

        try {
            $goods_attachs = $this->table('erui_goods.goods_attach')
                    ->field('id,attach_type,attach_url,attach_name,attach_url,sku,default_flag')
                    ->where(['sku' => ['in', $skus],
                        'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])
                    ->order('default_flag desc,sort_order desc')
                    ->select();
            $ret = [];
            if ($goods_attachs) {
                foreach ($goods_attachs as $item) {
                    $data['attach_name'] = $item['attach_name'];
                    $data['attach_url'] = $item['attach_url'];

                    $ret[$item['sku']][$item['attach_type']][] = $data;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            print_r($ex->getMessage());
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
