<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/18
 * Time: 15:41
 */
class ConsultsModel extends PublicModel
{
    protected $tableName = 'consults';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct()
    {
        parent::__construct();
    }

    public function addInfo($data=[]){
        if(empty($data)){
            return false;
        }

        $data = $this->create($data);
        if(!isset($data['country_bn']) || empty($data['country_bn'])){
            jsonReturn(false,Msg::MSG_FAILED,'country_bn not null');
        }
        $data['country_bn'] = ucfirst($data['country_bn']);
        $data['type'] = isset($data['type']) ? strtoupper($data['type']) : 'PRODUCT';
        if($data['type']=='PRODUCT' && !isset($data['spu'])){
            jsonReturn(false,Msg::MSG_FAILED,'spu not null');
        }
        if(empty($data['content'])){
            jsonReturn(false,Msg::MSG_FAILED,'content not null');
        }
        if(!isset($data['buyer_id'])){
            $data['buyer_id'] = null;
        }
        $data['created_at'] = date('Y-m-d H:i:s',time());
        $id = $this->add($data);
        return $id ? $id : false;
    }
}