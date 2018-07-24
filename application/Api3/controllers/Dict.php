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
class DictController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
        $this->input = $this->getPut();
    }

    public function CountryListAction() {
        $data = $this->getPut();

        $limit = [];
        $where = ['deleted_flag' => 'N'];
        if (!empty($data['bn'])) {
            $where['bn'] = $data['bn'];
        }
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['status'])) {
            $where['status'] = $data['status'];
        } else {
            $where['status'] = 'VALID';
        }
        if (!empty($data['time_zone'])) {
            $where['time_zone'] = $data['time_zone'];
        }
        if (!empty($data['region_bn'])) {
            $where['region_bn'] = $data['region_bn'];
        }
        if (!empty($data['page'])) {
            $limit['page'] = $data['page'];
        }
        if (!empty($data['countPerPage'])) {
            $limit['num'] = $data['countPerPage'];
        }
        $lang = '';
        if (!empty($data['lang'])) {
            $lang = $data['lang'];
        }
        $model_group = new CountryModel();
        if (empty($where) && empty($limit)) {
            if (!$lang) {
                $lang = 'zh';
            }
            $where['lang'] = $lang;
            if (redisHashExist('CountryList', $lang)) {
                $arr = json_decode(redisHashGet('CountryList', $lang), true);
            } else {
                $model_group = new CountryModel();
                $arr = $model_group->getlist($where, $limit, 'bn asc'); //($this->put_data);
                if ($arr) {
                    redisHashSet('CountryList', $lang, json_encode($arr));
                }
            }
        } else {
            if (!empty($data['lang'])) {
                $where['lang'] = $data['lang'];
            }
            $model_group = new CountryModel();
            $arr = $model_group->getlist($where, $limit, 'bn asc'); //($this->put_data);
        }
        if (!empty($arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        jsonReturn($datajson);
    }

    public function TransModeAction() {
        $condition = $this->getPut();
        $condition['deleted_flag'] = 'N';
        if (!empty($condition['terms'])) {
            $where['terms'] = $condition['terms'];
        } else {
            jsonReturn('', MSG::MSG_FAILED, '[terms]缺少!');
        }
        if (!empty($condition['lang'])) {
            $where['lang'] = $condition['lang'];
        } else {
            $where['lang'] = 'en';
        }
        $trade_terms = new TradeTermsModel();
        try {
            $field = 'id,trans_mode_bn,lang';

            $result = $trade_terms->field($field)->where($where)->select();
            if ($result) {
                jsonReturn($result);
            }
            jsonReturn('', MSG::MSG_FAILED, '');
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    public function TradeTermsListAction() {
        $data = $this->getPut();
        $limit = [];
        $where = ['deleted_flag' => 'N'];
        if (!empty($data['page'])) {
            $limit['page'] = $data['page'];
        }
        if (!empty($data['countPerPage'])) {
            $limit['num'] = $data['countPerPage'];
        }
        $lang = '';
        if (!empty($data['lang'])) {
            $lang = $data['lang'];
        }

        $trade_terms = new TradeTermsModel();
        if (empty($where) && empty($limit)) {
            if (!$lang) {
                $lang = 'en';
            }

            $where['lang'] = $lang;
            if (redisHashExist('TradeTerms', 'TradeTerms' . $lang)) {
//                $arr = json_decode(redisHashGet('TradeTerms', 'TradeTerms'.$lang), true);
//                return $arr;
            }
            $arr = $trade_terms->getlist($where, $limit); //($this->put_data);
            if ($arr) {
                redisHashSet('TradeTerms', 'TradeTerms' . $lang, json_encode($arr));
            }
        } else {
            if (!empty($data['lang'])) {
                $where['lang'] = $data['lang'];
            }
            $arr = $trade_terms->getlist($where, $limit); //($this->put_data);
        }
        if (!empty($arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        jsonReturn($datajson);
    }

    public function TransModeListAction() {
        $data = $this->getPut();
        $limit = [];
        $where = ['deleted_flag' => 'N'];
        if (!empty($data['page'])) {
            $limit['page'] = $data['page'];
        }
        if (!empty($data['countPerPage'])) {
            $limit['num'] = $data['countPerPage'];
        }
        $lang = '';
        if (!empty($data['lang'])) {
            $lang = $data['lang'];
        }

        $trade_mode = new TransModeModel();
        if (empty($where) && empty($limit)) {
            if (!$lang) {
                $lang = 'zh';
            }

            $where['lang'] = $lang;
            if (redisHashExist('TransModeList', $lang)) {
                $arr = json_decode(redisHashGet('TransModeList', $lang), true);
//                return $arr;
            }
            $arr = $trade_mode->getlist($where, $limit); //($this->put_data);

            if ($arr) {
                redisHashSet('TransModeList', $lang, json_encode($arr));
            }
        } else {
            if (!empty($data['lang'])) {
                $where['lang'] = $data['lang'];
            }
            $arr = $trade_mode->getlist($where, $limit); //($this->put_data);
        }
        if (!empty($arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        jsonReturn($datajson);
    }

    /**
     * 获取国家对应营销区域
     * @author klp
     */
    public function getMarketAreaAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? strtolower($data['lang']) : (browser_lang() ? browser_lang() : 'en');
        if (!empty($data['name'])) {
            $country = ucwords($data['name']);
        } else {
            jsonReturn('', '-1001', '参数[name]不能为空');
        }
        $countryModel = new CountryModel();
        $result = $countryModel->getMarketArea($country, $lang);
        if ($result) {
            $data = array(
                'code' => '1',
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '数据获取失败');
        }
        exit;
    }

    /**
     * 港口
     */
    public function portlistAction() {
        $lang = isset($this->input['lang']) ? $this->input['lang'] : 'en';
        //国家简称(bn)
        $country = isset($this->input['country']) ? $this->input['country'] : '';
        $portModel = new PortModel();
        $port = $portModel->getPort($lang, $country);
        jsonReturn(array('data' => $port));
    }

    /**
     * 货币
     */
    public function currencylistAction() {
        $curModel = new CurrencyModel();
        $currency = $curModel->getCurrency();
        jsonReturn(array('data' => $currency));
    }

    /**
     * 支付方式列表
     */
    public function paymentmodelistAction() {
        $lang = isset($this->input['lang']) ? $this->input['lang'] : '';
        $pModel = new PaymentmodeModel();
        $payment = $pModel->getPaymentmode($lang);
        jsonReturn(array('data' => $payment));
    }

    /**
     * 根据IP自动获取国家(新浪接口)
     * @author klp
     */
    public function getCounryAction() {
        $IpModel = new CountryModel();

        $ip = get_client_ip();
        $iplocation = new IpLocation();
        if ($ip != 'Unknown') {
            $country = $iplocation->getlocation($ip);

            $send = $IpModel->getCountrybynameandlang($country['country'], $this->getLang());
        } else {
            $send = 'China';
        }
        $this->setCode(1);
        $this->jsonReturn($send);
    }

    /**
     * 国家地区列表,按首字母分组排序
     * @author klp
     */
    public function listCountryAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? strtolower($data['lang']) : 'ru';

        /* if (redisHashExist('CountryList', $lang)) {
          $result = json_decode(redisHashGet('CountryList', $lang), true);
          jsonReturn($result);
          } */

        $countryModel = new CountryModel();
        $result = $countryModel->getInfoSort($lang);
        if ($result) {
            redisHashSet('CountryList', $lang, json_encode($result));
        }
        if (!empty($result)) {
            $data = array(
                'code' => '1',
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '数据获取失败');
        }
        exit;
    }

    /**
     * 落地配
     */
    public function destdeliveryListAction() {
        if (!isset($this->input['country'])) {
            jsonReturn('', '1000');
        }

        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';
        $ddlModel = new DestDeliveryLogiModel();
        $data = $ddlModel->getList($this->input['country'], $this->input['lang']);
        if ($data || empty($data)) {
            jsonReturn(array('data' => $data));
        } else {
            jsonReturn('', '400', '失败');
        }
    }

    /**
     * 贸易术语
     */
//    public function tradeTermsListAction() {
//        $this->input['lang'] = isset($this->input['lang']) ? $this->input['lang'] : 'en';
//        $ddlModel = new TradeTermsModel();
//        $data = $ddlModel->getList($this->input['country'], $this->input['lang']);
//        if ($data  || empty($data)) {
//            jsonReturn(array('data' => $data));
//        } else {
//            jsonReturn('', '400', '失败');
//        }
//    }
}
