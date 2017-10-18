<?php

class BizdivitionController extends PublicController{

    //请求参数
    private $requestParams = [];

    public function init(){

        parent::init();

        $this->requestParams = json_decode(file_get_contents("php://input"), true);

    }

    public function rejectToMarket(){

    }

    public function rejectToErui(){

    }

    public function assignQuoter(){

    }

}
