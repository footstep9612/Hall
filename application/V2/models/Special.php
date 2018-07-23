<?php
/**
 * 专题
 * @author  link
 * @date    2018-05-17 13:38:48
 * @version V1.0
 * @desc
 */
class SpecialModel extends PublicModel {
    protected $dbName = 'erui_mall';
    protected $tableName = 'special';

    public function __construct() {
        parent::__construct();
    }

    public function getList($condition){
        $where =['deleted_at'=>['exp', 'is null']];
        if(isset($condition['type'])){
            $where['type'] = intval($condition['type']);
        }
        if(isset($condition['country_bn'])){
            $where['country_bn'] = trim($condition['country_bn']);
        }
        if(isset($condition['lang'])){
            $where['lang'] = trim($condition['lang']);
        }
        if(isset($condition['status'])){
            $where['status'] = trim($condition['status']);
        }
        if(isset($condition['name'])){
            $where['name'] = ['like', '%' . trim($condition['name']) . '%'];
        }
        if(isset($condition['created_at_start']) && isset($condition['created_at_end'])){
            $where['created_at'] = ['between', trim($condition['created_at_start']).','.trim($condition['created_at_end'])];
        }elseif(isset($condition['created_at_start'])){
            $where['created_at'] = ['egt', trim($condition['created_at_start'])];
        }elseif(isset($condition['created_at_end'])){
            $where['created_at'] = ['elt', trim($condition['created_at_end'])];
        }

        try{
            $data = [];
            list($from, $size) = $this->_getPage($condition);
            $rel = $this->field('id,country_bn,lang,name,remark,type,status,sort_order,show_flag,created_at,created_by,updated_by,updated_at,settings')->where($where)
                ->limit($from, $size)
                ->select();
            if($rel){
                $data['data'] = $rel;
                $count = $this->getCount($where);
                $data['count'] = $count ? $count : 0;
                $data['pagesize'] = $size;
                $data['current_no'] = isset($condition['current_no']) ? intval($condition['current_no']) : 1;
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
            return $this->where($where)
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
            return $this->where(['id'=>$id,'deleted_at'=>['exp', 'is null']])->find();
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
                'name' => trim($input['name']),
                'lang' => (isset($input['lang']) && in_array($input['lang'],['zh','en','ru','es'])) ? $input['lang'] : 'en',
                'country_bn' => isset($input['country_bn']) ? $input['country_bn'] : null,
                'remark' => isset($input['remark']) ? trim($input['remark']) : '',
                'type' => (isset($input['type']) && $input['type']==1) ? 1 : 0,
                'settings' => (isset($input['settings']) && (is_array($input['settings']) || is_object($input['settings']))) ? json_encode($input['settings'],320) : '',
                'show_flag' => (isset($input['show_flag']) && (trim($input['show_flag'])=='Y' || trim($input['show_flag'])===true || trim($input['show_flag'])==1)) ? 'Y' : 'N',
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'name' => $data['name'],
                'lang' => $data['lang'],
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
            $lang = (isset($input['lang']) && in_array($input['lang'],['zh','en','ru','es'])) ? $input['lang'] : 'en';
            if(!$id){
                jsonReturn('', MSG::MSG_FAILED,'没有id');
            }
            $data = [
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'id' => ['neq', $id],
                'lang' => $lang,
                'deleted_at' => ['exp', 'is null']
            ];
            if(isset($input['name'])){
                $data['name'] = trim($input['name']);
                $where['name'] = $data['name'];
            }
            if(isset($input['lang'])){
                $data['lang'] = trim($input['lang']);
                $where['lang'] = $data['lang'];
            }
            if(isset($input['name']) && self::exist($where)){
                jsonReturn('', MSG::MSG_FAILED,'已经存在');
            }
            if(isset($input['country_bn'])){
                $data['country_bn'] = trim($input['country_bn']);
            }
            if(isset($input['remark'])){
                $data['remark'] = trim($input['remark']);
            }
            if(isset($input['type'])){
                $data['type'] = (isset($input['type']) && $input['type']==1) ? 1 : 0;
            }
            if(isset($input['show_flag'])){
                $data['show_flag'] = (isset($input['show_flag']) && (trim($input['show_flag'])=='Y' || trim($input['show_flag'])===true || trim($input['show_flag'])==1)) ? 'Y' : 'N';
            }
            if(isset($input['sort_order'])){
                $data['sort_order'] = intval($input['sort_order']);
            }
            if(isset($input['status']) && in_array(strtoupper($input['status']),['VALID','CHECKING','INVALID','CLOSED'])){
                $data['status'] = strtoupper($input['status']);
            }
            if(isset($input['settings'])){
                $data['settings'] = (isset($input['settings']) && (is_array($input['settings']) || is_object($input['settings']))) ? json_encode($input['settings'],320) : '';
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
