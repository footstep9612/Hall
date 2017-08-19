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
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function getList($lang = '', $to_country = '', $from_country = '', $warehouse = '') {
        if (empty($lang) || empty($to_country)) {
            return array();
        }

        $countryModel = new CountryModel();
        $cityModel = new CityModel();
//库中中国状态暂为无效
        $from_country = $from_country ? $from_country : $countryModel->getCountryByBn('China', $lang);
//city库中暂无东营,暂时写死以为效果
        $warehouse = $warehouse ? $warehouse : $cityModel->getCityByBn('Dongying', $lang);

        $condition = array(
            'status' => self::STATUS_VALID,
            'lang' => $lang,
            'to_country' => $to_country,
            'from_country' => $from_country,
            'warehouse' => $warehouse
        );
        if (redisHashExist('LogiPeriod', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('LogiPeriod', md5(json_encode($condition))), true);
        }
        try {
            $field = 'id,lang,logi_no,trade_terms_bn,trans_mode_bn,warehouse,from_country,'
                    . 'from_port,to_country,clearance_loc,to_port,packing_period_min,'
                    . 'packing_period_max,collecting_period_min,collecting_period_max,'
                    . 'declare_period_min,declare_period_max,loading_period_min,'
                    . 'loading_period_max,int_trans_period_min,int_trans_period_max,'
                    . 'remarks,period_min,period_max,description';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if ($result) {
                foreach ($result as $item) {
                    $data[$item['trade_terms']][] = $item;
                }
                redisHashSet('LogiPeriod', md5(json_encode($condition)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
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
        if ($logis) {
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
