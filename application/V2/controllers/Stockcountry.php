<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货国家
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class StockcountryController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取现货国家列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function ListAction() {

        $condition = $this->getPut();
        $stock_country_model = new StockCountryModel();
        $lang = isset($condition['lang']) ? $condition['lang'] : 'zh';
        $list = $stock_country_model->getList($condition);

        if ($list) {
            $this->_setCountry($list, $lang);
            $count = $stock_country_model->getCount($condition, $lang);
            $this->setvalue('count', $count);
            $this->setvalue('current_no', isset($condition['current_no']) ? intval($condition['current_no']) : 1);
            $this->setvalue('pagesize', isset($condition['pagesize']) ? intval($condition['pagesize']) : 10);
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr, $lang = 'zh') {
        if ($arr) {
            $country_model = new CountryModel();
            $market_area_country_model = new MarketAreaCountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, $lang);
            $market_areas = $market_area_country_model->getAreasBybns($country_bns, $lang);
            foreach ($arr as $key => $val) {

                if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                    $val['country_name'] = $countrynames[trim($val['country_bn'])];
                } else {
                    $val['country_name'] = '';
                }

                if (trim($val['country_bn']) && isset($market_areas[trim($val['country_bn'])])) {
                    $val['market_area_name'] = $market_areas[trim($val['country_bn'])]['market_area_name'];
                    $val['market_area_bn'] = $market_areas[trim($val['country_bn'])]['market_area_bn'];
                } else {
                    $val['market_area_name'] = '';
                    $val['market_area_bn'] = '';
                }
                jsonDecode($val,'settings');    //json解析
                ynTotruefalse($val,'show_flag');
                $arr[$key] = $val;
            }
        }
    }

    /**
     * Description of 获取现货国家详情
     * @author  Link
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function InfoAction() {
        $condition = $this->getPut();
        $stock_country_model = new StockCountryModel();
        $list = $stock_country_model->getInfo($condition);
        jsonDecode($list,'settings');    //json解析
        if ($list) {
            $scadsModel = new StockCountryAdsModel();
            $ads = $scadsModel->getList(['country_bn'=>$list['country_bn'], 'deleted_at'=>['exp','is null']]);
            if($ads){
                foreach($ads as $r){
                    $list['ads'][$r['group']][] = $r;
                }
            }else{
                $list['ads'] = [];
            }

            jsonReturn($list);
        } else {
            jsonReturn('' , MSG::MSG_FAILED, '系统错误!' );
        }
    }

    /**
     * Description of 新加现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function CreateAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $show_type = $this->getPut('show_type');
        $stock_country_model = new StockCountryModel();
        $lang = $this->getPut('lang', 'en');
        if ($stock_country_model->getExit($country_bn, $lang, null, $show_type)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择国家已经存在,请您重新选择!');
            $this->jsonReturn();
        }

        $show_flag = $this->getPut('show_flag', 'N');
        $display_position = $this->getPut('display_position');
        $settings = $this->getPut('settings', '');
        $settings = $settings ? json_encode($settings,JSON_UNESCAPED_UNICODE):'{}';

        $list = $stock_country_model->createData($country_bn, $show_flag, $lang, $display_position, $show_type,$settings);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货国家
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function UpdateAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('ID不能为空!');
            $this->jsonReturn();
        }
        if(count($this->getPut())<2){
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('无修改信息!');
            $this->jsonReturn();
        }
        $country_bn = $this->getPut('country_bn','');
        $show_type = $this->getPut('show_type');
        $lang = $this->getPut('lang', 'en');

        try{
            $stock_country_model = new StockCountryModel();
            if (!empty($country_bn)) {
                if ($stock_country_model->getExit($country_bn, $lang, $id, $show_type)) {
                    $this->setCode(MSG::MSG_EXIST);
                    $this->setMessage('您选择国家已经存在,请您重新选择!');
                    $this->jsonReturn();
                }
            }

            $show_flag = $this->getPut('show_flag', 'N');
            $display_position = $this->getPut('display_position');
            $settings = $this->getPut('settings', '');
            $settings = $settings ? json_encode($settings,JSON_UNESCAPED_UNICODE):'';
            $ads = $this->getPut('ads');
            $stock_country_model->startTrans();
            if($ads){
                $scInfo = $stock_country_model->getInfo(['id'=>$id]);
                $scadsModel = new StockCountryAdsModel();
                foreach($ads as $key=>$v){
                    foreach($v as $k=>$r){
                        if(isset($r['id']) && $r['id']!=''){
                            $result = $scadsModel->updateData($r['id'], $scInfo['country_bn'], isset($r['img_name']) ? $r['img_name'] : '', isset($r['img_url']) ? $r['img_url'] : '',  isset($r['link']) ? $r['link'] : '',  isset($r['group']) ? $r['group'] : '',$scInfo['lang']);
                            if(!$result){
                                $stock_country_model->rollback();
                                jsonReturn('', MSG::MSG_FAILED, '操作失败！');
                            }
                        }else{
                            $result = $scadsModel->createData($scInfo['country_bn'], isset($r['img_name']) ? $r['img_name'] : '', isset($r['img_url']) ? $r['img_url'] : '',  isset($r['link']) ? $r['link'] : '',  isset($r['group']) ? $r['group'] : '',$scInfo['lang']);
                            if(!$result){
                                $stock_country_model->rollback();
                                jsonReturn('', MSG::MSG_FAILED, '操作失败！');
                            }
                        }
                    }
                }
            }

            $list = $stock_country_model->updateData($id, $country_bn, $show_flag, $lang, $display_position, $show_type,$settings);
            if ($list) {
                $stock_country_model->commit();
                $this->jsonReturn($list);
            } elseif ($list === false) {
                $stock_country_model->rollback();
                $this->setCode(MSG::ERROR_EMPTY);
                $this->setMessage('更新失败!');
                $this->jsonReturn(null);
            } else {
                $stock_country_model->rollback();
                $this->setCode(MSG::MSG_FAILED);
                $this->setMessage('系统错误!');
                $this->jsonReturn();
            }
        }catch (Exception $e){
            $stock_country_model->rollback();
            jsonReturn('', MSG::MSG_FAILED, '操作失败！');
        }
    }

}
