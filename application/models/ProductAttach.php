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
}