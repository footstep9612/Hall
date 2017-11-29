<?php
/**
 * Description of ExportTariffController
 * @author  Link
 * @date    2017-11-29
 * @desc   目的拜访类型
 */
class VisitTypeController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 列表
     * @author  link
     * @date    2017-11-29
     */
    public function listAction() {
        $data = $this->getPut();
        $visit_model = new VisitTypeModel();
        $arr = $visit_model->getList($data);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 详情
     * @author  link
     * @date    2017-11-29
     */
    public function infoAction() {
        $data = $this->getPut();
        if(!isset($data['id']) || empty($data['id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new VisitTypeModel();
        $arr = $visit_model->getInfoById($data['id']);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 新增
     * @author  link
     * @date    2017-11-29
     */
    public function createAction() {
        $data = $this->getPut();
        $visit_model = new VisitTypeModel();
        $arr = $visit_model->edit($data);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 修改
     * @author  link
     * @date    2017-11-29
     */
    public function updateAction() {
        $data = $this->getPut();
        if(!isset($data['id']) || empty($data['id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new VisitTypeModel();
        $arr = $visit_model->edit($data);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 删除级别
     * @author  link
     * @date    2017-11-29
     * @desc    删
     */
    public function deleteAction() {
        $data = $this->getPut();
        if(!isset($data['id']) || empty($data['id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new VisitTypeModel();
        $arr = $visit_model->deleteById($data['id']);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
