<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ChargeUnit
 * @author  zhongyg
 * @date    2017-8-1 17:32:49
 * @version V2.0
 * @desc   
 */
class ChargeunitController extends PublicController {

    //put your code here
    public function init() {
        // parent::init();
    }

    /*
     * 所有计费单位
     */

    public function listAction() {
        $data = $this->get();
        $data['lang'] = $this->get('lang', 'zh');
        $charge_unit_model = new ChargeUnitModel();
        if (redisGet('ChargeUnit_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('ChargeUnit_' . md5(json_encode($data))), true);
        } else {
            $arr = $charge_unit_model->getlist($data);
            if ($arr) {
                redisSet('ChargeUnit_' . md5(json_encode($data)), json_encode($arr));
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
