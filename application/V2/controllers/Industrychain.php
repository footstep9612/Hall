<?php

/**
 * name: Industrychain.php
 * desc: 产业链控制器
 */
class IndustrychainController extends PublicController {
    public function __init() {
           parent::init();
    }
    //上下游获取添加数据
    public function createChainAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new IndustrychainModel();
//        $res = $model->createChain($data);
        $res = $model->updateChain($data);
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
        if(empty($res['up'])){
            $res['up']=[array(
                    'industry_group'=>'up', //上游
                    'name'=>null, //上游客户名称
                    'cooperation'=>null, //客户合作情况
                    'business_type'=>null, //业务的类型
                    'scale'=>null, //客户的规模
                    'settlement'=>null, //结算方式
                    'marketing_network'=>null, //营销网络
//                    'buyer_type_name'=>null, //客户类型
                    'buyer_project'=>null, //客户参与的项目
                    'buyer_problem'=>null, //客户遇到的困难
                    'solve_problem'=>null, //如何解决困难
                )];
        }
        if(empty($res['down'])){
            $res['down']=[array(
                    'industry_group'=>'down', //下游
                    'name'=>null, //客户名称
                    'cooperation'=>null, //客户合作情况
                    'goods'=>null, //客户类型
                    'profile'=>null, //供应商信息
                    'settlement'=>null, //结算方式
                    'warranty_terms'=>null, //质保条款
                    'relationship'=>null, //供应商与客户关系如何
                    'analyse'=>null, //与KERUI/ERUI的对标分析
                    'dynamic'=>null, //供应商动态
                )];
        }
        if(empty($res['competitor'])){
            $res['competitor']=[array(
                    'industry_group'=>'competitor', //供应商信息
                    'competitor_name'=>null, //结算方式
                    'competitor_area'=>null, //质保条款
                    'company_compare'=>null, //供应商与客户关系如何
                    'what_plan'=>null, //与KERUI/ERUI的对标分析
                )];
        }
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $res;
        $this -> jsonReturn($dataJson);
    }
    public function industryChainListAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new IndustrychainModel();
        $res = $model->industryChainList($data);
        if($res==false){
            $dataJson['code'] = 0;
            $dataJson['message'] = '参数错误';
        }
        if(empty($res['up'])){
            $res['up']=[array(
                'industry_group'=>'up', //上游
                'name'=>null, //上游客户名称
                'cooperation'=>null, //客户合作情况
                'business_type'=>null, //业务的类型
                'scale'=>null, //客户的规模
                'settlement'=>null, //结算方式
                'marketing_network'=>null, //营销网络
//                    'buyer_type_name'=>null, //客户类型
                'buyer_project'=>null, //客户参与的项目
                'buyer_problem'=>null, //客户遇到的困难
                'solve_problem'=>null, //如何解决困难
            )];
        }
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $res;
        $this -> jsonReturn($dataJson);
    }
}