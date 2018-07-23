<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 专题关键词
 * @author  link
 * @date    2018-05-17 13:38:48
 * @version V2.0
 * @desc
 */
class SpecialCategoryModel extends PublicModel {
    protected $dbName = 'erui_mall';
    protected $tableName = 'special_category';

    public function __construct() {
        parent::__construct();
    }

    public function getList($condition){
        $where =['deleted_at'=>['exp', 'is null']];
        if(isset($condition['special_id'])){
            $where['special_id'] = intval($condition['special_id']);
        }
        if(isset($condition['pid'])){
            $where['pid'] = intval($condition['pid']);
        }
        if(isset($condition['cat_name'])){
            $where['cat_name'] = ['like', '%' . trim($condition['cat_name']) . '%'];
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
            $rel = $this->field('id,special_id,cat_name,pid,allpid,description,created_at,created_by,updated_by,updated_at,settings')->where($where)
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
    public function getInfo($id,$field=''){
        if(!$id || !is_numeric($id)){
            jsonReturn('', MSG::MSG_FAILED,'id不存在');
        }
        try{
            $field = empty($field) ? 'id,special_id,cat_name,thumb,pid,allpid,sort_order,settings,description,created_by,created_at,updated_by,updated_at' : $field;
            return $this->field("$field")->where(['id'=>$id,'deleted_at'=>['exp', 'is null']])->find();
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
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(!isset($input['cat_name']) || empty($input['cat_name'])){
            jsonReturn('', MSG::MSG_FAILED,'请输入名称');
        }
        try{
            $data = [
                'special_id' => intval($input['special_id']),
                'cat_name' =>$input['cat_name'],
                'thumb' => isset($input['thumb']) ? trim($input['thumb']) : '',
                'description' => isset($input['description']) ? trim($input['description']) : '',
                'sort_order' => isset($input['sort_order']) ? intval($input['sort_order']) : 0,
                'settings' => (isset($input['settings']) && (is_array($input['settings']) || is_object($input['settings']))) ? json_encode($input['settings'],320) : '',
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'special_id' => $data['special_id'],
                'cat_name' => $data['cat_name'],
                'deleted_at' => ['exp', 'is null']
            ];
            if( isset($input['pid']) && $input['pid']!==0){
                $data['pid'] = intval($input['pid']);
                //根据pid查询父级信息
                $parentInfo = $this->getInfo($data['pid']);
                if(!$parentInfo){
                    jsonReturn('', MSG::MSG_FAILED, '父级未找到');
                }
                $data['allpid'] = $parentInfo['allpid'].','.$data['pid'];
                $where['pid'] = $data['pid'];
            }
            if(!self::exist($where)){
                $id = $this->add($data);
                return $id ? $id : false;
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
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        $this->startTrans();
        try{
            $id = isset($input['id']) ? intval($input['id']) : 0;
            if(!$id){
                jsonReturn('', MSG::MSG_FAILED,'没有id');
            }
            $data = [
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            //先获取当前分类的所有父级
            $thisallpid = $this->getInfo($id,'allpid,cat_name,pid');

            $where = [
                'id' => ['neq', $id],
                'special_id' => intval($input['special_id']),
                'deleted_at' => ['exp', 'is null']
            ];
            if(isset($input['pid'])){
                $data['pid'] = intval($input['pid']);

                $where['pid'] = intval($input['pid']);
                $where['cat_name'] = $thisallpid['cat_name'];

                //根据pid查询父级信息
                $parentInfo = $this->getInfo($data['pid']);
                if(!$parentInfo){
                    jsonReturn('', MSG::MSG_FAILED, '父级未找到');
                }
                $data['allpid'] = $parentInfo['allpid'].','.$data['pid'];

                //初始化所有子节点的父级
                $this->where(['allpid'=>['like',$thisallpid['allpid'].'%']])->save(['allpid'=>['exp',"replace(allpid,'".$thisallpid['allpid']."','".$data['allpid']."')"],'updated_by'=>defined('UID') ? UID : 0,'updated_at'=>date('Y-m-d H:i:s',time())]);
            }
            if(isset($input['cat_name'])){
                if(!isset($input['pid'])){
                    $where['pid'] = $thisallpid['pid'];
                }
                $where['cat_name'] = trim($input['cat_name']);
                $data['cat_name'] = trim($input['cat_name']);
            }
            if(self::exist($where)){
                jsonReturn('', MSG::MSG_FAILED,'已经存在');
            }

            if(isset($input['description'])){
                $data['description'] = trim($input['description']);
            }
            if(isset($input['settings'])){
                $data['settings'] = (isset($input['settings']) && (is_array($input['settings']) || is_object($input['settings']))) ? json_encode($input['settings'],320) : '';
            }
            if(isset($input['thumb'])){
                $data['thumb'] = trim($input['thumb']);
            }
            if(isset($input['sort_order'])){
                $data['sort_order'] = intval($input['sort_order']);
            }
            $rel = $this->where(['id' => $id, 'deleted_at' => ['exp','is null']])->save($data);
            if($rel){
                $this->commit();
                return $rel;
            }else{
                $this->rollback();
                return false;
            }
        }catch (Exception $e){
            $this->rollback();
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
            if(isset($input['type']) && $input['type']=='UNDELETE'){
                $data = [
                    'deleted_by' => 0,
                    'deleted_at' => null
                ];
            }else{
                $data=[
                    'deleted_by' => defined('UID') ? UID : 0,
                    'deleted_at' => date('Y-m-d H:i:s', time())
                ];
            }
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
