<?php
/**
 * Name: Dice
 * Desc: 基础数据展示
 * User: 张玉良
 * Date: 2017/8/7
 * Time: 11:37
 */
class DictController extends Yaf_Controller_Abstract {

    public function countryListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
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
        if (!empty($data['region'])) {
            $where['region'] = $data['region'];
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
                $arr = $model_group->getlist($where, $limit, 'pinyin asc');
                if ($arr) {
                    redisHashSet('CountryList', $lang, json_encode($arr));
                }
            }
        } else {
            if (!empty($data['lang'])) {
                $where['lang'] = $data['lang'];
            }
            $model_group = new CountryModel();
            $arr = $model_group->getlist($where, $limit, 'pinyin asc');
        }
        if (!empty($arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -103;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }
}