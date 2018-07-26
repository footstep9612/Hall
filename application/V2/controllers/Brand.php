<?php

/**
  附件文档Controller
 */
class BrandController extends PublicController {

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function init() {

        parent::init();
        error_reporting(E_ALL);
    }

    public function listAction() {

        $condition = $this->getPut();
        $lang = $this->getPut('lang', '');
        unset($condition['token']);

        $brand_model = new BrandModel();
        $arr = $brand_model->getlist($condition, $lang);
        if (!empty($condition['current_no'])) {
            $current_no = intval($condition['current_no']);
        } else {
            $current_no = 1;
        }
        foreach ($arr as $key => $item) {


            $brands = json_decode($item['brand'], true);

            foreach ($this->langs as $lang) {
                $brand[$lang] = [];
            }
            if (isset($brands['lang'])) {
                $val = $brands;
                $brand[$val['lang']] = $val;
                $brand[$val['lang']]['id'] = $item['id'];
            } else {
                foreach ($brands as $val) {
                    if (empty($val['lang'])) {
                        continue;
                    }
                    $brand[$val['lang']] = $val;
                    $brand[$val['lang']]['id'] = $item['id'];
                }
            }
            $arr[$key] = $brand;
        }
        if ($arr) {
            $count = $brand_model->getCount($condition, $lang);
            $this->setvalue('count', $count);
            $this->setvalue('current_no', $current_no);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 获取相似品牌
     */

    public function getSimilarAction() {

        $condition = $this->getPut();
        $lang = $this->getPut('lang', '');
        $id = $this->getPut('id', '');
        $name = $this->getPut('name', '');
//        if (empty($id)) {
//            $this->setCode(MSG::ERROR_PARAM);
//            $this->setMessage('请输入ID!');
//            $this->jsonReturn();
//        }
        if (empty($lang)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入语言!');
            $this->jsonReturn();
        }
        if (empty($name)) {

            $this->setCode(MSG::MSG_SUCCESS);

            $this->jsonReturn(null);
        }
        $brand_model = new BrandModel();
        unset($condition['id']);
        $arr = $brand_model->listall($condition, $lang);

        $ret = [];
        foreach ($arr as $item) {
            $brands = json_decode($item['brand'], true);
            if (isset($brands['lang'])) {
                $val = $brands;
                $name = strtoupper($name);
                if ($val['lang'] === $lang && $item['id'] != $id && strpos(strtoupper($val['name']), $name) !== false) {
                    $ret[trim($val['name'])] = ['name' => trim($val['name'])];
                }
            } else {
                foreach ($brands as $val) {
                    if (empty($val['lang'])) {
                        continue;
                    }
                    $name = strtoupper($name);
                    if ($val['lang'] === $lang && $item['id'] != $id && strpos(strtoupper($val['name']), $name) !== false) {
                        $ret[trim($val['name'])] = ['name' => trim($val['name'])];
                    }
                }
            }
            foreach ($brands as $val) {
                $name = strtoupper($name);
                if ($val['lang'] === $lang && $item['id'] != $id && strpos(strtoupper($val['name']), $name) !== false) {
                    $ret[trim($val['name'])] = ['name' => trim($val['name'])];
                }
            }
        }

        rsort($ret);


        if (!empty($ret)) {
            if (count($ret) > 10) {
                $ret = array_slice($ret, 0, 10);
            }
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($ret);
        } elseif ($arr === null || ($arr && $ret == [])) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 获取所有品牌
     */

    public function ListAllAction() {

        $condition = $this->getPut();
        $lang = $this->getLang();

        $brand_model = new BrandModel();
        $arr = $brand_model->listall($condition, $lang);
        echo $brand_model->_sql();
        foreach ($arr as $key => $item) {
            $brands = json_decode($item['brand'], true);
            $brand = [];
            foreach ($this->langs as $lang) {
                $brand[$lang] = [];
            }
            if (isset($brands['lang'])) {
                $val = $brands;
                $brand[$val['lang']] = $val;
                $brand[$val['lang']]['id'] = $item['id'];
            } else {
                foreach ($brands as $val) {
                    if (empty($val['lang'])) {
                        continue;
                    }
                    $brand[$val['lang']] = $val;
                    $brand[$val['lang']]['id'] = $item['id'];
                }
            }
            $arr[$key] = $brand;
        }


        if ($arr) {
            $count = $brand_model->getCount($condition, $lang);
            $this->setvalue('count', $count);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * 分类联动
     */
    public function infoAction() {
        $id = $this->getPut('id');
        if (!$id) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $brand_model = new BrandModel();
        $result = $brand_model->info($id);

        $brands = json_decode($result['brand'], true);
        foreach ($this->langs as $lang) {
            $result[$lang] = [];
        }

        if (empty($brands['name'])) {
            foreach ($brands as $val) {
                $result[$val['lang']] = $val;
            }
        } else {
            $result[$brands['lang']] = $brands;
        }

        unset($result['brand']);
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);

            $this->jsonReturn($result);
        } elseif ($result === null) {
            $this->setCode(MSG::ERROR_EMPTY);

            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        exit;
    }

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Brand');
        $redis->delete($keys);
    }

    public function createAction() {
        $brand_model = new BrandModel();
        $data = $this->getPut();
        if (empty($data['zh']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入中文');
            $this->jsonReturn();
        } elseif ($data['zh']['name']) {
            $flag = $brand_model->brandExist($data['zh']['name'], 'zh');
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('中文名称已存在!');
                $this->jsonReturn();
            }
        }
        if (empty($data['en']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入英文');
            $this->jsonReturn();
        } elseif (isset($data['en']['name']) && $data['en']['name']) {
            $this->_verifyName($data['en']['name']);
            $flag = $brand_model->brandExist($data['en']['name'], 'en');
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('英文名称已存在!');
                $this->jsonReturn();
            }
        }
        if (isset($data['es']['name']) && $data['es']['name']) {
            $flag = $brand_model->brandExist($data['es']['name'], 'es');
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('西文名称已存在!');
                $this->jsonReturn();
            }
        }
        if (isset($data['ru']['name']) && $data['ru']['name']) {
            $flag = $brand_model->brandExist($data['ru']['name'], 'ru');
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('俄文名称已存在!');
                $this->jsonReturn();
            }
        }

        $result = $brand_model->create_data($data);
        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateAction() {
        $brand_model = new BrandModel();
        $data = $this->getPut();
        if (empty($data['zh']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入中文!');
            $this->jsonReturn();
        } elseif ($data['zh']['name']) {
            $flag = $brand_model->brandExist($data['zh']['name'], 'zh', $data['id']);
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('中文名称已存在!');
                $this->jsonReturn();
            }
        }
        if (empty($data['en']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入英文!');
            $this->jsonReturn();
        } elseif (isset($data['en']['name'])) {
            $data['en']['name'] = $this->_verifyName($data['en']['name']);
            $flag = $brand_model->brandExist($data['en']['name'], 'en', $data['id']);
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('英文名称已存在!');
                $this->jsonReturn();
            }
        }
        if (isset($data['es']['name']) && $data['es']['name']) {
            $flag = $brand_model->brandExist($data['es']['name'], 'es', $data['id']);
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('西文名称已存在!');
                $this->jsonReturn();
            }
        }
        if (isset($data['ru']['name']) && $data['ru']['name']) {
            $flag = $brand_model->brandExist($data['ru']['name'], 'ru', $data['id']);
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('俄文名称已存在!');
                $this->jsonReturn();
            }
        }
        //    $this->_verifyLog($data);
        $result = $brand_model->update_data($data);
        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    private function _verifyName($name) {

        $name = $this->SBC_DBC($name);
        $p = '[\x{4e00}-\x{9fa5}'
                . '\。\，\、\；\：\？\！\…\—\·\ˉ\¨\‘\’'
                . '\“\”\々\～\‖\∶\＂\＇\｀\｜\〃\〔\〕'
                . '\〈\〉\《\》\「\」\『\』\．\〖\〗\【'
                . '\】\（\）\［\］\｛\｝]';
        if (preg_match('/^' . $p . '+$/u', $name) > 0) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('英文品牌中含有中文或中文符号，请您查证后重新输入！');
            $this->jsonReturn();
        } elseif (preg_match('/' . $p . '/u', $name) > 0) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('英文品牌中含有中文或中文符号，请您查证后重新输入！');
            $this->jsonReturn();
        }
        return $name;
    }

    /*
     * 判断文件格式和大小
     */

    private function _verifyLog($data) {
        $maxsize = 1048576;
        foreach ($this->langs as $lang) {
            if (!empty($data[$lang]['logo'])) {
                $imageinfo = getimagesize($data[$lang]['logo']);
                if (!$imageinfo || !isset($imageinfo['2']) || !in_array($imageinfo['2'], [2, 3])) {
                    $this->setCode(MSG::ERROR_PARAM);
                    $this->setMessage('只能上传jpg、png格式图片！');
                    $this->jsonReturn();
                }
                $headers = get_headers($data[$lang]['logo'], 1);
                if (isset($headers['Content-Length']) && $headers['Content-Length'] > $maxsize) {
                    $this->setCode(MSG::ERROR_PARAM);
                    $this->setMessage('您上传的文件大于1M！');
                    $this->jsonReturn();
                }
            }
        }
    }

// 第一个参数：传入要转换的字符串
// 第二个参数：取0，半角转全角；取1，全角到半角
    function SBC_DBC($str, $args2 = 1) {
        $DBC = Array(
            '０', '１', '２', '３', '４', '５', '６', '７', '８', '９', 'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ',
            'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ', 'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ', 'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ',
            'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ', 'Ｚ', 'ａ', 'ｂ', 'ｃ', 'ｄ', 'ｅ', 'ｆ', 'ｇ', 'ｈ', 'ｉ',
            'ｊ', 'ｋ', 'ｌ', 'ｍ', 'ｎ', 'ｏ', 'ｐ', 'ｑ', 'ｒ', 'ｓ', 'ｔ', 'ｕ', 'ｖ', 'ｗ', 'ｘ',
            'ｙ', 'ｚ', '－', '　', '：', '．', '，', '／', '％', '＃', '！', '＠', '＆', '（', '）',
            '＜', '＞', '＂', '＇', '？', '［', '］', '｛', '｝', '＼', '｜', '＋', '＝', '＿', '＾',
            '￥', '￣', '｀'
        );
        $SBC = Array(// 半角
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E',
            'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T',
            'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i',
            'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
            'y', 'z', '-', ' ', ':', '.', ',', '/', '%', '#', '!', '@', '&', '(', ')',
            '<', '>', '"', '\'', '?', '[', ']', '{', '}', '\\', '|', '+', '=', '_', '^',
            '$', '~', '`'
        );
        if ($args2 == 0) {
            return str_replace($SBC, $DBC, $str);  // 半角到全角
        } else if ($args2 == 1) {
            return str_replace($DBC, $SBC, $str);  // 全角到半角
        } else {
            return false;
        }
    }

    /**
     * 导出品牌
     */
    public function exportAction() {
        $data = $this->getPut();
        $brand_model = new BrandModel();
        $localDir = $brand_model->export($data, $this->user);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    public function deleteAction() {
        $brand_model = new BrandModel();
        $product_model = new EsProductModel();
        $id = $this->getPut('id');
        $brand_info = $brand_model->info($id);
        $brands = json_decode($brand_info['brand'], true);
        if ($brands) {
            foreach ($brands as $brand) {

                if (!empty($brand['name'])) {
                    $conditions = [];
                    $condition['status'] = 'ALL';
                    $condition['onshelf_flag'] = 'A';
                    $conditions['brand'] = trim($brand['name']);
                    $data = $product_model->getCounts($conditions, $brand['lang']);

                    if ($data) {
                        $this->setCode(MSG::DELETE_MATERIAL_CAT_ERR);
                        $this->jsonReturn();
                    }
                }
            }
        }
        $result = $brand_model->delete_data($id);
        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function batchdeleteAction() {
        $brand_model = new BrandModel();
        $ids = $this->getPut('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $result = $brand_model->batchdelete_data($ids);
        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
