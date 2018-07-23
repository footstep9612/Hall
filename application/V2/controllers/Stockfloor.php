<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货楼层
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class StockfloorController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取现货楼层列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function ListAction() {

        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();
        $list = $stock_floor_model->getList($condition);
        if ($list) {
            $count = $stock_floor_model->getCont($condition);
            $this->setvalue('count', $count);
            $ids = [];
            foreach($list as $r){
                $ids[] = $r['id'];
            }
            $sfaModel = new StockFloorAdsModel();
            $ads = $sfaModel->getData(['floor_id'=>['in',$ids]]);
            $adsAry = [];
            foreach($ads as $ad){
                $adsAry[$ad['floor_id']][] = $ad;
            }
            foreach($list as $k=>$v){
                $list[$k]['ads'] = empty($adsAry[$v['id']]) ? [] : $adsAry[$v['id']];
            }
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

    /**
     * Description of 获取现货楼层详情
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function InfoAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();

        $list = $stock_floor_model->getInfo($id);
        if ($list) {
            $sfaModel = new StockFloorAdsModel();
            $ads = $sfaModel->getData(['floor_id'=>$id]);
            $list['ads'] = $ads;
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

    /**
     * Description of 新加现货楼层
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function CreateAction() {
        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        if (empty($condition['floor_name'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请输入楼层名称!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();
        $show_type = $this->getPut('show_type');
        if ($stock_floor_model->getExit($condition['country_bn'], $condition['floor_name'], $condition['lang'], null, $show_type)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }


        $list = $stock_floor_model->createData($condition);
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
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function UpdateAction() {
        $condition = $this->getPut();
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择要编辑的楼层!');
            $this->jsonReturn();
        }
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        if (empty($condition['floor_name'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请输入楼层名称!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();

        if ($stock_floor_model->getExit($condition['country_bn'], $condition['floor_name'], $condition['lang'], $id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }


        $list = $stock_floor_model->updateData($id, $condition);
        if ($list) {
            $this->jsonReturn();
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
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function OnshelfAction() {
        $condition = $this->getPut();
        $id = $condition['id'];
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        if (!isset($condition['onshelf_flag'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('楼层上下架状态不能为空!');
            $this->jsonReturn();
        }

        $stock_floor_model = new StockFloorModel();
        $list = $stock_floor_model->onshelfData($id, $condition['onshelf_flag']);
        if ($list) {
            jsonReturn('', MSG::MSG_SUCCESS);
        } elseif ($list === false) {
            jsonReturn('', MSG::MSG_FAILED);
        } else {
            jsonReturn('', MSG::MSG_FAILED, '系统错误');
        }
    }

    /**
     * 楼层商品列表
     * @author link
     * @date    2018-07-04
     */
    public function goodsListAction()
    {
        $condition = $this->getPut();
        if ( empty( $condition['floor_id'] ) ) {
            jsonReturn('', MSG::ERROR_PARAM, '请选择楼层!');
        }
        if ( empty( $condition['country_bn'] ) ) {
            jsonReturn('', MSG::ERROR_PARAM, '请选择国家!');
        }else{
            $condition['country_bn'] = ucfirst(strtolower($condition['country_bn']));
        }
        if ( empty( $condition['lang'] ) ) {
            jsonReturn('', MSG::ERROR_PARAM, '请选择语言!');
        }

        $stock = new StockModel();
        $list = $stock->getList($condition);
        if($list===false){
            jsonReturn('', MSG::MSG_FAILED, '');
        }else{
            jsonReturn($list);
        }
    }

    /**
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function addGoodsAction() {

        $floor_id = $this->getPut('floor_id');
        if (empty($floor_id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }

        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $skus = $this->getPut('skus');
        if (empty($skus)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择产品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        if (!in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的语言不正确!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();
        $show_type = $this->getPut('show_type');

        $flag = $stock_floor_model->addGoods($floor_id, $country_bn, $lang, $skus, $show_type);

        if ($flag) {
            $message = '添加产品成功!';
            $this->setMessage($message);
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $message = '添加产品失败!';
            $this->setMessage($message);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 添加楼层关键词
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function addKeywordsAction() {

        $floor_id = $this->getPut('floor_id');
        if (empty($floor_id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }

        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $keywords = $this->getPut('keywords');
        if (empty($keywords)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请添加关键词!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        if (!in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的语言不正确!');
            $this->jsonReturn();
        }
        $stock_floor_keyword_model = new StockFloorKeywordModel();
        $show_type = $this->getPut('show_type');

        $flag = $stock_floor_keyword_model->addKeywords($floor_id, $country_bn, $lang, $keywords, $show_type);

        if ($flag) {
            $message = '添加关键词成功!';
            $this->setMessage($message);
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $message = '添加关键词失败!';
            $this->setMessage($message);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function addCatsAction() {

        $floor_id = $this->getPut('floor_id');
        if (empty($floor_id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }

        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $cat_nos = $this->getPut('cat_nos');
        if (empty($cat_nos)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择分类!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        if (!in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的语言不正确!');
            $this->jsonReturn();
        }
        $show_type = $this->getPut('show_type');
        $stock_floor_show_cat_model = new StockFloorShowCatModel();


        $flag = $stock_floor_show_cat_model->addCats($floor_id, $country_bn, $lang, $cat_nos, $show_type);

        if ($flag) {
            $message = '添加分类成功!';
            $this->setMessage($message);
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $message = '添加分类失败!';
            $this->setMessage($message);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * 删除楼层
     * @author link
     * @date 2018-07-04
     */
    public function deleteAction(){
        $input = $this->getPut();
        if(!isset($input['id'])){
            jsonReturn('', MSG::ERROR_PARAM, '请选择楼层ＩＤ');
        }
        $model = new StockFloorModel();;
        $model->startTrans();
        try{
            $rel = $model->deleteData($input);
            if($rel){
                //删除楼层广告
                $sfaModel = new StockFloorAdsModel();
                $sfaModel->deletedData(['floor_id'=>$input['id']]);

                //还原楼层商品
                $stockModel = new StockModel();
                $stockModel->clearFloor(['floor_id'=>$input['id']]);
            }else{
                $model->rollback();
                jsonReturn(false, MSG::MSG_FAILED, '操作失败!');
            }
            $model->commit();
            jsonReturn('', MSG::MSG_SUCCESS, '操作成功');
        }catch (Exception $e){
            $model->rollback();
            jsonReturn(false, MSG::MSG_FAILED, '系统错误!');
        }
    }

}
