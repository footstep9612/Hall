<?php

/**
 * name: Industrychain.php
 * desc: 产业链控制器
 */
class IndustrychainController extends PublicController {
    public function __init() {
           parent::__init();
    }
    //上下游获取添加数据
    public function createChainAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new IndustrychainModel();
        $res = $model->createChain($data);
        if($res !== false && $res !== true && $res !== 'nullData'){    //返回验证错误提示信息
            $valid = array(
                'code'=>0,
                'message'=>$res.'数据过长'
            );
        }elseif($res===false){
            $valid = array(
                'code'=>0,
                'message'=>'上下游失败'
            );
        }elseif ($res === 'nullData'){
            $valid = array(
                'code'=>1,
                'message'=>'上下游为空数据'
            );
        }else{
            $valid = array(
                'code'=>1,
                'message'=>'上下游数据成功'
            );
        }
        $this -> jsonReturn($valid);
    }
    //上下游数据详情
    public function chainListAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new IndustrychainModel();
        $res = $model->chainList($data['buyer_id'],$created_by);
        $dataJson = array(
            'code'=>1,
            'message'=>'返回数据',
            'data'=>$res,
        );
        $this->jsonReturn($dataJson);
    }
}