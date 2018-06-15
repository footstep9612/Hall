<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/5/16
 * Time: 21:37
 */
class SpecialCategoryModel extends Model{
    protected $tableName = 'special_category';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据id获取详情
     * @param $id
     * @return bool|mixed
     */
    public function getInfo($input=[],$field=''){
        if(!isset($input['id']) || !is_numeric($input['id'])){
            jsonReturn('', MSG::MSG_FAILED,'分类id不存在');
        }
        try{
            $field = empty($field) ? 'id,cat_name,thumb,pid,haschild,settings,description,allpid' : $field;
            return $this->field("$field")->where(['id'=>$input['id'],'deleted_at'=>['exp', 'is null']])->find();
        }catch (Exception $e){
            return false;
        }
    }

    public function getList($input=[]){
        if(!isset($input['special_id'])){
            jsonReturn('',MSG::MSG_FAILED,'请选择专题');
        }
        try{
            if(isset($input['category_id'])){
                $condition = [
                    'special_id' => intval($input['special_id']),
                    'pid' => intval($input['category_id']),
                    'deleted_at' => ['exp', 'is null']
                ];
            }elseif(isset($input['all'])){
                $condition = [
                    'special_id' => intval($input['special_id']),
                    'deleted_at' => ['exp', 'is null']
                ];
            }else{
                $condition = [
                    'special_id' => intval($input['special_id']),
                    'pid' => 0,
                    'deleted_at' => ['exp', 'is null']
                ];
            }

            $result = $this->field('id,cat_name,thumb,pid,haschild,settings,description')->where($condition)->order('sort_order DESC')->select();
            return $result;
        }catch (Exception $e){
            return false;
        }
    }
}