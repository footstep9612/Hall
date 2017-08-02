<?php
/**
 * 商品附件
 * User: linkai
 * Date: 2017/6/24
 * Time: 15:26
 */
class GoodsAttachModel extends PublicModel{
    protected $dbName = 'erui2_goods'; //数据库名称
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

    //定义校验规则
    protected $field = array(
        'attach_url' => array('required')
    );

    /**
     * 获取商品附件
     * @param array $condition
     * @return array|mixed
     */
    public function getAttach($condition=[]){
        $sku = isset($condition['sku']) ? $condition['sku'] : '';
        if (empty($sku)) {
            jsonReturn('', 1000,'[sku]不可以为空');
        }
        $where = array(
            'sku' => $sku,
        );
        $type = isset($condition['attach_type']) ? strtoupper($condition['attach_type']) : '';
        if($type){
            if(!in_array($type , array('SMALL_IMAGE','MIDDLE_IMAGE','BIG_IMAGE','DOC'))){
                jsonReturn('',1000,'[type]不正确');
            }
            $where['attach_type'] = $type;
        }
        $status = isset($condition['status']) ? strtoupper($condition['status']) : '';
        if($status){
            if($status != '' && !in_array($status , array('VALID','INVALID','DELETED'))){
                jsonReturn('',1000,'[status]不正确');
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
     * sku附件新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createAttachSku($data){
        if(empty($data)) {
            return false;
        }
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
        }*/


    /**
     * sku附件参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data=[]){
        if(empty($data)) {
            return false;
        }
        if (isset($data['sku']) && !empty($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('','-1001','sku编号不能为空');
        }
        $condition['created_at'] = isset($data['created_at']) ? $data['created_at']: date('Y-m-d H:i:s');
        if(isset($data['status'])){
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
        foreach ($data['attachs'] as $k=>$v) {
            $condition['attach_type'] = isset($v['attach_type']) ? $v['attach_type'] : 'BIG_IMAGE';//默认
            $condition['attach_name'] = isset($v['attach_name']) ? $v['attach_name']: '';
            $condition['attach_url'] = $v['attach_url'];
            $condition['sort_order'] = isset($v['sort_order']) ? $v['sort_order']: 0;
            $attachs[] = $condition;
        }
        return $attachs;
    }

    /**
     * sku附件更新参数处理（门户后台）
     * @author klp
     * @return arr
     */
    public function check_up($data){
        if(empty($data))
            return false;

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
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        }
        //附件组处理
        $attachs = array();
        foreach ($data['attachs'] as $k=>$v) {
            if(!isset($v['id'])){
                JsonReturn('','-1003','附件[id]不能为空');
            }
            $condition['id'] = $v['id'];
            if (isset($v['attach_type'])) {$condition['attach_type'] = $v['attach_type'];}
            if (isset($v['attach_name'])) {$condition['attach_name'] = $v['attach_name'];}
            if (isset($v['attach_url'])) {$condition['attach_url'] = $v['attach_url'];}
            if (isset($v['sort_order'])) {$condition['sort_order'] = $v['sort_order'];}
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
        } else{
            jsonReturn('',MSG::MSG_FAILED,MSG::ERROR_PARAM);
        }
        if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        } else{
            $where['status'] = array('<>', self::STATUS_DELETED);
        }
        if(!empty($condition['attach_type']) && !in_array($condition['attach_type'] , array('SMALL_IMAGE','MIDDLE_IMAGE','BIG_IMAGE','DOC'))){
            $where['status'] = strtoupper($condition['attach_type']);
        }

        //redis
        if (redisHashExist('SkuAttachs', md5(json_encode($where)))) {
            return json_decode(redisHashGet('SkuAttachs', md5(json_encode($where))), true);
        }
        $field = 'id, sku, supplier_id, attach_type, attach_name, attach_url, default_flag, sort_order, status, created_by,  created_at, updated_by, updated_at, checked_by, checked_at';
        try {
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if($result){
                //按照类型分组
                foreach ($result as $item){
                    $data[$item['attach_type']][] = $item;
                }
                redisHashSet('SkuAttachs', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e){
            return array();
        }
    }

    /**
     * sku附件新增/编辑 -- 公共
     * @author klp
     * @return array
     */
    public function createAttach($input){
       if(empty($input)) {
           return false;
       }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->startTrans();
        try {
            foreach ($input['attachs']  as $key => $value) {
                $checkout = $this->checkParam($value,$this->field);
                $data = [
                    'supplier_id' => isset($checkout['supplier_id']) ? $checkout['supplier_id'] : '',
                    'attach_type' => isset($checkout['attach_type']) ? $checkout['attach_type'] : '',
                    'attach_name' => isset($checkout['attach_name']) ? $checkout['attach_name'] : '',
                    'attach_url' => $checkout['attach_url'],
                    'default_flag' => isset($checkout['default_flag']) ? $checkout['default_flag'] : 'N',
                    'sort_order' => isset($checkout['sort_order']) ? $checkout['sort_order'] : 0
                ];
                //存在sku编辑,反之新增,后续扩展性
                $result = $this->field('sku')->where(['sku' => $input['sku']])->find();
                if ($result) {
                    $data['updated_by'] = $userInfo['id'];
                    $data['updated_at'] =  date('Y-m-d H:i:s', time());
                    $where = [
                        'sku' => trim($input['sku']),
                        'id' => $checkout['id']
                    ];
                    $this->where($where)->save($data);
                } else {
                    $data['status'] = self::STATUS_DRAFT;
                    $data['sku'] = $input['sku'];
                    $data['created_by'] = $userInfo['id'];
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $this->add($data);
                }
            }
            $this->commit();
            return true;
        }catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku附件[状态更改]
     * @author klp
     * @return bool
     */
    public function modifyAttach($data,$status){
        if(empty($data) || empty($status)) {
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->startTrans();
        try {
            foreach($data as $item) {
                if(self::STATUS_CHECKING == $status){
                    $where = [
                        'sku' => $item['sku']
                    ];
                    $resach = $this->field('sku')->where($where)->find();
                    if ($resach) {
                        $this->where($where)->save(['status' => $status]);
                    }
                } else {
                    $where = [
                        'sku' => $item['sku']
                    ];
                    $save =[
                        'status'     => $status,
                        'checked_by' => $userInfo['id'],
                        'checked_at' => date('Y-m-d H:i:s', time())
                    ];
                    $resach = $this->field('sku')->where($where)->find();
                    if ($resach) {
                        $this->where($where)->save($save);
                    }
                }
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku附件删除
     * @author klp
     * @return bool
     */
    public function deleteSkuAttach($delData){
        if(empty($delData)) {
            return false;
        }
        $this->startTrans();
        try{
            $where = [
                "sku" => $delData
            ];
            $resach = $this->field('sku')->where($where)->find();
            if ($resach) {
                $this->where($where)->save(['status' => self::STATUS_DELETED,'deleted_flag'=>'Y']);
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }


    /**
     * 参数校验    注：没有参数或没有规则，默认返回true（即不做验证）
     * @param array $param  参数
     * @param array $field  校验规则
     * @return bool
     *
     */
    private function checkParam($param = [], $field = [])
    {
        if (empty($param) || empty($field))
            return array();
        foreach ($param as $k => $v) {
            if (isset($field[$k])) {
                 $item = $field[$k];
                switch ($item[0]) {
                    case 'required':
                        if ($v == '' || empty($v)) {
                            jsonReturn('', '1000', 'Param ' . $k . ' Not null !');
                        }
                        break;
//                    case 'method':
//                        if (!method_exists($item[1])) {
//                            jsonReturn('', '404', 'Method ' . $item[1] . ' nont find !');
//                        }
//                        if (!call_user_func($item[1], $v)) {
//                            jsonReturn('', '1001', 'Param ' . $k . ' Validate failed !');
//                        }
//                        break;
                }
            }
            continue;
        }
        return $param;
    }

}