<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/24
 * Time: 14:45
 */
class ConfigController extends PublicController{

    public function init(){
        parent::init();
        $this->input = $this->getPut();
    }

    /**
     * 落地配
     */
    public function destdeliveryListAction() {
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