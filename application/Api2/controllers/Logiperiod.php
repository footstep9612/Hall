<?php

/**
 * 物流时效
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:02
 */
class LogiperiodController extends PublicController {

    private $input;

    public function init() {
        $this->token = false;
        parent::init();
        $this->input = $this->getPut();
    }

    /**
     * 物流时效借口
     */
    public function listAction() {
        if (!isset($this->input['to_country'])) {
            jsonReturn('', '1000');
        }
        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : (browser_lang() ? browser_lang() : 'en');

        $logiModel = new LogiPeriodModel();
        $logis = $logiModel->getList($this->input['lang'], $this->input['to_country']);
        if ($logis || empty($logis)) {
            jsonReturn(array('data' => $logis));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

    /**
     * 根据贸易术语与运输方式查询物流起运国
     */
    public function fromcountryAction() {
        if (!isset($this->input['trade_terms'])) {
            jsonReturn('', '1000');
        }

        if (!isset($this->input['trans_mode'])) {
            jsonReturn('', '1000');
        }
        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';

        $logiModel = new LogiPeriodModel();
        $countrys = $logiModel->getFCountry($this->input['trade_terms'], $this->input['trans_mode'], $this->input['lang']);
        if ($countrys) {
            jsonReturn(array('data' => $countrys));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

    /**
     * 根据贸易术语，运输方式，与国家获取起运港口城市
     */
    public function fromportAction() {
        if (!isset($this->input['trade_terms']) || !isset($this->input['from_country']) || !isset($this->input['trans_mode'])) {
            jsonReturn('', '1000');
        }
        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';

        $logiModel = new LogiPeriodModel();
        $ports = $logiModel->getFPort($this->input['trade_terms'], $this->input['trans_mode'], $this->input['from_country'], $this->input['lang']);
        if ($ports) {
            jsonReturn(array('data' => $ports));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

    /**
     * 根据贸易术语，运输方式，起国家，起运港获取目的国
     */
    public function toCountryAction() {
        if (!isset($this->input['trade_terms']) || !isset($this->input['from_country']) || !isset($this->input['trans_mode']) || !isset($this->input['from_port'])) {
            jsonReturn('', '1000');
        }

        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';

        $logiModel = new LogiPeriodModel();
        $result = $logiModel->getToCountry($this->input['trade_terms'], $this->input['trans_mode'], $this->input['from_country'], $this->input['from_port'], $this->input['lang']);
        if ($result) {
            jsonReturn(array('data' => $result));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

    /**
     * 根据贸易术语，运输方式与起运国，起运港口， 目地国获取目的港口信息
     */
    public function toportAction() {
        if (!isset($this->input['trade_terms']) || !isset($this->input['from_country']) || !isset($this->input['trans_mode']) || !isset($this->input['from_port']) || !isset($this->input['to_country'])) {
            jsonReturn('', '1000');
        }


        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';

        $logiModel = new LogiPeriodModel();
        $result = $logiModel->getToPort($this->input['trade_terms'], $this->input['trans_mode'], $this->input['from_country'], $this->input['from_port'], $this->input['to_country'], $this->input['lang']);
        if ($result) {
            jsonReturn(array('data' => $result));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

}
