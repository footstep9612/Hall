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
        if (empty($condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
        }

        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
        }
        $stock_floor_model = new StockFloorModel();

        $list = $stock_floor_model->getList($condition);
        if ($list) {
            $count = $stock_floor_model->getCont($condition);
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
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();

        $list = $stock_floor_model->getInfo($id);
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

        if ($stock_floor_model->getExit($condition['country_bn'], $condition['floor_name'], $condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }

        $show_flag = $this->getPut('show_flag', 'N');
        $display_position = $this->getPut('display_position');
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

        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        $onshelf_flag = $this->getPut('onshelf_flag');
        if (empty($onshelf_flag)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('楼层上下架状态不能为空!');
            $this->jsonReturn();
        }

        if (!in_array($onshelf_flag, ['N', 'Y'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('楼层上下架状态不正确!');
            $this->jsonReturn();
        }
        $stock_floor_model = new StockFloorModel();


        $list = $stock_floor_model->onshelfData($id, $onshelf_flag);
        if ($list) {
            $message = ($onshelf_flag == 'Y' ? '上架' : '下架') . '成功!';
            $this->setMessage($message);
            $this->jsonReturn();
        } elseif ($list === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $message = ($onshelf_flag == 'Y' ? '上架' : '下架') . '失败!';
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


        $flag = $stock_floor_model->addGoods($floor_id, $country_bn, $lang, $skus);

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

}
