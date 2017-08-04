<?php

/**
  附件文档Controller
 */
class CountryController extends PublicController {

    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $index = 'erui_dict';
    protected $es = '';

    public function init() {
//parent::init();
        $this->es = new ESClient();
        $this->_model = new CountryModel();
    }

    /*
     * 营销区域列表
     */

    public function listAction() {
        $data = $this->get();
        unset($data['token']);
        if (isset($data['current_no']) && $data['current_no']) {
            $data['current_no'] = intval($data['current_no']) > 0 ? intval($data['current_no']) : 1;
        }
        if (isset($data['pagesize']) && $data['pagesize']) {
            $data['pagesize'] = intval($data['pagesize']) > 0 ? intval($data['pagesize']) : 2;
        }
        $market_area = new CountryModel();
        if (redisGet('Country_list_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Country_list_' . md5(json_encode($data))), true);
        } else {
            $arr = $market_area->getlistBycodition($data); //($this->put_data);

            if ($arr) {
                redisSet('Country_list_' . md5(json_encode($data)), json_encode($arr));
            }
        }

        if (!empty($arr)) {
            $data['code'] = MSG::MSG_SUCCESS;
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS);
            $data['data'] = $arr;
        } else {
            $data['code'] = MSG::MSG_FAILED;
            $data['message'] = MSG::getMessage(MSG::MSG_FAILED);
        }
        $data['count'] = $market_area->getCount($data);

        $this->jsonReturn($data);
    }

    /*
     * 营销区域列表
     */

    public function listallAction() {
        $data = $this->get();
        unset($data['token']);
        $data['lang'] = $this->getPut('lang', 'zh');

        $market_area = new CountryModel();
        if (redisGet('Country_listall_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Country_listall_' . md5(json_encode($data))), true);
        } else {
            $arr = $market_area->getlistBycodition($data, false);
            if ($arr) {
                redisSet('Country_listall_' . md5(json_encode($data)), json_encode($arr));
            }
        }
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /*
     * 验重
     */

    public function checknameAction() {
        $name = $this->get('name');
        $exclude = $this->get('exclude');

        $lang = $this->get('lang', 'en');
        if ($exclude == $name) {
            $this->setCode(1);
            $data = true;
            $this->jsonReturn($data);
        } else {
            $info = $this->model->exist(['name' => $name, 'lang' => $lang]);

            if ($info) {
                $this->setCode(1);
                $data = false;
                $this->jsonReturn($data);
            } else {
                $this->setCode(1);
                $data = true;
                $this->jsonReturn($data);
            }
        }
    }

    /**
     * 详情
     */
    public function infoAction() {
        $bn = $this->get('id');
        $bn = 'Middle Asia';
        if (!$bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $ret_en = $this->_model->info($bn, 'en');
        $ret_zh = $this->_model->info($bn, 'zh');
        $ret_es = $this->_model->info($bn, 'es');
        $ret_ru = $this->_model->info($bn, 'ru');
        $result = !empty($ret_en) ? $ret_en : (!empty($ret_zh) ? $ret_zh : (empty($ret_es) ? $ret_es : $ret_ru));
        if ($ret_en) {
            $result['en']['name'] = $ret_en['name'];
            $result['en']['id'] = $ret_en['id'];
        }
        if ($ret_zh) {
            $result['zh']['name'] = $ret_zh['name'];
            $result['zh']['id'] = $ret_zh['id'];
        }
        if ($ret_ru) {
            $result['ru']['name'] = $ret_ru['name'];
            $result['ru']['id'] = $ret_ru['id'];
        }
        if ($ret_es) {
            $result['es']['name'] = $ret_es['name'];
            $result['es']['id'] = $ret_es['id'];
        }
        unset($result['id']);
        unset($result['lang']);
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /*
     * 删除缓存
     */

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Country_*');
        $redis->delete($keys);
    }

    /*
     * 创建能力值
     */

    public function createAction() {
        $result = $this->_model->create_data($this->put_data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 更新能力值
     */

    public function updateAction() {
        $where = [];
        $bn = $this->getPut('bn');
        $market_area_bn = $this->getPut('market_area_bn');
        if (!$bn || !$market_area_bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $result = $this->_model->update_data($this->put_data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 更新能力值
     */

    public function updatestatusAction() {
        $result = $this->_model->updatestatus($this->put_data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 删除能力
     */

    public function deleteAction() {
        $condition = $this->put_data;
        if (isset($condition['id']) && $condition['id']) {
            if (is_string($condition['id'])) {
                $where['id'] = $condition['id'];
            } elseif (is_array($condition['id'])) {
                $where['id'] = ['in', $condition['id']];
            }
        } elseif ($condition['bn']) {
            $where['bn'] = $condition['bn'];
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $result = $this->_model->where($where)->delete();
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function indexAction() {
        $body['mappings'] = [];
        foreach ($this->langs as $lang) {
            $body['mappings']['country_' . $lang]['properties'] = $this->country($lang);
            $body['mappings']['country_' . $lang]['_all'] = ['enabled' => false];
        }
        $this->es->create_index($this->index, $body, 5);
        $this->setCode(1);
        $this->setMessage('成功!');
        $this->jsonReturn();
    }

    private function country($lang) {
        if (!in_array($lang, $this->langs)) {
            $lang = 'en';
        }
        $body = '{"id":{"type":"integer"},'
                . '"time_zone":{"type":"integer"},'
                . '"status":{"index":"not_analyzed","type":"string"},'
                . '"letter":{"index":"not_analyzed","type":"string"},'
                . '"lang":{"index":"not_analyzed","type":"string"},'
                . '"market_area_bn":{"index":"not_analyzed","type":"string"},'
                . '"bn":{"index":"not_analyzed","type":"string"},'
                . '"name":{"index":"no","type":"string",'
                . '"fields":{"all":{"index":"not_analyzed","type":"string"},'
                . '"standard":{"analyzer":"standard","type":"string"},'
                . '"ik":{"analyzer":"ik","type":"string"},'
                . '"whitespace":{"analyzer":"whitespace","type":"string"}}},'
                . '"region":{"index":"no","type":"string",'
                . '"fields":{"all":{"index":"not_analyzed","type":"string"},'
                . '"standard":{"analyzer":"standard","type":"string"},'
                . '"ik":{"analyzer":"ik","type":"string"},'
                . '"whitespace":{"analyzer":"whitespace","type":"string"}}},'
                . '"pinyin":{"index":"no","type":"string",'
                . '"fields":{"all":{"index":"not_analyzed","type":"string"},'
                . '"standard":{"analyzer":"standard","type":"string"},'
                . '"ik":{"analyzer":"ik","type":"string"},'
                . '"whitespace":{"analyzer":"whitespace","type":"string"}}},'
                . '"citys":{"index":"no","type":"string",'
                . '"fields":{"all":{"index":"not_analyzed","type":"string"},'
                . '"standard":{"analyzer":"standard","type":"string"},'
                . '"ik":{"analyzer":"ik","type":"string"},'
                . '"whitespace":{"analyzer":"whitespace","type":"string"}}},'
                . '"ports":{"index":"no","type":"string",'
                . '"fields":{"all":{"index":"not_analyzed","type":"string"},'
                . '"standard":{"analyzer":"standard","type":"string"},'
                . '"ik":{"analyzer":"ik","type":"string"},'
                . '"whitespace":{"analyzer":"whitespace","type":"string"}}}}';

        return json_decode($body, true);
    }

    /*
     * product数据导入
     */

    public function importAction($lang = 'en') {
        try {
            set_time_limit(0);
            ini_set('memory_limi', '1G');
            foreach ($this->langs as $lang) {
                $country_model = new CountryModel();
                $country_model->import($lang);
            }
            $this->setCode(1);
            $this->setMessage('成功!');
            $this->jsonReturn();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            $this->setCode(-2001);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
