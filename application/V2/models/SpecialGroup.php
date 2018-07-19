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
class SpecialGroupModel extends PublicModel {
    protected $dbName = 'erui_mall';
    protected $tableName = 'special_group';

    public function __construct() {
        parent::__construct();
    }

    public function getList($condition){
        $where =['deleted_at'=>['exp', 'is null']];
        if(isset($condition['group_no'])){
            $where['group_no'] = trim($condition['group_no']);
        }
        if(isset($condition['lang'])){
            $where['lang'] = trim($condition['lang']);
        }
        if(isset($condition['group_name'])){
            $where['group_name'] = ['like', '%' . trim($condition['group_name']) . '%'];
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
            $rel = $this->field('id,group_no,group_name,sort_order,special_count,lang,show_flag,created_at,created_by,updated_by,updated_at')->where($where)->order('sort_order DESC')
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
    public function getInfo($id,$field=''){
        if(!$id || !is_numeric($id)){
            jsonReturn('', MSG::MSG_FAILED,'id不存在');
        }
        try{
            $field = empty($field) ? 'id,group_no,group_name,sort_order,special_count,lang,show_flag,created_at,created_by,updated_by,updated_at' : $field;
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
        if(!isset($input['group_name']) || empty($input['group_name'])){
            jsonReturn('', MSG::MSG_FAILED,'请输入名称');
        }
        if(!isset($input['lang']) || !in_array(trim($input['lang']),['zh','en','es','ru'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择语言');
        }
        try{
            $no = $this->_getNo();
            if($no === false){
                jsonReturn('', MSG::MSG_FAILED,'编码生成失败，请稍后重试');
            }
            $data = [
                'group_no' => $no,
                'group_name' => trim($input['group_name']),
                'lang' => trim($input['lang']),
                'show_flag' => (isset($input['show_flag']) && (trim($input['show_flag'])=='Y' || trim($input['show_flag'])===true || trim($input['show_flag'])==1)) ? 'Y' : 'N',
                'sort_order' => isset($input['sort_order']) ? intval($input['sort_order']) : 0,
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'lang' => $data['lang'],
                'group_name' => $data['group_name'],
                'deleted_at' => ['exp', 'is null']
            ];
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
        if(!isset($input['id']) || empty($input['id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择品类');
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
            $where = [
                'id' => ['neq', $id],
                'deleted_at' => ['exp', 'is null']
            ];
            if(isset($input['group_name'])){
                $data['group_name'] = trim($input['group_name']);
                $where['group_name'] = trim($input['group_name']);
            }
            if(isset($input['lang'])){
                $data['lang'] = trim($input['lang']);
                $where['lang'] = trim($input['lang']);
            }
            if(isset($input['group_name']) && self::exist($where)){
                jsonReturn('', MSG::MSG_FAILED,'已经存在');
            }

            if(isset($input['show_flag'])){
                $data['show_flag'] = (isset($input['show_flag']) && (trim($input['show_flag'])=='Y' || trim($input['show_flag'])===true || trim($input['show_flag'])==1)) ? 'Y' : 'N';
            }
            if(isset($input['special_count'])){
                $data['special_count'] = intval($input['special_count']);
            }
            if(isset($input['sort_order'])){
                $data['sort_order'] = intval($input['sort_order']);
            }
            $rel = $this->where(['id' => $id, 'deleted_at' => ['exp','is null']])->save($data);
            return $rel ? $rel : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 排序
     * @param array $input
     * @return bool
     */
    public function sortOrder($input=[]){
        if(empty($input)){
            jsonReturn('', MSG::MSG_FAILED,'没有信息');
        }
        if(!isset($input['id']) || empty($input['id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择品类');
        }
        if(!isset($input['sort_order'])){
            jsonReturn('', MSG::MSG_FAILED,'请输入排序数');
        }
        try{
            $data = [
                'sort_order' => intval($input['sort_order']),
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];

            $rel = $this->where(['id' => intval($input['id'])])->save($data);
            return $rel ? $rel : false;
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

    /**
     * 获取编码
     * @return bool|int|string
     */
    protected function _getNo(){
        try{
            $groupInfo = $this->field('group_no')->order('group_no DESC')->find();
            if($groupInfo){
                return intval($groupInfo['group_no'])+1;
            }else{
                return '10000000';
            }
        }catch (Exception $e){
            return false;
        }
    }
}
