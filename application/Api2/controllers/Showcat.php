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
            //    parent::init();
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

        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }

        $jsondata['country_bn'] = $country_bn;


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
            $this->_setCount($lang, $country_bn);
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
    private function _setCount($lang, $country_bn) {



        $countData = ['lang' => $lang,
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


        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }

        $jsondata['country_bn'] = $country_bn;

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
                            ['parent_cat_no' => $val['cat_no'],
                                'level_no' => 2,
                                'country_bn' => $country_bn,
                                'lang' => $lang]);

                    if ($arr[$key]['childs']) {
                        foreach ($arr[$key]['childs'] as $k => $item) {
                            $arr[$key]['childs'][$k]['childs'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'],
                                'level_no' => 3,
                                'country_bn' => $country_bn,
                                //  'markeshow_material_catarea_bn' => $markeshow_material_catarea_bn,
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
                                ['parent_cat_no' => $item['cat_no'], 'level_no' => 3,
                                    'country_bn' => $country_bn,
                                    //    'marke_area_bn' => $markeshow_material_catarea_bn,
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
        $show_cat_no = $this->getPut('show_cat_no', '');
        $country_bn = $this->getPut('country_bn', '');



        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }
//        $market_area_bn = $this->getPut('market_area_bn', '');
//
//        if (empty($market_area_bn)) {
//            $this->setCode(MSG::ERROR_EMPTY);
//            $this->setMessage('营销区域简称不能为空');
//            $this->jsonReturn();
//        }
        $arr = $this->_model->get_list($country_bn, $show_cat_no, $lang);
        if ($arr) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $this->jsonReturn($arr);
    }

}
