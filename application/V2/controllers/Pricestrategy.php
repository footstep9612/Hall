<?php
/**
 * 价格策略
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:29
 */
class PricestrategyController extends PublicController {
    public function init() {
        parent::init();
    }

    /**
     * 列表
     * @author link
     * @param storage_name|id
     */
    public function listAction(){
        $condition = $this->getPut();
        if(!isset($condition['lang'])){
            jsonReturn('', MSG::ERROR_PARAM, '请选择语言！');
        }
        $psModel = new PriceStrategyModel();
        $result = $psModel->getList($condition);

        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('',MSG::MSG_FAILED);
        }
    }
}