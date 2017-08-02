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
class ShipownerClauseController extends PublicController {

    //put your code here
    public function init() {
        //parent::init();
    }

    /*
     * 所有船东条款
     */

    public function listAction() {
        $data = $this->get();
        $data['lang'] = $this->get('lang', 'zh');

        $shipowner_clause_model = new ShipownerClauseModel();
        if (redisGet('ShipownerClause_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('ShipownerClause_' . md5(json_encode($data))), true);
        } else {
            $arr = $shipowner_clause_model->getlist($data);
            if ($arr) {
                redisSet('ShipownerClause_' . md5(json_encode($data)), json_encode($arr));
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
