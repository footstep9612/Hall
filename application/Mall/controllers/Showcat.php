<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
    }

    public function InfoAction() {


        $lang = $this->getPut('lang', 'en');
        if (empty($lang)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('语言不能为空');
            $this->jsonReturn();
        }
        $country_bn = $this->getPut('country_bn', '');

        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }
        $cat_no = $this->getPut('cat_no', '');
        if (empty($cat_no)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('分类编码不能为空!');
            $this->jsonReturn();
        }


        $show_model = new ShowCatModel();
        $data = [];
        $arr = $show_model->info($cat_no, $country_bn, $lang);
        if ($arr) {
            $data[] = $arr;
        }
        if (!empty($arr['parent_cat_no'])) {
            $arr_top = $show_model->info($arr['parent_cat_no'], $country_bn, $lang);
            if ($arr_top) {
                array_unshift($data, $arr_top);
            }
        }
        if (!empty($arr_top['parent_cat_no'])) {
            $arr_top_top = $show_model->info($arr_top['parent_cat_no'], $country_bn, $lang);
            if ($arr_top_top) {
                array_unshift($data, $arr_top_top);
            }
        }
        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {

            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn($arr);
        }
    }

    public function InfoBySpuAction() {


        $lang = $this->getPut('lang', 'en');
        if (empty($lang)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('语言不能为空');
            $this->jsonReturn();
        }
        $country_bn = $this->getPut('country_bn', '');

        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }
        $spu = $this->getPut('spu', '');
        if (empty($spu)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('产品编码不能为空!');
            $this->jsonReturn();
        }

        $show_model = new ShowCatModel();
        $show_product_model = new ShowCatProductModel();
        $data = [];
        $arr = $show_product_model->getShowcatnosByspu($spu, $country_bn, $lang);
        if ($arr) {
            $data[] = $arr;
        }
        if (!empty($arr['parent_cat_no'])) {
            $arr_top = $show_model->info($arr['parent_cat_no'], $country_bn, $lang);
            if ($arr_top) {
                array_unshift($data, $arr_top);
            }
        }
        if (!empty($arr_top['parent_cat_no'])) {
            $arr_top_top = $show_model->info($arr_top['parent_cat_no'], $country_bn, $lang);
            if ($arr_top_top) {
                array_unshift($data, $arr_top_top);
            }
        }
        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {

            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn($arr);
        }
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

        $show_model = new ShowCatModel();
        $arr = $show_model->tree($jsondata);

        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            foreach ($arr as $key => $val) {
                $children_data = $jsondata;
                $children_data['level_no'] = 2;
                $children_data['parent_cat_no'] = $val['value'];
                $arr[$key]['children'] = $show_model->tree($children_data);
                if ($arr[$key]['children']) {
                    foreach ($arr[$key]['children'] as $k => $item) {
                        $children_data['level_no'] = 3;
                        $children_data['parent_cat_no'] = $item['value'];
                        $arr[$key]['children'][$k]['children'] = $show_model->tree($children_data);
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
        $show_model = new ShowCatModel();
        $countData['level_no'] = 1;
        $count1 = $show_model->getCount($countData); //一级分类数据

        $countData['level_no'] = 2;
        $count2 = $show_model->getCount($countData); //二级分类数据
        $countData['level_no'] = 3;
        $count3 = $show_model->getCount($countData); //三级分类数据
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

        $show_model = new ShowCatModel();

        $arr = $show_model->getlist($jsondata);
        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            foreach ($arr as $key => $val) {
                $arr[$key]['childs'] = $show_model->getlist(
                        ['parent_cat_no' => $val['cat_no'],
                            'level_no' => 2,
                            'country_bn' => $country_bn,
                            'lang' => $lang]);

                if ($arr[$key]['childs']) {
                    foreach ($arr[$key]['childs'] as $k => $item) {
                        $arr[$key]['childs'][$k]['childs'] = $show_model->getlist(['parent_cat_no' => $item['cat_no'],
                            'level_no' => 3,
                            'country_bn' => $country_bn,
                            'lang' => $lang]);
                    }
                }
            }

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } else {
            $condition['level_no'] = 2;
            $arr = $show_model->getlist($condition);
            if ($arr) {

                foreach ($arr[$key]['childs'] as $k => $item) {
                    $arr[$key]['childs'][$k]['childs'] = $show_model->getlist(
                            ['parent_cat_no' => $item['cat_no'], 'level_no' => 3,
                                'country_bn' => $country_bn,
                                'lang' => $lang]);
                }

                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($arr);
            } else {
                $condition['level_no'] = 3;
                $arr = $show_model->getlist($condition);
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

    public function getlistAction() {

        $lang = $this->getPut('lang', 'en');
        $show_cat_no = $this->getPut('show_cat_no', '');
        $country_bn = $this->getPut('country_bn', '');


        $show_model = new ShowCatModel();
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空');
            $this->jsonReturn();
        }

        $arr = $show_model->get_list($country_bn, $show_cat_no, $lang);
        if ($arr) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $this->jsonReturn($arr);
    }

    public function getListByLetterAction() {

        $lang = $this->getPut('lang', 'en');
        $letter = $this->getPut('letter', '');
        $country_bn = $this->getPut('country_bn', '');
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空!');
            $this->jsonReturn();
        }


        $show_model = new ShowCatModel();
        $arr = $show_model->getListByLetter($country_bn, $letter, $lang);
        if ($arr) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $this->jsonReturn($arr);
    }

    public function getListByLetterExitAction() {

        $data = $this->getPut();
        if (empty($data['country_bn'])) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('国家简称不能为空!');
            $this->jsonReturn();
        }
        $show_model = new ShowCatModel();
        $newletter = [];
        foreach ($data['letters'] as $letter) {
            $flag = $show_model->getListByLetterExit($data['country_bn'], $letter, $data['lang']);
            if ($flag) {
                $newletter[] = $letter;
            }
        }
        if ($newletter) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($newletter);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $this->jsonReturn($newletter);
    }

    /**
     * Description of 判断当前国家是否存在现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货国家
     */
    public function getExitAction() {

        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('请选择国家!');
            $this->jsonReturn(null);
        }
        $lang = $this->getPut('lang', 'en');
        if (empty($lang)) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('请选择语言!');
            $this->jsonReturn(null);
        }
        $show_model = new ShowCatModel();

        $list = $show_model->getExit($country_bn, $lang);

        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
