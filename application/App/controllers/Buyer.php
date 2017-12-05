<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class BuyerController extends PublicController {

    public function __init() {
        parent::__init();
    }

    /*
     * 用户列表
     * */

    public function listAction() {

        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['keyword'])) {

            $keyword = trim($data['keyword']);

            //国家
            $Country = new CountryModel();
            $isCountry = $Country->where(['name'=>$keyword])->find();
            if ($isCountry){
                $where['country_bn'] = $isCountry['bn'];
            }

            //客户编码
            $isBuyerNo = strstr($keyword,"C2");
            if ($isBuyerNo){
                $where['buyer_no'] = $keyword;
            }

            if (!$isCountry && !$isBuyerNo){
                $where['name'] = $keyword;
            }

        }

        $where['currentPage'] = !empty($data['currentPage']) ? $data['currentPage']:1;
        $where['pageSize'] = !empty($data['pageSize']) ? $data['pageSize']:10;

        $model = new BuyerModel();

        $data = $model->getlist($where);

        $this->_setArea($data, 'area');
        $this->_setCountry($data, 'country');

        if (!empty($data)) {

            $buyerList = [];
            foreach ($data as $key=>$value){
                $buyerList[$key] = [
                    'id'         => $value['id'],
                    'buyer_code'  => $value['buyer_code'],
                    'buyer_no'   => $value['buyer_no'],
                    'name'       => $value['name'],
                    'country_name' => $value['country_name'],
                    'country_bn' => $value['country_bn'],
                    'area_bn' => $value['area_bn'],
                    'created_by' => $value['created_by'],
                ];
            }

            $datajson['code'] = 1;
            $datajson['count'] = $model->getCount($where);
            $datajson['data'] = $buyerList;
        } else {
            $datajson['code'] = -1;
            $datajson['data'] = null;
            $datajson['message'] = '没有数据!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * Description of 获取营销区域
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setArea(&$arr, $filed) {
        if ($arr) {
            $marketarea_model = new MarketAreaModel();
            $bns = [];
            foreach ($arr as $key => $val) {
                $bns[] = trim($val[$filed . '_bn']);
            }
            $area_names = $marketarea_model->getNamesBybns($bns);
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($area_names[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $area_names[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
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

    private function _setCountry(&$arr, $filed) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val[$filed . '_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($countrynames[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $countrynames[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
