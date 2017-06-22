<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author jhw
 */
class DictController extends Yaf_Controller_Abstract {

    public function __init() {
        //   parent::__init();
    }

    public function CountryListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['bn'])){
            $where['bn'] = $data['bn'];
        }
        if(!empty($data['name'])){
            $where['name'] = $data['name'];
        }
        if(!empty($data['time_zone'])){
            $where['time_zone'] = $data['time_zone'];
        }
        if(!empty($data['region'])){
            $where['region'] = $data['region'];
        }
        if(!empty($data['page'])){
            $limit['page'] = $data['page'];
        }
        if(!empty($data['countPerPage'])){
            $limit['num'] = $data['countPerPage'];
        }
        $lang = '';
        if(!empty($data['lang'])){
            $lang = $data['lang'];
        }
        $model_group = new CountryModel();
        if(empty($where)&&empty($limit)){
            if(!$lang){
                $lang = 'zh';
            }
            $where['lang'] = $lang;
            if(redisHashExist('CountryList',$lang)){
                $arr = json_decode(redisHashGet('CountryList',$lang),true);
            }else{
                $model_group = new CountryModel();
                $arr = $model_group->getlist($where,$limit); //($this->put_data);
                redisHashSet('CountryList', $lang, json_encode($arr));
            }

        }else{
            if(!empty($data['lang'])){
                $where['lang'] = $data['lang'];
            }
            $model_group = new CountryModel();
            $arr = $model_group->getlist($where,$limit); //($this->put_data);
        }
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        jsonReturn($datajson);
    }

    public function TradeTermsListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['page'])){
            $limit['page'] = $data['page'];
        }
        if(!empty($data['countPerPage'])){
            $limit['num'] = $data['countPerPage'];
        }
        $lang = '';
        if(!empty($data['lang'])){
            $lang = $data['lang'];
        }

        $trade_terms = new TradeTermsModel();
        if(empty($where)&&empty($limit)){
            if(!$lang){
                $lang = 'zh';
            }

            $where['lang'] = $lang;
            if(redisHashExist('TradeTermsList',$lang)){
               $arr = json_decode(redisHashGet('TradeTermsList',$lang),true);
            }else{
                $arr = $trade_terms->getlist($where,$limit); //($this->put_data);

                if($arr){
                    redisHashSet('TradeTermsList', $lang, json_encode($arr));
                }
            }
        }else{
            if(!empty($data['lang'])){
                $where['lang'] = $data['lang'];
            }
            $arr = $trade_terms->getlist($where,$limit); //($this->put_data);
        }
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        jsonReturn($datajson);
    }
    public function TransModeListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['page'])){
            $limit['page'] = $data['page'];
        }
        if(!empty($data['countPerPage'])){
            $limit['num'] = $data['countPerPage'];
        }
        $lang = '';
        if(!empty($data['lang'])){
            $lang = $data['lang'];
        }

        $trade_mode = new TransModeModel();
        if(empty($where)&&empty($limit)){
            if(!$lang){
                $lang = 'zh';
            }

            $where['lang'] = $lang;
            if(redisHashExist('TransModeList',$lang)){
                $arr = json_decode(redisHashGet('TransModeList',$lang),true);
            }else{
                $arr = $trade_mode->getlist($where,$limit); //($this->put_data);

                if($arr){
                    redisHashSet('TransModeList', $lang, json_encode($arr));
                }
            }
        }else{
            if(!empty($data['lang'])){
                $where['lang'] = $data['lang'];
            }
            $arr = $trade_mode->getlist($where,$limit); //($this->put_data);
        }
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        jsonReturn($datajson);
    }

    public function marketAreaCountryListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if(!empty($data['page'])){
            $limit['page'] = $data['page'];
        }
        if(!empty($data['countPerPage'])){
            $limit['num'] = $data['countPerPage'];
        }
        $market_area_country = new MarketAreaCountryModel();
        if(empty($where)&&empty($limit)){
            if(redisHashExist('marketAreaCountryList',$lang)){
                $arr = json_decode(redisHashGet('marketAreaCountryList',$lang),true);
            }else{
                $arr = $market_area_country->getlist($where,$limit); //($this->put_data);

                if($arr){
                    redisHashSet('marketAreaCountryList', $lang, json_encode($arr));
                }
            }
        }else{
            if(!empty($data['lang'])){
                $where['lang'] = $data['lang'];
            }
            $arr = $market_area_country->getlist($where,$limit); //($this->put_data);
        }
        if(!empty($arr)){
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        }else{
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }
        jsonReturn($datajson);
    }


    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data)){
            $datajson['code'] = -101;
            $datajson['message'] = '数据不可为空!';
            $this->jsonReturn($datajson);
        }
        $model_group = new GroupModel();
        $id = $model_group->create_data($data);
        if(!empty($id)){
            $datajson['code'] = 1;
            $datajson['data']['id'] = $id;
        }else{
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '添加失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data)){
            $datajson['code'] = -101;
            $datajson['message'] = '数据不可为空!';
            $this->jsonReturn($datajson);
        }
        if(empty($data['id'])){
            $datajson['code'] = -101;
            $datajson['message'] = '缺少主键!';
            $this->jsonReturn($datajson);
        }else{
            $where['id'] = $data['id'];
        }
        $model_group = new GroupModel();
        $id = $model_group->update_data($data,$where);
        if($id > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '修改失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function deleteAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if(empty($id)){
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_group = new GroupModel();
        $re = $model_group->delete_data($id);
        if($re > 0){
            $datajson['code'] = 1;
        }else{
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

}
