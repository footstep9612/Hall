<?php

/**
 * 产品附件.
 * User: linkai
 * Date: 2017/6/20
 * Time: 14:19
 */
class ProductAttachModel extends PublicModel {

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID';  //无效
    const STATUS_DELETED = 'DELETED';  //无效
    const DELETED_Y = 'Y'; //删除
    const DELETED_N = 'N'; //未删除

    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'product_attach';

        parent::__construct();
    }

    /**
     * 根据spu获取附件
     * @param $spu spu编码
     * @return array
     */
    public function getAttachBySpu($spu = '', $status = '') {
        if (empty($spu)) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        $field = 'attach_type,attach_name,attach_url,default_flag,sort_order,status,created_by,created_at,updated_by,updated_at,checked_by,checked_at';
        $condition = array(
            'spu' => $spu,
            'deleted_flag' => self::DELETED_N
        );
        if (!empty($status)) {
            $condition['status'] = $status;
        }

        //根据缓存读取,没有则查找数据库并缓存
        $key_redis = md5(json_encode($condition));
        if (redisHashExist('spu_attach', $key_redis)) {
            //$result = redisHashGet('spu_attach', $key_redis);
            //return json_decode($result, true);
        }

        try {
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                $data = array();
                //按类型分组
                foreach ($result as $item) {
                    $data[$item['attach_type']][] = $item;
                }
                redisHashSet('spu_attach', $key_redis, json_encode($data));
                return $data;
            }
            return array();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 添加附件
     * @param array $input
     * @return bool|mixed
     */
    public function addAttach($input = []) {
        if (empty($input) || !isset($input['spu']) || !isset($input['attach_url'])) {
            return false;
        }
        $userInfo = getLoinInfo();
        $data = [];
        $data['spu'] = isset($input['spu']) ? $input['spu'] : '';
        $data['attach_type'] = isset($input['attach_type']) ? $input['attach_type'] : '';
        $data['attach_name'] = isset($input['attach_name']) ? $input['attach_name'] : $input['attach_url'];
        $data['attach_url'] = isset($input['attach_url']) ? $input['attach_url'] : '';
        $data['sort_order'] = isset($input['sort_order']) ? $input['sort_order'] : 0;
        $data['default_flag'] = isset($input['default_flag']) ? 'Y' : 'N';
        $data['status'] = isset($input['status']) ? $input['status'] : self::STATUS_VALID;
        $data['created_at'] = date('Y-m-d H:i:s', time());
        $data['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : '';
        if (isset($input['id']) && !empty($input['id'])) {    //修改
            if ($this->where(array('id' => $input['id']))->save($data)) {
                return $input['id'];
            } else {
                return false;
            }
        }
        return $this->add($data);
    }

    /* 通过SKU获取数据商品文件列表
     * @param mix $spus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getproduct_attachsbyspus($spus, $lang = 'en') {

        try {
            $product_attachs = $this->field('id,attach_type,attach_url,attach_name,attach_url,spu,default_flag')
                    ->where(['spu' => ['in', $spus],
                        'attach_type' => ['in', ['BIG_IMAGE', 'MIDDLE_IMAGE', 'SMALL_IMAGE', 'DOC']],
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])
                    ->order('default_flag desc,sort_order desc')
                    ->select();
            $ret = [];
            if ($product_attachs) {
                foreach ($product_attachs as $item) {
                    $data['attach_name'] = $item['attach_name'];
                    $data['attach_url'] = $item['attach_url'];
                    $ret[$item['spu']][$item['attach_type']][] = $data;
                }
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 获取商品附件   注：此方法用去前台接口调用，因为有错误输出
     * @param array $condition
     * @return array|mixed
     */
    public function getAttach($condition = []) {
        $spu = isset($condition['spu']) ? $condition['spu'] : '';
        if (empty($spu)) {
            jsonReturn('', 1000);
        }

        $where = array(
            'spu' => $spu,
        );
        $type = isset($condition['attach_type']) ? strtoupper($condition['attach_type']) : '';
        if ($type) {
            if (!in_array($type, array('SMALL_IMAGE', 'MIDDLE_IMAGE', 'BIG_IMAGE', 'DOC'))) {
                jsonReturn('', 1000);
            }
            $where['attach_type'] = $type;
        }
        $status = isset($condition['status']) ? strtoupper($condition['status']) : '';
        if ($status) {
            if ($status != '' && !in_array($status, array('VALID', 'INVALID', 'DELETED'))) {
                jsonReturn('', 1000);
            }
            $where['status'] = $status;
        }

        //读取redis缓存
        if (redisHashExist('Attach', $spu . '_' . $type . '_' . $status)) {
            return json_decode(redisHashGet('Attach', $spu . '_' . $type . '_' . $status), true);
        }

        try {
            $field = 'attach_type,attach_name,attach_url,status,created_at';
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
                redisHashSet('Attach', $spu . '_' . $type . '_' . $status, json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
        return array();
    }

}
