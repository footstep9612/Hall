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
    public function getAttachBySpu($spu = '',$status='') {
        if (empty($spu)) {
            jsonReturn('',ErrorMsg::NOTNULL_SPU);
        }

        $field = 'attach_type,attach_name,attach_url,default_flag,sort_order,status,created_by,created_at,updated_by,updated_at,checked_by,checked_at';
        $condition = array(
            'spu' => $spu,
            'default_flag' => self::DELETED_N
        );
        if(!empty($status)){
            $condition['status'] = $status;
        }

        //根据缓存读取,没有则查找数据库并缓存
        $key_redis = md5(json_encode($condition));
        if (redisHashExist('spu_attach',$key_redis)) {
            $result = redisHashGet('spu_attach',$key_redis);
            return json_decode($result,true);
        } else {
            try{
                $result = $this->field($field)->where($condition)->select();
                if ($result) {
                    $data = array();
                    //按类型分组
                    foreach ($result as $item) {
                        $data[$item['attach_type']][] = $item;
                    }
                    redisHashSet($key_redis, json_encode($data));
                    return $data;
                }
                return array();
            }catch (Exception $e){
                return false;
            }
        }
    }


    /**
     * 添加附件
     * @param array $data
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
        $data['attach_name'] = isset($input['attach_name']) ? $input['attach_name'] : '';
        $data['attach_url'] = isset($input['attach_url']) ? $input['attach_url'] : '';
        $data['sort_order'] = isset($input['sort_order']) ? $input['sort_order'] : '';
        $data['default_flag'] = self::DELETED_N;
        $data['status'] = isset($input['status']) ? $input['status'] : self::STATUS_CHECKING;
        $data['created_at'] = date('Y-m-d H:i:s', time());
        $data['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : '';
        return $this->add($data);
    }

}
