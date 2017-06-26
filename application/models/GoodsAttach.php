<?php
/**
 * 商品附件
 * User: linkai
 * Date: 2017/6/24
 * Time: 15:26
 */
class GoodsAttachModel extends Model{
    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'goods_attach';

        parent::__construct();
    }

    /**
     * 获取商品附件
     * @param array $condition
     * @return array|mixed
     */
    public function getAttach($condition=[])
    {
        $sku = isset($condition['sku']) ? $condition['sku'] : '';
        if (empty($sku)) {
            jsonReturn('', 1000);
        }

        $where = array(
            'sku' => $sku,
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
            if(!in_array($status , array('VALID','INVALID','DELETED'))){
                jsonReturn('',1000);
            }
            $where['status'] = $status;
        }

        //读取redis缓存
        if(redisHashExist('Attach',$sku.'_'.$type.'_'.$status)){
            return (array)json_decode(redisHashGet('Attach',$sku.'_'.$type.'_'.$status));
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
                redisHashSet('Attach',$sku.'_'.$type.'_'.$status,json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();
    }
}