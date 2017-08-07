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
        $data = $this->getPut();
        $data['lang'] = $this->getPut('lang', 'zh');
        $market_area = new CountryModel();

        $arr = $market_area->getlistBycodition($data); //($this->put_data);
        $count = $market_area->getCount($data);

        $this->setvalue('count', $count);
        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);

            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
    }

    /*
     * 营销区域列表
     */

    public function listallAction() {
        $data = $this->getPut();

        $data['lang'] = $this->getPut('lang', 'zh');

        $market_area = new CountryModel();

        $arr = $market_area->getlistBycodition($data, false);

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
        $name = $this->getPut('name');
        $exclude = $this->getPut('exclude');

        $lang = $this->getPut('lang', 'en');
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
        $bn = $this->getPut('bn');

        if (!$bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $data = [];
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $result = $this->_model->field('lang,region_bn,code,bn,name,time_zone,status')
                            ->where(['bn' => $bn, 'lang' => $lang])->find();
            if ($result) {
                if (!$data) {
                    $data = $result;
                    $data['name'] = null;
                    unset($data['name']);
                }
                $data[$lang]['name'] = $result['name'];
            }
        }

        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } elseif ($data === []) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
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
        $result = $this->_model->create_data($this->getPut(), $this->user['id']);
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
        $result = $this->_model->update_data($this->getPut(), $this->user['id']);
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
        $result = $this->_model->updatestatus($this->getPut(), $this->user['id']);
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
        $result = $this->_model->where($where)->save(['status' => 'DELETED']);
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
