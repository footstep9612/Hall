<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:47
 */
class SpecialPositionModel extends PublicModel {
    protected $tableName = 'special_position';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getList($condition,$fields=''){
        $where =['deleted_at'=>['exp', 'is null']];
        if(isset($condition['special_id'])){
            $where['special_id'] = intval($condition['special_id']);
        }
        if(isset($condition['name'])){
            $where['name'] = ['like', '%' . trim($condition['name']) . '%'];
        }
        if(isset($condition['type'])){
            $where['type'] = trim($condition['type']);
        }
        if(isset($condition['created_at_start']) && isset($condition['created_at_end'])){
            $where['created_at'] = ['between', trim($condition['created_at_start']).','.trim($condition['created_at_end'])];
        }elseif(isset($condition['created_at_start'])){
            $where['created_at_start'] = ['egt', trim($condition['created_at_start'])];
        }elseif(isset($condition['created_at_end'])){
            $where['created_at_start'] = ['elt', trim($condition['created_at_end'])];
        }

        try{
            $data = [];
            list($from, $size) = $this->_getPage($condition);
            $rel = $this->field($fields ? $fields : 'id,special_id,type,name,description,created_at,created_by,updated_by,updated_at,remark,sort_order,thumb,maxnum,link,settings')->where($where)->order('sort_order DESC')
                ->limit($from, $size)
                ->select();
            if($rel){
                $data['data'] = $rel;
                $count = $this->getCount($where);
                $data['count'] = $count ? $count : 0;
            }
            return $data;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 获取记录数
     * @param $where
     * @return bool
     */
    public function getCount($where) {
        try{
            return $this->field('id')->where($where)
                ->count();
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 根据id获取详情
     * @param $id
     * @return bool|mixed
     */
    public function getInfo($id){
        if(!$id || !is_numeric($id)){
            jsonReturn('', MSG::MSG_FAILED,'id不存在');
        }
        try{
            return $this->field('id,special_id,type,name,description,created_at,created_by,updated_by,updated_at,remark,sort_order,thumb,maxnum,link,settings')->where(['id'=>$id,'deleted_at'=>['exp', 'is null']])->find();
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 新增
     * @param array $input
     * @return bool|mixed
     */
    public function createData($input=[]){
        if(!isset($input['name']) || empty($input['name'])){
            jsonReturn('', MSG::MSG_FAILED,'请输入名称');
        }
        try{
            $data = [
                'special_id' => isset($input['special_id']) ? intval($input['special_id']) : 0,
                'type' => isset($input['type']) ? trim($input['type']) : 'G',
                'name' =>trim($input['name']),
                'description' => isset($input['description']) ? trim($input['description']) : '',
                'remark' => isset($input['remark']) ? trim($input['remark']) : '',
                'sort_order' => isset($input['sort_order']) ? intval($input['sort_order']) : 0,
                'thumb' => isset($input['thumb']) ? trim($input['thumb']) : '',
                'maxnum' => isset($input['maxnum']) ? intval($input['maxnum']) : 20,
                'link' => isset($input['link']) ? trim($input['link']) : '',
                'settings' => isset($input['settings']) ? json_encode($input['settings'],320) : '',
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'special_id' => $data['special_id'],
                'name' => $data['name'],
                'deleted_at' => ['exp', 'is null']
            ];
            if(!self::exist($where)){
                return $this->add($data);
            }else{
                jsonReturn('', MSG::MSG_FAILED,'已经存在');
            }
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 修改
     * @param array $input
     * @return bool
     */
    public function updateData($input=[]){
        if(empty($input)){
            jsonReturn('', MSG::MSG_FAILED,'没有信息');
        }
        try{
            $id = isset($input['id']) ? intval($input['id']) : 0;
            if(!$id){
                jsonReturn('', MSG::MSG_FAILED,'没有id');
            }
            $data = [
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            if(isset($input['name'])){
                $where = [
                    'id' => ['neq', $id],
                    'special_id' => isset($input['special_id']) ? intval($input['special_id']) : 0,
                    'name' => trim($input['name']),
                    'deleted_at' => ['exp', 'is null']
                ];
                if(self::exist($where)){
                    jsonReturn('', MSG::MSG_FAILED,'已经存在');
                }
                $data['special_id'] = isset($input['special_id']) ? intval($input['special_id']) : 0;
                $data['name'] = trim($input['name']);
            }
            if(isset($input['remark'])){
                $data['remark'] = trim($input['remark']);
            }
            if(isset($input['type'])){
                $data['type'] = trim($input['type']);
            }
            if(isset($input['description'])){
                $data['description'] = trim($input['description']);
            }
            if(isset($input['sort_order'])){
                $data['sort_order'] = trim($input['sort_order']);
            }
            if(isset($input['thumb'])){
                $data['thumb'] = trim($input['thumb']);
            }
            if(isset($input['maxnum'])){
                $data['maxnum'] = intval($input['maxnum']);
            }
            if(isset($input['link'])){
                $data['link'] = intval($input['link']);
            }
            if(isset($input['settings'])){
                $data['settings'] = json_encode($input['settings'],320);
            }
            return $this->where(['id' => $id, 'deleted_at' => ['exp','is null']])->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @param array $input
     * @return bool
     */
    public function deleteData($input=[]){
        if(empty($input) || !isset($input['id'])){
            return false;
        }
        try{
            if(is_array($input['id'])){
                $where['id'] = ['in', $input['id']];
            }else{
                $where['id'] = intval($input['id']);
            }
            $data=[
                'deleted_by' => defined('UID') ? UID : 0,
                'deleted_at' => date('Y-m-d H:i:s', time())
            ];
            return $this->where($where)->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 检测是否存在
     * @param array $where
     * @return bool|mixed
     */
    private function exist($where=[]){
        if(empty($where)){
            return true;
        }
        try{
            return $this->field('id')->where($where)->find();
        }catch (Exception $e){
            return true;
        };
    }

}