<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeeType
 * @author  zhongyg
 * @date    2017-8-1 17:34:40
 * @version V2.0
 * @desc   
 */
class SupervisedcriteriaController extends PublicController {

    //put your code here
    public function init() {
        //parent::init();
    }

    /*
     * 监管条件
     */

    public function listAction() {
        $data = $this->get();
        $shipowner_clause_model = new SupervisedCriteriaModel();
        if (redisGet('SupervisedCriteria_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('SupervisedCriteria' . md5(json_encode($data))), true);
        } else {
            $arr = $shipowner_clause_model->getlist($data);
            if ($arr) {
                redisSet('SupervisedCriteria_' . md5(json_encode($data)), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

}
