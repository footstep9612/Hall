<?php
/**
 * 国家联系方式
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/5/2
 * Time: 10:20
 */
class CountryContactModel extends PublicModel{
    protected $dbName = 'erui_mall';
    protected $tableName = 'country_contact';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 新增
     * @param $input
     * @return bool|mixed
     */
    public function addData($input){
        if(!isset($input['country_bn']) || empty($input['country_bn'])){
            jsonReturn('',MSG::ERROR_PARAM,'country_bn 不能为空');
        }
        if((!isset($input['tel']) && !isset($input['email'])) || (empty($input['tel']) && empty($input['email']))){
            jsonReturn('',MSG::ERROR_PARAM,'联系方式不能为空');
        }
        try{
            $data =[
                'country_bn' => ucfirst(trim($input['country_bn'])),
                'tel' => isset($input['tel']) ? trim($input['tel']) : '',
                'email' => isset($input['email']) ? trim($input['email']) : '',
            ];
            if($this->isExist($data)!==false){
                jsonReturn('',MSG::ERROR_PARAM,'已经存在');
            }
            $data['contact'] = isset($input['contact']) ? trim($input['contact']) : '';
            $data['department'] = isset($input['department']) ? trim($input['department']) : '';
            $data['position'] = isset($input['position']) ? trim($input['position']) : '';
            $data['created_at'] = date('Y-m-d H:i:s',time());
            $data['created_by'] = defined('UID') ? UID : 0;
            $id = $this->add($data);

            return $id ? $id : false;
        }catch (Exception $e){

            jsonReturn($this->getLastSql());
            return false;
        }
    }

    /**
     * 更新
     * @param $input
     * @return bool
     */
    public function updateData($input){
        if((!isset($input['country_bn']) && !isset($input['id'])) || (empty($input['country_bn']) && empty($input['id']))){
            jsonReturn('',MSG::ERROR_PARAM,'id或country_bn 不能为空');
        }
        try{
            $data = [];
            foreach($input as $k=>$v){
                if(in_array($k,['country_bn','tel','email','contact','department','position','listorder'])){
                    $data[$k] = ($k=='country_bn') ? ucfirst(trim($v)) : trim($v);
                }
            }
            $where = [];
            if($input['country_bn']){
                $where['country_bn']= ucfirst(trim($input['country_bn']));
            }
            if($input['id']){
                $where['id']= trim($input['id']);
            }
            $data['updated_at'] = date('Y-m-d H:i:s',time());
            $data['updated_by'] = defined('UID') ? UID : 0;
            $id = $this->where($where)->save($data);
            return $id ? $id : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @param $input
     * @return bool
     */
    public function deleteData($input){
        if((!isset($input['country_bn']) && !isset($input['id'])) || (empty($input['country_bn']) && empty($input['id']))){
            jsonReturn('',MSG::ERROR_PARAM,'id或country_bn 不能为空');
        }
        try{
            $where = [
                'deleted_at' => ['exp', 'is null']
            ];
            if($input['country_bn']){
                $where['country_bn']= ucfirst(trim($input['country_bn']));
            }
            if($input['id']){
                if(is_array($input['id'])){
                    $where['id'] = ['in',$input['id']];
                }else{
                    $where['id'] = $input['id'];
                }
            }
            if(empty($where)){
                jsonReturn('',MSG::ERROR_PARAM,'条件为空');
            }
            $data = [];
            $data['deleted_at'] = date('Y-m-d H:i:s',time());
            $id = $this->where($where)->save($data);
            return $id ? $id : false;
        }catch (Exception $e){
            return false;
        }
    }

    public function isExist($condition=[]){
        $condition['deleted_at'] = ['exp', 'is null'];
        try{
            $result = $this->field('id')->where($condition)->find();
            return $result ? true : false;
        }catch (Exception $e){
            return ['code'=>0, 'error'=>$e];
        }
    }
}