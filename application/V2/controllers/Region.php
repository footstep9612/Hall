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

    }

    private function _init() {
        parent::init();
    }

    /*
     * 所有区域
     */

    public function listAction() {
        $data = $this->getPut();
        $data['lang'] = $this->getPut('lang', 'zh');
        $region_model = new RegionModel();
        $arr = $region_model->getlist($data);

        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }
    //营销区域列表
    public function areaListAction(){
        $data['lang'] = $this->getLang();
        $lang=!empty($data['lang'])?$data['lang']:'zh';
        $area=new CountryModel();
        $info=$area->table('erui_operation.market_area')
            ->field('bn as area_bn,name as area_name')
            ->where(array('lang'=>$lang,'deleted_flag'=>'N'))->select();
        $dataJson['code'] = 1;
        $dataJson['message'] = '营销区域地区数据';
        $dataJson['data'] = $info;
        $this->jsonReturn($dataJson);
    }

}
