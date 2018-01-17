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
class HomefloorController extends PublicController {

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
        $stock_floor_model = new HomeFloorProductModel();

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
        $home_floor_model = new HomeFloorModel();

        if ($home_floor_model->getExit($condition['country_bn'], $condition['floor_name'], $condition['lang'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }

        $list = $home_floor_model->createData($condition);
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
        $home_floor_model = new HomeFloorModel();

        if ($home_floor_model->getExit($condition['country_bn'], $condition['floor_name'], $condition['lang'], $id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('所在国家已经存在相同楼层名称,请您添加不同名称的楼层!');
            $this->jsonReturn();
        }


        $list = $home_floor_model->updateData($id, $condition);
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
        $home_floor_model = new HomeFloorModel();


        $list = $home_floor_model->onshelfData($id, $onshelf_flag);
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
    public function addProductsAction() {

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
        $spus = $this->getPut('spus');
        if (empty($spus)) {
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
        $stock_floor_model = new HomeFloorModel();


        $flag = $stock_floor_model->addProducts($floor_id, $country_bn, $lang, $spus);

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
        $home_floor_keyword_model = new HomeFloorKeywordModel();


        $flag = $home_floor_keyword_model->addKeywords($floor_id, $country_bn, $lang, $keywords);

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
     * Description of 添加楼层关键词
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function updatedKeywordsAction() {

        $floor_id = $this->getPut('floor_id');
        if (empty($floor_id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择楼层!');
            $this->jsonReturn();
        }
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择需要更新的产品!');
            $this->jsonReturn();
        }
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $keyword = $this->getPut('keyword');
        if (empty($keyword)) {
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
        $sort_order = $this->getPut('sort_order');
        $deleted_flag = $this->getPut('deleted_flag', 'N');
        if (!in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的语言不正确!');
            $this->jsonReturn();
        }
        $home_floor_keyword_model = new HomeFloorKeywordModel();


        $flag = $home_floor_keyword_model->updateData($id, $floor_id, $country_bn, $lang, $sort_order, $keyword, $deleted_flag);

        if ($flag) {
            $message = '删除关键词成功!';
            $this->setMessage($message);
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $message = '删除关键词失败!';
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
        $deleted_flag = $this->getPut('deleted_flag', 'N');
        if (!in_array($lang, ['zh', 'en', 'es', 'ru'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('您选择的语言不正确!');
            $this->jsonReturn();
        }
        $home_floor_show_cat_model = new HomeFloorShowCatModel();


        $flag = $home_floor_show_cat_model->addCats($floor_id, $country_bn, $lang, $cat_nos, $deleted_flag);

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
     * Description of 更新现货楼层
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  现货楼层
     */
    public function updateCatsAction() {

        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择更新的分类!');
            $this->jsonReturn();
        }
        $condition = $this->getPut();
        $home_floor_show_cat_model = new HomeFloorShowCatModel();
        $flag = $home_floor_show_cat_model->updateData($id, $condition);

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

}
