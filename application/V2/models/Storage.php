<?php
/**
 * 仓库管理
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:50
 */
class StorageModel extends PublicModel{
    //put your code here
    protected $tableName = 'storage';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 新增仓库
     * @param $input
     * @return bool|mixed
     */
    public function createData($input){
        if(empty($input['country_bn']) || empty($input['storage_name'])){
            return false;
        }
        try{
            $data =[
                'country_bn' => ucfirst(trim($input['country_bn'])),
                'storage_name' => trim($input['storage_name']),
                'keyword' => trim($input['keyword']),
                'description' => trim($input['description']),
                'remark' => trim($input['remark']),
                'content' => trim($input['content']),
                'contact' => $input['contact'] ? json_encode($input['contact'],JSON_UNESCAPED_UNICODE) : ''
            ];
            if($this->getExit(['country_bn'=>$data['country_bn'],'storage_name'=>$data['storage_name']])===false){
                $data['created_at'] = date('Y-m-d H:i:s',time());
                $data['created_by'] = defined('UID') ? UID : 0;
                $flag = $this->add($data);
                return $flag ? $flag : false;
            }
        }catch (Exception $e){
            return false;
        }

    }

    /**
     * 更新仓库
     * @param $input
     * @return bool|mixed
     */
    public function updateData($input){
        if(empty($input['id'])){
            jsonReturn('',MSG::MSG_FAILED, 'Id不能为空');
        }
        try{
            $data = [];
            foreach($input as $k=>$v){
                if($k=='contact'){
                    $v = $v ? json_encode($v,JSON_UNESCAPED_UNICODE ) : '';
                }
                if(in_array($k,['country_bn','storage_name','keyword','description','remark','content','status','contact'])){
                    $v = (trim($k)=='country_bn') ? ucfirst(trim($v)) : trim($v);
                    $data[$k] = $v;
                }
            }
            if(empty($data)){
               return false;
            }
            $data['updated_at'] = date('Y-m-d H:i:s',time());
            $data['updated_by'] = defined('UID') ? UID : 0;

            $where =['id'=>$input['id']];
            $flag = $this->where($where)->save($data);
            unset($data);
            return $flag ? $flag : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除仓库
     * @param $input
     * @return bool|mixed
     */
    public function deleteData($input){
        if(empty($input['id'])){
            jsonReturn('',MSG::MSG_FAILED, 'Id不能为空');
        }
        try{
            $data = [];
            $data['deleted_at'] = date('Y-m-d H:i:s',time());
            $data['deleted_by'] = defined('UID') ? UID : 0;

            $where =['id'=>$input['id']];
            $flag = $this->where($where)->save($data);
            return $flag ? $flag : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 检测是否存在
     * @param array $where
     * @return bool|mixed
     */
    public function getExit($where=[]){
        try{
            $result = $this->field('id')->where($where)->find();
            return $result ? true : false;
        }catch (Exception $e){
            return ['code'=>0, 'error'=>$e];
        }
    }
}