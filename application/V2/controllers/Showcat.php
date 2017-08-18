<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

    public function init() {
        parent::init();
        $this->_model = new ShowCatModel();
    }

    public function treeAction() {
        ini_set('memory_limit', '800M');
        $lang = $this->getPut('lang', 'zh');
        $jsondata = ['lang' => $lang];
        $jsondata['level_no'] = 1;
        $country_bn = $this->get('country_bn', '') ?: $this->getPut('country_bn', '');
        $marke_area_bn = $this->get('marke_area_bn', '') ?: $this->getPut('marke_area_bn', '');
        $jsondata['country_bn'] = $country_bn;
        $jsondata['marke_area_bn'] = $marke_area_bn;
        $redis_key = 'show_cat_tree_' . $lang . md5($country_bn . $marke_area_bn);
        $data = json_decode(redisGet($redis_key), true);
        if (!$data) {
            $arr = $this->_model->tree($jsondata);
            if ($arr) {
                $this->setCode(MSG::MSG_SUCCESS);
                foreach ($arr as $key => $val) {
                    $children_data = $jsondata;
                    $children_data['level_no'] = 2;
                    $children_data['parent_catno'] = $val['value'];
                    $arr[$key]['children'] = $this->_model->tree($children_data);
                    if ($arr[$key]['children']) {
                        foreach ($arr[$key]['children'] as $k => $item) {
                            $children_data['level_no'] = 3;
                            $children_data['parent_catno'] = $item['value'];
                            $arr[$key]['children'][$k]['children'] = $this->_model->tree($children_data);
                        }
                    }
                }
                redisSet($redis_key, json_encode($arr), 86400);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->_setCount($lang, $country_bn, $marke_area_bn);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::ERROR_EMPTY);
                $this->jsonReturn($arr);
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->_setCount($lang, $country_bn, $marke_area_bn);
        $this->jsonReturn($data);
    }

    /**
     * 根据条件获取查询条件
     * @param string $lang 语言
     * @return null
     * @author zyg
     *
     */
    private function _setCount($lang, $country_bn, $marke_area_bn) {
        $redis_key = 'show_cat' . $lang;
        list($count1, $count2, $count3) = json_decode(redisGet($redis_key), true);
        if ($count1 || $count2 || $count3) {
            $this->setvalue('count1', $count1);
            $this->setvalue('count2', $count2);
            $this->setvalue('count3', $count3);
        } else {
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
            $this->setvalue('count1', $count1);
            $this->setvalue('count2', $count2);
            $this->setvalue('count3', $count3);
            redisSet($redis_key, json_encode([$count1, $count2, $count3]), 86400);
        }
    }

    public function listAction() {
        $lang = $this->getPut('lang', 'en');
        $jsondata = ['lang' => $lang];
        $jsondata['level_no'] = 1;
        $country_bn = $this->getPut('country_bn', '');
        $marke_area_bn = $this->getPut('marke_area_bn', '');
        $jsondata['country_bn'] = $country_bn;
        $jsondata['marke_area_bn'] = $marke_area_bn;
        $jsondata['cat_no1'] = $this->get('cat_no1', '') ?: $this->getPut('cat_no1', '');
        $jsondata['cat_no2'] = $this->get('cat_no2', '') ?: $this->getPut('cat_no2', '');
        $jsondata['cat_no3'] = $this->get('cat_no3', '') ?: $this->getPut('cat_no3', '');
        $condition = $jsondata;
        $key = 'Show_cat_' . $lang . '_' . md5(json_encode($condition));
        $data = json_decode(redisGet($key), true);
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
                redisSet($key, json_encode($arr), 86400);
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
                    redisSet($key, json_encode($arr), 86400);
                    $this->setCode(MSG::MSG_SUCCESS);
                    $this->jsonReturn($arr);
                } else {
                    $condition['level_no'] = 3;
                    $arr = $this->_model->getlist($condition);
                    if ($arr) {
                        redisSet($key, json_encode($arr), 86400);
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

        $lang = $this->get('lang') ?: $this->getPut('lang', 'en');
        $show_material_catno = $this->get('show_material_catno', '') ?: $this->getPut('show_material_catno', '');
        $country_bn = $this->get('country_bn', '') ?: $this->getPut('country_bn', '');
        $market_area_bn = $this->get('market_area_bn', '') ?: $this->getPut('market_area_bn', '');
        $key = 'show_material_cat_' . $lang . '_' . $show_material_catno;

        $data = json_decode(redisGet($key), true);
        if (!$data) {
            $arr = $this->_model->geshow_material_catlist($market_area_bn, $country_bn, $show_material_catno, $lang);
            if ($arr) {
                redisSet($key, json_encode($arr), 86400);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }
        $this->jsonReturn($data);
    }

    /**
     * 分类详情
     */
    public function infoAction() {
        $cat_no = $this->get('cat_no') ?: $this->getPut('cat_no');
        if (!$cat_no) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $data = [];
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $result = $this->_model->info($cat_no, $lang);

            if ($result) {
                if (!$data) {
                    $data = $result;
                    $data['name'] = $data['id'] = null;
                    unset($data['name'], $data['id']);
                }
                $data[$lang]['name'] = $result['name'];
            }
        }


        if ($data) {
            list($top_cats, $parent_cats) = $this->_getparentcats($data);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setvalue('top_cats', $top_cats);
            $this->setvalue('parent_cats', $parent_cats);
            if ($data['level_no'] == 3) {
                $show_material_catnos = $this->_model->Table('erui2_goods.show_material_cat')
                        ->where(['show_cat_no' => $cat_no])
                        ->field('material_cat_no')
                        ->select();
                $mcashow_material_catnos = [];
                foreach ($show_material_catnos as $mcashow_material_catno) {
                    $mcashow_material_catnos = $mcashow_material_catno['material_cat_no'];
                }

                $es_producshow_material_catmodel = new EsProductModel();
                $material_cats = $es_producshow_material_catmodel->getmaterial_cats($mcashow_material_catnos, 'zh');
            } else {
                $material_cats = null;
            }
            $this->setvalue('material_cats', $material_cats);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);

            $this->jsonReturn();
        }
        exit;
    }

    private function _getparentcats($data) {
        $parenshow_material_catcats = $top_cats = null;
        if ($data['level_no'] == 3) {
            $result = $this->_model->info($data['parent_cat_no'], 'zh');
            $parenshow_material_catcats = $this->_model->getlist($result['market_area_bn'], $result['country_bn'], $result['parent_catno'], 'zh');
            $top_cats = $this->_model->getlist($result['market_area_bn'], $result['country_bn'], '', 'zh');

            foreach ($parenshow_material_catcats as $key => $item) {
                if ($item['cat_no'] == $result['parent_cat_no']) {
                    $item['checked'] = true;
                } else {
                    $item['checked'] = false;
                }
                $parenshow_material_catcats[$key] = $item;
            }
            foreach ($top_cats as $key => $item) {
                if ($item['cat_no'] == $result['parent_cat_no']) {
                    $item['checked'] = true;
                } else {
                    $item['checked'] = false;
                }
                $top_cats[$key] = $item;
            }
        } elseif ($data['level_no'] == 2) {
            $top_cats = $this->_model->getlist($data['parent_cat_no'], 'zh');
            foreach ($top_cats as $key => $item) {
                if ($item['cat_no'] == $data['parent_cat_no']) {
                    $item['checked'] = true;
                } else {
                    $item['checked'] = false;
                }
                $top_cats[$key] = $item;
            }
        }
        return [$top_cats, $parenshow_material_catcats];
    }

    private function delcache() {
        $redis = new phpredis();
        $treekeys = $redis->getKeys('show_cat_*');
        $redis->delete($treekeys);
    }

    public function createAction() {
        $data = $this->getPut();
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

}
