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
class SpecialKeywordModel extends PublicModel {
    protected $dbName = 'erui_mall';
    protected $tableName = 'special_keyword';

    public function __construct() {
        parent::__construct();
    }

    public function getList($condition){
        $where =['deleted_at'=>['exp', 'is null']];
        if(isset($condition['special_id'])){
            $where['special_id'] = intval($condition['special_id']);
        }
        if(isset($condition['keyword'])){
            $where['keyword'] = ['like', '%' . trim($condition['keyword']) . '%'];
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
            $rel = $this->field('id,special_id,keyword,description,created_at,created_by,updated_by,updated_at,settings')->where($where)
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
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(!isset($input['keyword']) || empty($input['keyword'])){
            jsonReturn('', MSG::MSG_FAILED,'请输入名称');
        }
        try{
            $data = [
                'special_id' => intval($input['special_id']),
                'keyword' =>$input['keyword'],
                'description' => isset($input['description']) ? trim($input['description']) : '',
                'settings' => (isset($input['settings']) && (is_array($input['settings']) || is_object($input['settings']))) ? json_encode($input['settings'],320) : '',
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'special_id' => $data['special_id'],
                'keyword' => $data['keyword'],
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
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(!isset($input['keyword']) || empty($input['keyword'])){
            jsonReturn('', MSG::MSG_FAILED,'请输入关键词');
        }
        try{
            $id = isset($input['id']) ? intval($input['id']) : 0;
            if(!$id){
                jsonReturn('', MSG::MSG_FAILED,'没有id');
            }
            $data = [
                'special_id' => intval($input['special_id']),
                'keyword' => trim($input['keyword']),
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'id' => ['neq', $id],
                'special_id' => intval($input['special_id']),
                'keyword' => trim($input['keyword']),
                'deleted_at' => ['exp', 'is null']
            ];
            if(self::exist($where)){
                jsonReturn('', MSG::MSG_FAILED,'已经存在');
            }
            if(isset($input['description'])){
                $data['description'] = trim($input['description']);
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
