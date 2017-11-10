<?php

/**
  附件文档Controller
 */
class CountryController extends PublicController {

    protected $langs = ['en', 'es', 'ru', 'zh'];
    protected $index = 'erui_dict';
    protected $es = '';

    public function init() {
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);

        $this->es = new ESClient();
    }

    private function _init() {
        parent::init();
    }

    /*
     * 营销区域列表
     */

    public function listAction() {



        $data = $this->getPut();
        $data['lang'] = $this->getPut('lang', 'zh');
        $country_model = new CountryModel();
        $arr = $country_model->getlistBycodition($data); //($this->put_data);
        $count = $country_model->getCount($data);
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
        $country_model = new CountryModel();
        $arr = $country_model->getlistBycodition($data, 'c.bn ASC', false);

        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /*
     * 营销区域列表
     */

    public function listByLetterAction() {
        //$data = $this->getPut();

        $data['lang'] = 'zh';
        $country_model = new CountryModel();
        $arr = $country_model->getlistBycodition($data, 'c.bn ASC', false);

        if ($arr) {
            foreach ($arr as $country) {
                $letter = $this->_getFirstCharter($country['name']);
                $re[$letter][] = [
                    'name' => $country['name'],
                    'bn' => $country['bn'],
                    'letter' => $letter,
                ];
            }
        }

        $return = [];
        for ($i = 65; $i <= 90; $i++) {

            if (!empty($re[chr($i)])) {
                $return[] = $re[chr($i)];
            }
        }
        if (!empty($return)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($return === []) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($return);
    }

    /**
     * 取汉字的第一个字的首字母
     * @param type $str
     * @return string|null
     * @author klp
     */
    private function _getFirstCharter($str) {
        if (empty($str)) {
            return '';
        } elseif ($str === '斐济') {
            return 'F';
        }
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($str{0});
        }
        $s1 = iconv('UTF-8', 'gb2312', $str);
        $s2 = iconv('gb2312', 'UTF-8', $s1);
        $s = $s2 == $str ? $s1 : $str;

        $ascs = [-20319, -20283, -19775, -19218, -18710, -18526, -18239, -17922, - 17417, -16474, -16212, -15640, -15165, -14922, -14914, -14630, -14149, -14090, -13318, -12838, -12556, -11847, -11055, -10247];
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        for ($i = 0; $i < 26; $i++) {
            if ($asc >= $ascs[$i] && $asc < $ascs[$i + 1]) {

                if ($i > 18) {
                    return chr($i + 68);
                } elseif ($i > 7 && $i <= 18) {
                    return chr($i + 66);
                } else {
                    return chr($i + 65);
                }
            }
        }
        return null;
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
            $country_model = new CountryModel();
            $info = $country_model->exist(['name' => $name, 'lang' => $lang]);

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
            $country_model = new CountryModel();
            $result = $country_model->field('lang,region_bn,code,bn,name,time_zone,status')
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
        unset($redis);
        $config = Yaf_Registry::get("config");
        $rconfig = $config->redis->config->toArray();
        $rconfig['dbname'] = 3;
        $redis3 = new phpredis($rconfig);
        $keys3 = $redis3->getKeys('Country');
        $redis3->delete($keys3);
        unset($redis3);
    }

    /*
     * 创建能力值
     */

    public function createAction() {
        $this->_init();
        $country_model = new CountryModel();
        $result = $country_model->create_data($this->getPut());
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
        $this->_init();

        $bn = $this->getPut('bn');
        $market_area_bn = $this->getPut('market_area_bn');
        if (!$bn || !$market_area_bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        } $country_model = new CountryModel();
        $result = $country_model->update_data($this->getPut());
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
        $this->_init();
        $country_model = new CountryModel();
        $result = $country_model->updatestatus($this->getPut());
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
        $this->_init();
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
        $result = $this->_model->where($where)->save([
            'status' => 'DELETED',
            'deleted_flag' => 'Y']);
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
        $this->_init();
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
