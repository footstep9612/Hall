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





        if(empty($input['id'])){
            jsonReturn('',MSG::MSG_FAILED, 'Id不能为空');
        }
        try{
            $data = [];
            foreach($input as $k=>$v){
                if(in_array($k,['storage_id','spu','sku','to_country_bn','to_city','cycle'])){
                    $v = (trim($k)=='to_country_bn') ? ucfirst(trim($v)) : trim($v);
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