<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

    public function init() {
        if ($this->getRequest()->isCli()) {
            ini_set("display_errors", "On");
            error_reporting(E_ERROR | E_STRICT);
        } else {
            parent::init();
        }
        $this->_model = new ShowCatModel();
    }

    public function treeAction() {
        ini_set('memory_limit', '800M');
        set_time_limit(360);
        $lang = $this->getPut('lang', 'zh');
        $jsondata = ['lang' => $lang];
        $jsondata['level_no'] = 1;
        $country_bn = $this->getPut('country_bn', '');
        $market_area_bn = $this->getPut('market_area_bn', '');
        $jsondata['country_bn'] = $country_bn;
        $jsondata['market_area_bn'] = $market_area_bn;

        $arr = $this->_model->tree($jsondata);

        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            foreach ($arr as $key => $val) {
                $children_data = $jsondata;
                $children_data['level_no'] = 2;
                $children_data['parent_cat_no'] = $val['value'];
                $arr[$key]['children'] = $this->_model->tree($children_data);
                if ($arr[$key]['children']) {
                    foreach ($arr[$key]['children'] as $k => $item) {
                        $children_data['level_no'] = 3;
                        $children_data['parent_cat_no'] = $item['value'];
                        $arr[$key]['children'][$k]['children'] = $this->_model->tree($children_data);
                    }
                }
            }

            $this->setCode(MSG::MSG_SUCCESS);
            $this->_setCount($lang, $country_bn, $marke_area_bn);
            $this->jsonReturn($arr);
        } else {
            $this->setvalue('count1', 0);
            $this->setvalue('count2', 0);
            $this->setvalue('count3', 0);
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn($arr);
        }
    }

    /**
     * 根据条件获取查询条件
     * @param string $lang 语言
     * @return null
     * @author zyg
     *
     */
    private function _setCount($lang, $country_bn, $marke_area_bn) {



        $countData = ['lang' => $lang,
            'marke_area_bn' => $marke_area_bn,
            'country_bn' => $country_bn,
        ];

        $countData['level_no'] = 1;
        $count1 = $this->_model->getCount($countData); //一级分类数据

        $countData['level_no'] = 2;
        $count2 = $this->_model->getCount($countData); //二级分类数据
        $countData['level_no'] = 3;
        $count3 = $this->_model->getCount($countData); //三级分类数据
        $this->setvalue('count1', intval($count1));
        $this->setvalue('count2', intval($count2));
        $this->setvalue('count3', intval($count3));
    }

    public function listAction() {
        $lang = $this->getPut('lang', 'en');
        $jsondata = ['lang' => $lang];
        $jsondata['level_no'] = 1;
        $country_bn = $this->getPut('country_bn', '');
        $market_area_bn = $this->getPut('market_area_bn', '');
        $jsondata['country_bn'] = $country_bn;
        $jsondata['market_area_bn'] = $market_area_bn;
        $jsondata['cat_no1'] = $this->getPut('cat_no1', '');
        $jsondata['cat_no2'] = $this->getPut('cat_no2', '');
        $jsondata['cat_no3'] = $this->getPut('cat_no3', '');
        $condition = $jsondata;


        if (!$data) {
            $arr = $this->_model->getlist($jsondata);
            if ($arr) {
                $this->setCode(MSG::MSG_SUCCESS);
                foreach ($arr as $key => $val) {
                    $arr[$key]['childs'] = $this->_model->getlist(
                            ['parenshow_material_catcashow_material_catno' => $val['cashow_material_catno'],
                                'level_no' => 2,
                                'country_bn' => $country_bn,
                                'markeshow_material_catarea_bn' => $markeshow_material_catarea_bn,
                                'lang' => $lang]);

                    if ($arr[$key]['childs']) {
                        foreach ($arr[$key]['childs'] as $k => $item) {
                            $arr[$key]['childs'][$k]['childs'] = $this->_model->getlist(['parenshow_material_catcashow_material_catno' => $item['cashow_material_catno'],
                                'level_no' => 3,
                                'country_bn' => $country_bn,
                                'markeshow_material_catarea_bn' => $markeshow_material_catarea_bn,
                                'lang' => $lang]);
                        }
                    }
                }

                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($arr);
            } else {
                $condition['level_no'] = 2;
                $arr = $this->_model->getlist($condition);
                if ($arr) {

                    foreach ($arr[$key]['childs'] as $k => $item) {
                        $arr[$key]['childs'][$k]['childs'] = $this->_model->getlist(
                                ['parent_catno' => $item['cat_no'], 'level_no' => 3,
                                    'country_bn' => $country_bn,
                                    'marke_area_bn' => $markeshow_material_catarea_bn,
                                    'lang' => $lang]);
                    }

                    $this->setCode(MSG::MSG_SUCCESS);
                    $this->jsonReturn($arr);
                } else {
                    $condition['level_no'] = 3;
                    $arr = $this->_model->getlist($condition);
                    if ($arr) {

                        $this->setCode(MSG::MSG_SUCCESS);
                        $this->jsonReturn($arr);
                    } else {
                        $this->setCode(MSG::MSG_FAILED);
                        $this->jsonReturn();
                    }
                }
            }
        }


        $this->jsonReturn($data);
    }

    public function getlistAction() {

        $lang = $this->getPut('lang', 'en');
        $show_material_catno = $this->getPut('show_material_catno', '');
        $country_bn = $this->getPut('country_bn', '');
        $market_area_bn = $this->getPut('market_area_bn', '');

        $arr = $this->_model->get_list($market_area_bn, $country_bn, $show_material_catno, $lang);
        if ($arr) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $this->jsonReturn($data);
    }

    private function _setUserName(&$arr) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                if ($val['created_by']) {
                    $userids[] = $val['created_by'];
                }
                if ($val['updated_by']) {
                    $userids[] = $val['updated_by'];
                }
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                    $val['created_by_name'] = $usernames[$val['created_by']];
                } else {
                    $val['created_by_name'] = '';
                }

                if ($val['updated_by'] && isset($usernames[$val['updated_by']])) {
                    $val['updated_by_name'] = $usernames[$val['updated_by']];
                } else {
                    $val['updated_by_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /**
     * 分类详情
     */
    public function infoAction() {
        $cat_no = $this->getPut('cat_no');
        if (!$cat_no) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $data = [];
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $result = $this->_model->info($cat_no, $lang);
            $arr = [$result];
            $this->_setUserName($arr);
            $result = $arr[0];
            if ($result) {
                if (!$data['cat_no']) {
                    $data = array_merge($data, $result);
                    $data['name'] = $data['id'] = null;
                    unset($data['name'], $data['id']);
                }
                $data[$lang]['name'] = $result['name'];
            } else {
                $data[$lang]['name'] = '';
            }
        }

        $data['id'] = $cat_no;

        if ($data) {
            list($parent1, $parent2) = $this->_getparentcats($data);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setvalue('parent1', $parent1);
            $this->setvalue('parent2', $parent2);
            $this->_getmaterials($data);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);

            $this->jsonReturn();
        }
        exit;
    }

    /**
     * 获取详情的父类和顶级分类数据
     * @param array $data 详情数据
     * @return null
     * @author zyg
     *
     */
    private function _getmaterials(&$data) {

        if ($data['level_no'] == 3) {
            $show_material_catnos = $this->_model->Table('erui_goods.show_material_cat')
                    ->where(['show_cat_no' => $data['cat_no']])
                    ->field('material_cat_no')
                    ->select();

            $mcashow_material_catnos = [];
            foreach ($show_material_catnos as $mcashow_material_catno) {
                $mcashow_material_catnos[] = $mcashow_material_catno['material_cat_no'];
            }
            $material_cat_model = new MaterialCatModel();

            $material_cats = $material_cat_model->getmaterial_cats($mcashow_material_catnos, 'zh');

            $this->setvalue('count', 0);
            rsort($material_cats);
            $this->setvalue('material_cats', $material_cats);
        } else {
            $material_cats = null;

            $count = $this->_model->getCount(['parent_cat_no' => $data['cat_no'],
                'level_no' => ($data['level_no'] + 1),
                'lang' => 'zh']); //下级分类数量

            $this->setvalue('count', intval($count));
            $this->setvalue('material_cats', $material_cats);
        }
    }

    /**
     * 获取详情的父类和顶级分类数据
     * @param array $data 详情数据
     * @return null
     * @author zyg
     *
     */
    private function _getparentcats($data) {
        $parent2 = $parent1 = null;

        if ($data['level_no'] == 3) {
            $parent2 = $this->_model->info($data['parent_cat_no'], 'zh');
            $parent1 = $this->_model->info($parent2['parent_cat_no'], 'zh');
        } elseif ($data['level_no'] == 2) {
            $parent1 = $this->_model->info($data['parent_cat_no'], 'zh');

            $parent2 = null;
        }
        return [$parent1, $parent2];
    }

    private function delcache() {

        $redis = new phpredis();
        $treekeys = $redis->getKeys('show_cat*');
        $redis->delete($treekeys);
        unset($redis);
        $config = Yaf_Registry::get("config");
        $rconfig = $config->redis->config->toArray();
        $rconfig['dbname'] = 3;
        $redis3 = new phpredis($rconfig);
        $keys = $redis3->getKeys('ShowCats_*');
        $redis3->delete($keys);
        $eruikeys = $redis3->getKeys('eruiShowCats_*');
        $redis3->delete($eruikeys);
        unset($redis3);
    }

    /*
     * 删除缓存
     * @author zyg
     */

    private function _delCache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('ShowCat*');

        $redis->delete($keys);
    }

    function _exist($data, $lang, $level_no, $market_area_bn, $country_bn, $cat_no = null, $is_empty = true) {
        $langs = [
            'zh' => '中文',
            'es' => '西文',
            'ru' => '俄文',
            'en' => '英文',
        ];
        if (empty($market_area_bn)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('营销区域不能为空');
            $this->jsonReturn(false);
        }
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('国家不能为空');
            $this->jsonReturn();
        }
        if (empty($data[$lang]['name']) && $is_empty) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入' . $langs[$lang]);
            $this->jsonReturn();
        } elseif ($data[$lang]['name']) {

            $flag = $this->_model->showCatExist($data[$lang]['name'], $lang, $market_area_bn, $country_bn, $level_no, $cat_no);

            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage($langs[$lang] . '展示分类名称【' . $data[$lang]['name'] . '】 在同国家同等级展示分类中已存在!');
                $this->jsonReturn();
            }
        }
    }

    public function createAction() {
        $data = $this->getPut();
        if (empty($data['parent_cat_no'])) {
            $level_no = 1;
        } else {
            $info = $this->_model->where(['cat_no' => $data['parent_cat_no']])->find();
            if (intval($info['level_no'])) {
                $level_no = intval($info['level_no']) + 1;
            } else {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('父类分类编码对应的父类分类不存在!');
                $this->jsonReturn();
            }
        }
        $market_area_bn = $this->getPut('market_area_bn');
        $country_bn = $this->getPut('country_bn');
        $this->_exist($data, 'zh', $level_no, $market_area_bn, $country_bn);
        $this->_exist($data, 'en', $level_no, $market_area_bn, $country_bn);
        $this->_exist($data, 'es', $level_no, $market_area_bn, $country_bn, null, false);
        $this->_exist($data, 'ru', $level_no, $market_area_bn, $country_bn, null, false);
        $result = $this->_model->create_data($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateAction() {
        $data = $this->getPut();
        $cat_no = $this->getPut('cat_no');
        if (empty($cat_no)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('物料分类编码不能为空!');
            $this->jsonReturn();
        } else {
            $info = $this->_model->where(['cat_no' => $cat_no])->find();
            if (intval($info['level_no'])) {
                $level_no = intval($info['level_no']);
            } else {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('分类编码对应的分类不存在!');
                $this->jsonReturn();
            }
        }
        $market_area_bn = $this->getPut('market_area_bn');
        $country_bn = $this->getPut('country_bn');
        $this->_exist($data, 'zh', $level_no, $market_area_bn, $country_bn, $cat_no);
        $this->_exist($data, 'en', $level_no, $market_area_bn, $country_bn, $cat_no);
        $this->_exist($data, 'es', $level_no, $market_area_bn, $country_bn, $cat_no, false);
        $this->_exist($data, 'ru', $level_no, $market_area_bn, $country_bn, $cat_no, false);
        $result = $this->_model->update_data($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateiconAction() {
        $data = $this->getPut();
        $cat_no = $this->getPut('cat_no');
        if (empty($cat_no)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('物料分类编码不能为空!');
            $this->jsonReturn();
        } else {
            $info = $this->_model->where(['cat_no' => $cat_no])->find();
            if (intval($info['level_no'])) {

            } else {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('分类编码对应的分类不存在!');
                $this->jsonReturn();
            }
        }
        $result = $this->_model->updateico_data($cat_no, $data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {
        $cat_no = $this->getPut('cat_no');
        $lang = $this->getPut('lang', '');
        $result = $this->_model->delete_data($cat_no, $lang);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function approvingAction() {
        $data = $this->getPut();
        $result = $this->_model->approving($data['id']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /* 交换顺序
     *
     */

    public function changeorderAction() {
        $data = $this->getPut();
        $result = $this->_model->changeorder($data['cat_no'], $data['chang_cat_no']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * 产品导出
     */
    public function importAction() {
        set_time_limit(0);
        $showcat = new ShowCatModel();

        $localDir = $showcat->import();
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 产品导出
     */
    public function InsrtIntoShowCat3Action() {
        set_time_limit(0);
        $show_cat3 = $this->getPut('show_cat3');
        $market_area_bn = $this->getPut('market_area_bn');
        $country_bn = $this->getPut('country_bn');
        $cat_no2 = $this->getPut('cat_no2');
        $showcat = new ShowCatModel();

        $localDir = $showcat->InsrtIntoShowCat3($show_cat3, $market_area_bn, $country_bn, $cat_no2);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
