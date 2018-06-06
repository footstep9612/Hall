<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/6/4
 * Time: 14:36
 */
class SupplierProductAttachModel extends PublicModel{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_product_attach';

    public function __construct($str = ''){
        parent::__construct($str = '');
    }


    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {
        $where = $this->_getCondition($condition);

        $field = 'id,spu,attach_type,attach_name,attach_url,default_flag,sort_order,status,created_at';
        return $this->field($field)
                     ->where($where)
                     ->order('id desc')
                     ->select();
    }

    /**
     *获取定制数量
     * @param array $condition
     * @author  klp
     */
    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }

    /**
     * 获取产品附件详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getDetail($condition = []) {
        $where = $this->_getCondition($condition);
        $field = 'id,spu,attach_type,attach_name,attach_url,default_flag,sort_order,status,created_at';
        return $this->field($field)
                     ->where($where)
                     ->select();
    }

    /**
     * @desc 添加记录
     * @param array $condition
     */
    public function addRecord($condition = []) {


        $data = $this->create($condition);

        return $this->add($data);
    }

    /**
     * @desc 修改信息
     * @param array $where , $condition
     * @return bool
     */
    public function updateInfo($where = [], $condition = []) {

        $data = $this->create($condition);

        $res = $this->where($where)->save($data);
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
     * @desc 软删除
     * @param array $where , $condition
     * @return bool
     */
    public function deleteInfo($where = [], $condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        }
        if (!empty($condition['spu'])) {
            $where['spu'] = ['in', explode(',', $condition['spu'])];
        }
        if(empty($where)){
            return false;
        }
        $res = $this->where($where)->save(['deleted_flag'=>'Y']);
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
     * @desc 删除记录
     * @param array $condition
     * @return bool
     */
    public function delRecord($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        }
        if(!empty($condition['spu'])){
            $where['spu'] = $condition['spu'];
        }
        if(empty($where)){
            return false;
        }
        return $this->where($where)->delete();
    }

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];
        if (isset($condition['spu']) && !empty($condition['spu'])) {
            if(is_array($condition['spu'])){
                $where['spu'] = ['in',$condition['spu']];
            }else{
                $where['spu'] = $condition['spu'];
            }
        }
        if (isset($condition['attach_type']) && !empty($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
        if (isset($condition['id']) && !empty($condition['id'])) {
            $where['id'] = $condition['id'];                  //id
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where['status'] = strtoupper($condition['status']);                  //状态
        }
        if (isset($condition['deleted_flag']) && !empty($condition['deleted_flag'])) {
            $where['deleted_flag'] = strtoupper($condition['deleted_flag']);                  //是否删除状态
        }else {
            $where['deleted_flag'] = 'N';
        }
        return $where;
    }

    //附件上传
    public function uploadattachs($array_attachs, $spu){

        foreach ($array_attachs as $item) {
            if(isset($item['attach_url']) && !empty($item['attach_url'])){
                $attachsData = [
                    'spu' => $spu,
                    'attach_url' => $item['attach_url'],
                    'attach_name' => isset($item['attach_name']) ? trim($item['attach_name']) : '',
                    'attach_type' => isset($item['attach_type']) ? $item['attach_type'] : 'BIG_IMAGE',
                    'default_flag' => !empty($item['default_flag']) ? $item['default_flag'] : 'N'
                ];

                if (!isset($item['attach_id']) || empty($item['attach_id'])) {
                    $attachsData['created_at'] = $this->getTime();
                    $res = $this->addRecord($attachsData);
                } else {
//                    $where['id'] = $item['attach_id'];
//                    $attachsData['updated_at'] = $this->getTime();
//                    $res = $this->updateInfo($where, $attachsData);
                    $attachsData['created_at'] = $this->getTime();
                    $res = $this->addRecord($attachsData);
                }
                if (!$res) {
                    return false;
                }
            }
        }
        return $res;
    }

    public function getTime() {
        return $time = date('Y-m-d H:i:s',time());
    }

}