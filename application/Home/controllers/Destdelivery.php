<?php

/**
 * 落地配
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:39
 */
class DestdeliveryController extends PublicController {

    private $input;

    public function init() {
        $this->token = false;
        parent::init();
        $this->input = $this->getPut();
    }

    /**
     * 落地配
     */
    public function listAction() {
        // $this->input['country'] = '巴西';
        // $this->input['lang'] = 'zh';
        if (!isset($this->input['country'])) {
            jsonReturn('', '1000');
        }

        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';
        $ddlModel = new DestDeliveryLogiModel();
        $data = $ddlModel->getList($this->input['country'], $this->input['lang']);
        if ($data) {
            jsonReturn(array('data' => $data));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

}
