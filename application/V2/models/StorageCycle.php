<?php
/**
 * 仓库物流时效管理
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:50
 */
class StorageCycleModel extends PublicModel{
    //put your code here
    protected $tableName = 'storage_cycle';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 新增
     * @param $input
     * @return bool|mixed
     */
    public function createData($input){
        if(empty($input['storage_id']) ||  empty($input['data'])){
            return false;
        }
        try{
            $storageModel = new StorageModel();
            $storageInfo = $storageModel->getInfo(['id'=>$input['storage_id']]);
            if(!$storageInfo){
                jsonReturn('', MSG::MSG_FAILED, '仓库不存在');
            }

            $dataAll = [];
            $exitAry = [];
            foreach($input['data'] as $key=>$r){
                $data = [
                    'storage_id' => intval($input['storage_id']),
                    'spu' => isset($r['spu']) ? trim($r['spu']) : '',
                    'sku' => isset($r['sku']) ? trim($r['sku']) : '',
                    'to_country_bn' => isset($r['to_country_bn']) ? ucfirst(trim($r['to_country_bn'])) : '',
                    'to_city' => trim($r['to_city']),
                ];
                if($data['to_country_bn']==''){
                    $data['to_country_bn'] = $storageInfo['country_bn'];
                }
                if($this->getExit($data)===false && !in_array(md5(json_encode($data)),$exitAry)){
                    $exitAry[] = md5(json_encode($data));
                    $data['cycle'] = trim($r['cycle']);
                    $data['created_at'] = date('Y-m-d H:i:s',time());
                    $data['created_by'] = defined('UID') ? UID : 0;
                    $dataAll[] = $data;
                }
                unset($data);
            }
            $flag = $this->addAll($dataAll);
            return $flag ? $flag : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 更新
     * @param $input
     * @return bool|mixed
     */
    public function updateData($input){
        if(empty($input['storage_id']) ||  empty($input['data'])){
            return false;
        }
        try{
            $storageModel = new StorageModel();
            $storageInfo = $storageModel->getInfo(['id'=>$input['storage_id']]);
            if(!$storageInfo){
                jsonReturn('', MSG::MSG_FAILED, '仓库不存在');
            }

            foreach($input['data'] as $key=>$r){
                if(empty($r['id'])){
                    continue;
                }
                $data = [
                    'storage_id' => intval($input['storage_id']),
                    'spu' => isset($r['spu']) ? trim($r['spu']) : '',
                    'sku' => isset($r['sku']) ? trim($r['sku']) : '',
                    'to_country_bn' => isset($r['to_country_bn']) ? ucfirst(trim($r['to_country_bn'])) : '',
                    'to_city' => trim($r['to_city']),
                ];
                if($data['to_country_bn']==''){
                    $data['to_country_bn'] = $storageInfo['country_bn'];
                }
                $data['id'] = ['neq',$r['id']];
                if($this->getExit($data)===false){
                    $data['cycle'] = trim($r['cycle']);
                    $data['updated_at'] = date('Y-m-d H:i:s',time());
                    $data['updated_by'] = defined('UID') ? UID : 0;
                    unset($data['id']);
                    $flag = $this->where(['id'=>$r['id']])->save($data);
                    return $flag ? $flag : false;
                }
                unset($data);
            }
            return false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 列表
     */
    public function getList($condition){
        if(!isset($condition['storage_id']) || empty($condition['storage_id'])){
            jsonReturn('',MSG::MSG_FAILED, '请选择仓库ID');
        }
        $where = [
            'storage_id' => intval($condition['storage_id']),
            'deleted_at' => ['exp','is null']
        ];
        if(isset($condition['to_city'])){
            $where['to_city'] = ['like','%'.$condition['to_city'].'%'];
        }
        if(isset($condition['to_country_bn'])){
            $where['to_country_bn'] = ucfirst(strtolower($condition['to_country_bn']));
        }
        try{
            $data = [];
            list($from ,$size) = $this->_getPage($condition);
            $result = $this->field('id,storage_id,to_country_bn,to_city,cycle,created_at,created_by,updated_at,updated_by')->where($where)->limit($from,$size)->select();
            if($result){
                $this->_setUser($rel);
                $data['data'] = $result;
                $count = $this->getCount($where);
                $data['count'] = $count ? $count : 0;
                $data['current_no'] = isset($condition['current_no']) ? intval($condition['current_no']) : 1;
                $data['pagesize'] = $size;
            }
            return $data ? $data : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 获取记录数
     * @param $where
     */
    public function getCount($where){
        try{
            return $this->field('id')->where($where)->count();
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
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

            if(is_array($input['id'])){
                $where =['id'=>['in', $input['id']]];
            }else{
                $where =['id'=>$input['id']];
            }
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