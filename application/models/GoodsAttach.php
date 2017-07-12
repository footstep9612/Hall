<?php
/**
 * 商品附件
 * User: linkai
 * Date: 2017/6/24
 * Time: 15:26
 */
class GoodsAttachModel extends PublicModel
{
    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'goods_attach';

        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
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
            if($status != '' && !in_array($status , array('VALID','INVALID','DELETED'))){
                jsonReturn('',1000);
            }
            $where['status'] = $status;
        }

        //读取redis缓存
        if(redisHashExist('Attach',$sku.'_'.$type.'_'.$status)){
            return (array)json_decode(redisHashGet('Attach',$sku.'_'.$type.'_'.$status));
        }

        try{
            $field = 'id,attach_type,attach_name,attach_url,status,created_at';
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

    /**
     * sku附件参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data=[])
    {
//        $condition['sku'] = $data['sku'] ? $data['sku']: '';
//        $condition['attach_name'] = $data['attach_name'] ? $data['attach_name']: '';
        $condition['sort_order'] = isset($data['sort_order']) ? $data['sort_order']: 0;
        $condition['created_at'] = isset($data['created_at']) ? $data['created_at']: date('Y-m-d H:i:s');
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('','-1001','sku编号不能为空');
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
        if (is_array($data['SMALL_IMAGE'])) {
            foreach ($data['SMALL_IMAGE'] as $k=>$v) {
                $condition['attach_type'] = 'SMALL_IMAGE';
                $condition['attach_name'] = $k;
                if(isset($v)) {
                    $condition['attach_url'] = $v;
                }
                $attachs[] = $condition;
            }
        } elseif (is_array($data['BIG_IMAGE'])) {
            foreach ($data['BIG_IMAGE'] as $k=>$v) {
                $condition['attach_type'] = 'BIG_IMAGE';
                $condition['attach_name'] = $k;
                if(isset($v)) {
                    $condition['attach_url'] = $v;
                }
                $attachs[] = $condition;
            }
        } elseif (is_array($data['MIDDLE_IMAGE'])) {
            foreach ($data['MIDDLE_IMAGE'] as $k=>$v) {
                $condition['attach_type'] = 'MIDDLE_IMAGE';
                $condition['attach_name'] = $k;
                if(isset($v)) {
                    $condition['attach_url'] = $v;
                }
                $attachs[] = $condition;
            }
        } elseif (is_array($data['DOC'])) {
            foreach ($data['DOC'] as $k=>$v) {
                $condition['attach_type'] = 'DOC';
                $condition['attach_name'] = $k;
                if(isset($v)) {
                    $condition['attach_url'] = $v;
                }
                $attachs[] = $condition;
            }
        }
        return $attachs;
    }

    /**
     * sku附件新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createAttachSku($data)
    {
        $arr = $this->check_data($data);

        $res = $this->addAll($arr);
        if($res){
            return true;
        } else{
            return false;
        }
    }
    /**
     * sku附件更新（门户后台）
     * @author klp
     * @return bool
     */
    public function updateAttachSku($data)
    {

        $condition = $this->check_up($data);//var_dump($condition);die;
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
    }
    /**
     * sku附件更新参数处理（门户后台）
     * @author klp
     * @return bool
     */
    public function check_up($data)
    {
        $condition = [];
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('','-1001','sku编号不能为空');
        }
        if (isset($data['sort_order'])) {$condition['sort_order'] = $data['sort_order'];}
        if (isset($data['status'])) {
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
        }
        //附件组处理
        $attachs = array();
        if (isset($data['SMALL_IMAGE']) && is_array($data['SMALL_IMAGE'])) {
            foreach ($data['SMALL_IMAGE'] as $v) {
                $condition['id'] = $v['id'];
                $condition['attach_name'] = $v['attach_name'];
                $condition['attach_url'] = $v['attach_url'];
                $attachs[] = $condition;
            }
        } elseif (isset($data['BIG_IMAGE']) && is_array($data['BIG_IMAGE'])) {
            foreach ($data['BIG_IMAGE'] as $v) {
                $condition['id'] = $v['id'];
                $condition['attach_name'] = $v['attach_name'];
                $condition['attach_url'] = $v['attach_url'];
                $attachs[] = $condition;
            }
        } elseif (isset($data['MIDDLE_IMAGE']) && is_array($data['MIDDLE_IMAGE'])) {
            foreach ($data['MIDDLE_IMAGE'] as $v) {
                $condition['id'] = $v['id'];
                $condition['attach_name'] = $v['attach_name'];
                $condition['attach_url'] = $v['attach_url'];
                $attachs[] = $condition;
            }
        } elseif (isset($data['DOC']) && is_array($data['DOC'])) {
            foreach ($data['DOC'] as $v) {
                $condition['id'] = $v['id'];
                $condition['attach_name'] = $v['attach_name'];
                $condition['attach_url'] = $v['attach_url'];
                $attachs[] = $condition;
            }
        }
        return $attachs;
    }

    /**
     * sku附件软删除[状态更改]（门户后台）
     * @author klp
     * @return bool
     */
    public function modifySkuAttach($delData)
    {
        $where = []; $status = [];
        if(isset($delData['sku'])){
            $where['sku'] = array('in',explode(',',$delData['sku']));
        }else{
            JsonReturn('','-1001','sku不能为空');
        }
        if(isset($delData['status'])) {
            switch ($delData['status']) {
                case self::STATUS_VALID:
                    $status['status'] = $delData['status'];
                    break;
                case self::STATUS_INVALID:
                    $status['status'] = $delData['status'];
                    break;
                case self::STATUS_DELETED:
                    $status['status'] = $delData['status'];
                    break;
            }
        } else{
            JsonReturn('','-1003','[status]不能为空');
        }
        try {
            $result = $this->where($where)->save($status);
            if(isset($result)){
                return true;
            }else{
                return false;
            }
        } catch (Exception $e) {
//        $results['code'] = $e->getCode();
//        $results['message'] = $e->getMessage();
            return false;
        }
    }

    /**
     * sku附件删除（门户后台）
     * @author klp
     * @return bool
     */
    public function deleteRealAttach($delData)
    {
        $where = [];
        if(isset($delData['sku'])){
            $where['sku'] = $delData['sku'];
        }else{
            JsonReturn('','-1001','sku不能为空');
        }
        try{
            return $this->where($where)->save(['status' => 'DELETED']);
        } catch(Exception $e){
//            $results['code'] = $e->getCode();
//            $results['message'] = $e->getMessage();
            return false;
        }
    }
}