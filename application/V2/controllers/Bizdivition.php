<?php

/**
 * 事业部分单员相关接口
 * @desc   BizdivitionController
 * @Author 买买提
 */
class BizdivitionController extends PublicController{

    //请求参数
    private $requestParams = [];

    public function init(){

        parent::init();

        $this->requestParams = json_decode(file_get_contents("php://input"), true);

    }

    public function rejectToMarket(){

        //$request = $this->validateRequests('inquiry_id');

    }

    public function rejectToErui(){

    }

    public function assignQuoter(){

    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params=''){

        $request = $this->requestParams;
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params){
            $params = explode(',',$params);
            foreach ($params as $param){
                if (empty($request[$param])) $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
            }
        }

        return $request;

    }

}
