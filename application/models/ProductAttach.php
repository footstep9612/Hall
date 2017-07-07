<?php
/**
 * SKU附件.
 * User: linkai
 * Date: 2017/6/20
 * Time: 14:19
 */
class ProductAttachModel extends PublicModel{
    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID';  //无效
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct()
    {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj=Yaf_Registry::get("config");
        $config_db=$config_obj->database->config->goods->toArray();
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
    public function getAttachBySpu($spu=''){
        if(empty($spu))
            jsonReturn('','-1001','spu不可以为空');

        $field = 'attach_type,attach_name,attach_url,sort_order,created_at';
        $condition = array(
            'spu'   => $spu,
            'status'=> self::STATUS_VALID
        );

        //根据缓存读取,没有则查找数据库并缓存
        $key_redis = md5(json_encode($condition));
        if(redisExist($key_redis)){
            $result = redisGet($key_redis);
            return $result ? json_decode($result) : array();
        } else {
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                $data = array();
                //按类型分组
                foreach ($result as $item) {
                    $data[$item['attach_type']][] = $item;
                }

                redisSet($key_redis, json_encode($data));
                return $data;
            }
            return array();
        }
    }

    /**
     * 获取商品附件   注：此方法用去前台接口调用，因为有错误输出
     * @param array $condition
     * @return array|mixed
     */
    public function getAttach($condition=[])
    {
        $spu = isset($condition['spu']) ? $condition['spu'] : '';
        if (empty($spu)) {
            jsonReturn('', 1000);
        }

        $where = array(
            'spu' => $spu,
        );
        $type = isset($condition['attach_type']) ? strtoupper($condition['attach_type']) : '';
        if($type){
            if(!in_array($type , array('SMALL_IMAGE','MIDDLE_IMAGE','BIG_IMAGE','DOC'))){
                jsonReturn('',1000);
            }
            $where['attach_type'] = $type;
        }
        $status = isset($condition['status']) ? strtoupper($condition['status']) : '';
        if($status){
            if($status != '' && !in_array($status , array('VALID','INVALID','DELETED'))){
                jsonReturn('',1000);
            }
            $where['status'] = $status;
        }

        //读取redis缓存
        if(redisHashExist('Attach',$spu.'_'.$type.'_'.$status)){
           return json_decode(redisHashGet('Attach',$spu.'_'.$type.'_'.$status),true);
        }

        try{
            $field = 'attach_type,attach_name,attach_url,status,created_at';
            $result = $this->field($field)->where($where)->select();
            if($result){
                $data = array();
                //按类型分组
                if(empty($type)){
                    foreach($result as $item){
                        $data[$item['attach_type']][] = $item;
                    }
                    $result = $data;
                }
                //添加到缓存
                redisHashSet('Attach',$spu.'_'.$type.'_'.$status,json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();
    }

    /**
     * 添加附件
     * @param array $data
     * @return bool|mixed
     */
    public function addAttach($data=[]){
        if(empty($data))
            return false;
        $data['status'] = self::STATUS_CHECKING;
        $data['created_at'] = date('Y-m-d H:i:s',time());
        return $this->add($data);
    }

    /**
     * spu附件参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data=[])
    {
//        $condition['spu'] = $data['sku'] ? $data['spu']: '';
//        $condition['attach_type'] = $data['attach_type'] ? $data['attach_type']: 'BIG_IMAGE';
        $condition['attach_name'] = $data['attach_name'] ? $data['attach_name']: '';
        $condition['sort_order'] = $data['sort_order'] ? $data['sort_order']: 0;
        $condition['created_at'] = $data['created_at'] ? $data['created_at']: date('Y-m-d H:i:s');
        if (isset($data['spu'])) {
            $condition['spu'] = $data['spu'];
        } else {
            JsonReturn('','-1001','spu编号不能为空');
        }
        if (isset($data['attach_url'])) {
            $condition['attach_url'] = $data['attach_url'];
        } else {
            JsonReturn('','-1001','文件地址不能为空');
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
        //附件组处理
        $attachs = array();
        if (is_array($condition['SMALL_IMAGE'])) {
            foreach ($condition['SMALL_IMAGE'] as $v) {
                $condition['attach_type'] = 'SMALL_IMAGE';
                if (isset($data['attach_url'])) {
                    $condition['attach_url'] = $v['attach_url'];
                } else {
                    JsonReturn('','-1001','文件地址不能为空');
                }
                $attachs[] = $condition;
            }
        } elseif (is_array($condition['BIG_IMAGE'])) {
            foreach ($condition['BIG_IMAGE'] as $v) {
                $condition['attach_type'] = 'BIG_IMAGE';
                if (isset($data['attach_url'])) {
                    $condition['attach_url'] = $v['attach_url'];
                } else {
                    JsonReturn('','-1001','文件地址不能为空');
                }
                $attachs[] = $condition;
            }
        } elseif (is_array($condition['MIDDLE_IMAGE'])) {
            foreach ($condition['MIDDLE_IMAGE'] as $v) {
                $condition['attach_type'] = 'MIDDLE_IMAGE';
                if (isset($data['attach_url'])) {
                    $condition['attach_url'] = $v['attach_url'];
                } else {
                    JsonReturn('','-1001','文件地址不能为空');
                }
                $attachs[] = $condition;
            }
        } elseif (is_array($condition['DOC'])) {
            foreach ($condition['DOC'] as $v) {
                $condition['attach_type'] = 'DOC';
                if (isset($data['attach_url'])) {
                    $condition['attach_url'] = $v['attach_url'];
                } else {
                    JsonReturn('','-1001','文件地址不能为空');
                }
                $attachs[] = $condition;
            }
        }
        return $attachs;
    }

    /**
     * spu附件新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createSpuAttach($data)
    {
        $arr = [];
        foreach($data as $value){
            $arr[] = $this->check_data($value);
        }
        $res = $this->addAll($arr);
        if($res){
            return true;
        } else{
            return false;
        }
    }
    /**
     * spu附件更新（门户后台）
     * @author klp
     * @return bool
     */
    public function updateSpu($data,$where)
    {
        //$condition = $this->check_data($data);
        if(!empty($where)){
            return $this->where($where)->save($condition);
        } else {
            JsonReturn('','-1001','条件不能为空');
        }
    }
    /**
     * spu附件删除（门户后台）
     * @author klp
     * @return bool
     */
    public function deleteSpu($where)
    {
        if(!empty($where)){
            return $this->where($where)->delete();
        } else {
            JsonReturn('','-1001','条件不能为空');
        }
    }
}