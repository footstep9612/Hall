<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Region
 * @author  zhongyg
 * @date    2017-8-1 16:16:28
 * @version V2.0
 * @desc   区域
 */
class RegionController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 所有区域
     */

    public function listAction() {
        $data = $this->get();
        unset($data['token']);
        $region_model = new RegionModel();
        if (redisGet('Region_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Region_' . md5(json_encode($data))), true);
        } else {
            $arr = $region_model->getlist($data);
            if ($arr) {
                redisSet('Region_' . md5(json_encode($data)), json_encode($arr));
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
