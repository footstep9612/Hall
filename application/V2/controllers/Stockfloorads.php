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
class StockflooradsController extends PublicController {

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


        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }

        if (empty($condition['floor_id'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        $stock_floor_ads_model = new StockFloorAdsModel();

        $list = $stock_floor_ads_model->getList($condition);
        if ($list) {
            $count = $stock_floor_ads_model->getCont($condition);
            $this->setvalue('count', $count);
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
            $this->setMessage('请选择楼层广告!');
            $this->jsonReturn();
        }
        $stock_floor_ads_model = new StockFloorAdsModel();

        $list = $stock_floor_ads_model->getInfo($id);
        if ($list) {
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
     * Description of 新加现货广告
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
        if (empty($condition['floor_id'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        if (empty($condition['img_url'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请上传广告图片!');
            $this->jsonReturn();
        }
        if (empty($condition['img_name'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请上传输入广告名称!');
            $this->jsonReturn();
        }

        if (empty($condition['link'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请输入广告链接地址!');
            $this->jsonReturn();
        }
        $stock_floor_ads_model = new StockFloorAdsModel();

        if ($stock_floor_ads_model->getExit($condition['country_bn'], $condition['floor_id'], $condition['img_name'], $condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }


        $list = $stock_floor_ads_model->createData($condition);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('新增失败!');
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
            $this->setMessage('请选择要编辑的广告!');
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
        if (empty($condition['floor_id'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        if (empty($condition['img_url'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请上传广告图片!');
            $this->jsonReturn();
        }
        if (empty($condition['img_name'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请上传输入广告名称!');
            $this->jsonReturn();
        }

        if (empty($condition['link'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请输入广告链接地址!');
            $this->jsonReturn();
        }
        $stock_floor_ads_model = new StockFloorAdsModel();

        if ($stock_floor_ads_model->getExit($condition['country_bn'], $condition['floor_id'], $condition['img_name'], $condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }


        $list = $stock_floor_ads_model->updateData($id, $condition);
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

}
