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
        $data['deleted_flag'] = 'N';
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
        $re = [];
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
                $return[] = [chr($i) => $re[chr($i)]];
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

    /*
     * 国家列表.全部
     */
    public function CountryListAction() {
        $data = $this->getPut();
        $limit = [];
        $where = [];
        if (!empty($data['bn'])) {
            $where['bn'] = $data['bn'];
        }
        if (!empty($data['name'])) {
            $where['name'] = $data['name'];
        }
        if (!empty($data['code'])) {
            $where['code'] = $data['code'];
        } else {
            $where['code'] = array('neq','');
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
            $arr = $model_group->getlist($where, $limit, 'bn asc');
        } else {
            if (!empty($data['lang'])) {
                $where['lang'] = $data['lang'];
            }
            $arr = $model_group->getlist($where, $limit, 'bn asc');
        }
        if ($arr) {
            jsonReturn($arr);
        } else {
            jsonReturn('', -104, '数据为空!');
        }
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

//    public function createAction() {
//        $this->_init();
//        $country_model = new CountryModel();
//        $result = $country_model->create_data($this->getPut());
//        if ($result) {
//            $this->delcache();
//            $this->setCode(MSG::MSG_SUCCESS);
//            $this->jsonReturn();
//        } else {
//            $this->setCode(MSG::MSG_FAILED);
//            $this->jsonReturn();
//        }
//    }
    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CountryModel();
        if (empty($data['area_bn'])) { //区域简称
            jsonReturn('', 0,'地区不可为空');
        }else{
            $arr['area_bn'] = trim($data['area_bn'],' ');
            $area=$model->checkArea($arr['area_bn']);
            if($area===false){
                jsonReturn('', 0, '暂无该地区');  //暂无该地区
            }
        }
        if (empty($data['country_bn'])) { //国家简称
            jsonReturn('', 0,'国家简称不可为空');
        }else{
            $arr['country_bn'] = trim($data['country_bn'],' ');
            $countryBn=$model->checkCountryBn($arr['country_bn']);
            if($countryBn===false){
                jsonReturn('', 0, '该国家简称已存在');
            }
        }
        if (empty($data['country_name_zh'])) { //国家名称
            jsonReturn('', 0,'国家名称不可为空');
        }else{
            $arr['country_name']['zh']=$data['country_name_zh'];
        }
        if (!empty($data['country_name_en'])) { //国家名称
            $arr['country_name']['en']=$data['country_name_en'];
        }
        if (!empty($data['country_name_ru'])) { //国家名称
            $arr['country_name']['ru']=$data['country_name_ru'];
        }
        if (!empty($data['country_name_es'])) { //国家名称
            $arr['country_name']['es']=$data['country_name_es'];
        }

        if (empty($arr['country_name'])) { //国家名称
            jsonReturn('', 0,'国家名称不可为空');
        }else{
            $countryArr = $arr['country_name'];
            $countryArr['zh']=$countryArr['zh']??'';
            $countryArr['en']=$countryArr['en']??'';
            $countryArr['ru']=$countryArr['ru']??'';
            $countryArr['es']=$countryArr['es']??'';
            $str='';
            foreach($countryArr as $k => &$v){
                $v=trim($v,' ');
                if(empty($countryArr['zh'])){
                    jsonReturn('', 0,'国家中文名称不可为空');
                }
                if(!empty($v)){
                    $str.=",'".$v."'";
                }
            }
            $str=substr($str,1);
            $countryName=$model->checkCountryName($str);
            if($countryName===false){
                jsonReturn('', 0, '该国家名称已存在');
            }
            $arr['country_name']=$countryArr;
        }
        if (!empty($data['tel_code'])) { //电话区号
            $tel = trim($data['tel_code'],' ');
            $telArr=str_split($tel);
            foreach($telArr as $k =>&$v){
                if(!is_numeric($v)){
                    unset($telArr[$k]);
                }
            }
            $telStr=implode($telArr);
            if(empty($telStr)){
                jsonReturn('', 0, '国家区号格式错误');
            }
            $arr['tel_code']=$telStr;
        }else{
            jsonReturn('', 0, '国家区号不可为空');
        }
        if(!empty($data['code'])){
            $arr['code']=strtoupper(trim($data['code'],' '));
        }
        $result=$model->insertCountry($arr);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }
    public function countryAdminAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CountryModel();
        $result = $model->countryAdmin($data);
        $dataJson['code'] = 1;
        $dataJson['message'] = '国家管理列表';
        $dataJson['current_page '] = $result['current_page'];
        $dataJson['total_count'] = $result['total_count'];
        $dataJson['data'] = $result['info'];
        $this->jsonReturn($dataJson);
    }
    public function showCountryAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CountryModel();
        $result = $model->showCountry($data);
        $dataJson['code'] = 1;
        $dataJson['message'] = '查看国家信息';
        $dataJson['data'] = $result;
        $this->jsonReturn($dataJson);
    }
    public function delCountryAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CountryModel();
        $result = $model->delCountry($data);
        $dataJson['code'] = 1;
        $dataJson['message'] = '成功';
        $dataJson['data'] = $result;
        $this->jsonReturn($dataJson);
    }
    public function countryTestAction() {
        $model = new CountryModel();
        $result = $model->countryTest();
        $dataJson['code'] = 1;
        $dataJson['message'] = '国家管理列表';
        $dataJson['data'] = $result;
        $this->jsonReturn($dataJson);
    }
    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CountryModel();
        if (empty($data['area_bn'])) { //区域简称
            jsonReturn('', 0,'地区不可为空');
        }else{
            $arr['area_bn'] = trim($data['area_bn'],' ');
            $area=$model->checkArea($arr['area_bn']);
            if($area===false){
                jsonReturn('', 0, '暂无该地区');  //暂无该地区
            }
        }
        if (empty($data['country_bn'])) { //国家简称
            jsonReturn('', 0,'国家简称不可为空');
        }else{
            $arr['country_bn'] = trim($data['country_bn'],' ');
            $countryBn=$model->checkCountryBn($arr['country_bn']);
            if($countryBn===true){
                jsonReturn('', 0, '该国家简称不存在');
            }
        }
        if (empty($data['country_name'])) { //国家名称
            jsonReturn('', 0,'国家名称不可为空');
        }else{
            $countryArr = $data['country_name'];
            $countryArr['zh']=$countryArr['zh']??'';
            $countryArr['en']=$countryArr['en']??'';
            $countryArr['ru']=$countryArr['ru']??'';
            $countryArr['es']=$countryArr['es']??'';
            $hehe0=$countryArr;
            $str='';
            foreach($countryArr as $k => &$v){
                $v=trim($v,' ');
                if(empty($countryArr['zh'])){
                    jsonReturn('', 0,'国家中文名称不可为空');
                }
                if(!empty($v)){
                    $str.=",'".$v."'";
                }
            }
            $str=substr($str,1);
            $countryName=$model->updateCountryName($str);
            $hehe=[];
            foreach($countryName as $K =>$v){
                $hehe[$v['lang']]=$v['name'];
            }
            if($countryArr!==$hehe){    //原始数据
                $countryName1=$model->nameBybn($data['country_bn']);
                print_r($countryName1);die;
            }
            die;
            print_r($hehe);die;
            if($countryName===false){
                jsonReturn('', 0, '该国家名称已存在');
            }
            $arr['country_name']=$countryArr;
        }
        if (!empty($data['tel_code'])) { //电话区号
            $tel = trim($data['tel_code'],' ');
            $telArr=str_split($tel);
            foreach($telArr as $k =>&$v){
                if(!is_numeric($v)){
                    unset($telArr[$k]);
                }
            }
            $telStr=implode($telArr);
            if(empty($telStr)){
                jsonReturn('', 0, '国家区号格式错误');
            }
            $arr['tel_code']=$telStr;
        }else{
            jsonReturn('', 0, '国家区号不可为空');
        }
        if(!empty($data['code'])){
            $arr['code']=strtoupper(trim($data['code'],' '));
        }
        $result=$model->updateCountry($arr);
        print_r($result);die;
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

//    public function updateAction() {
//        $this->_init();
//
//        $bn = $this->getPut('bn');
//        $market_area_bn = $this->getPut('market_area_bn');
//        if (!$bn || !$market_area_bn) {
//            $this->setCode(MSG::MSG_FAILED);
//            $this->jsonReturn();
//        } $country_model = new CountryModel();
//        $result = $country_model->update_data($this->getPut());
//        if ($result) {
//            $this->delcache();
//            $this->setCode(MSG::MSG_SUCCESS);
//            $this->jsonReturn();
//        } else {
//            $this->setCode(MSG::MSG_FAILED);
//            $this->jsonReturn();
//        }
//    }

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

    /**
     * 国家联系方式
     */
    public function contactCreateAction(){
        $input = $this->getPut();
        $ccontactModel = new CountryContactModel();
        $rel = $ccontactModel->addData($input);
        if($rel !== false){
            jsonReturn($rel);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 国家联系方式
     */
    public function contactUpdateAction(){
        $input = $this->getPut();
        $ccontactModel = new CountryContactModel();
        $rel = $ccontactModel->updateData($input);
        if($rel !== false){
            jsonReturn($rel);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 国家联系方式
     */
    public function contactDeleteAction(){
        $input = $this->getPut();
        $ccontactModel = new CountryContactModel();
        $rel = $ccontactModel->deleteData($input);
        if($rel !== false){
            jsonReturn($rel);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }
}
