<?php

/**
 * Description of FeeType
 * @author  zhongyg
 * @date    2017-8-1 17:34:40
 * @version V2.0
 * @desc  物料分类
 */
class MaterialcatController extends PublicController {

    public function init() {

        parent::init();
        if (!method_exists($this, $this->getRequest()->getActionName() . 'Action')) {
            $this->setCode(MSG::MSG_ERROR_ACTION);
            $this->jsonReturn();
        }
        $this->_model = new MaterialCatModel();
    }

    /*
     * 获取分类树形数据
     */

    public function treeAction() {
        $lang = $this->getPut('lang', 'zh');

        $jsondata = ['lang' => $lang];

        $redis_key = 'Material_cat_tree_' . $lang;
        $data = json_decode(redisGet($redis_key), true);
        if (!$data) {
            $arr = $this->_model->tree($jsondata);
            if ($arr) {

                redisSet($redis_key, json_encode($arr), 86400);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->_setCount($lang);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->_setCount($lang);
        $this->jsonReturn($data);
    }

    /*
     * 获取分类树形数据
     */

    public function TreesAction() {
        $lang = $this->getPut('lang', 'zh');

        $jsondata = ['lang' => $lang];

        $redis_key = 'Material_cat_trees_' . $lang;
        $data = json_decode(redisGet($redis_key), true);
        if (!$data) {
            $arr = (new Goods_MaterialCatModel())->tree($jsondata);
            if ($arr) {

                redisSet($redis_key, json_encode($arr), 86400);
                $this->setCode(MSG::MSG_SUCCESS);

                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);

        $this->jsonReturn($data);
    }

    /*
     * 获取分类树形数据
     */

    public function twotreeAction() {
        $lang = $this->getPut('lang', 'zh');

        $jsondata = ['lang' => $lang];

        $redis_key = 'Material_cat_twotree_' . $lang;
        $data = json_decode(redisGet($redis_key), true);
        if (!$data) {
            $arr = $this->_model->tree($jsondata, true);
            if ($arr) {
                $this->setCode(MSG::MSG_SUCCESS);
                redisSet($redis_key, json_encode($arr), 86400);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->_setCount($lang);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->_setCount($lang);
        $this->jsonReturn($data);
    }

    /**
     * 根据条件获取分类数量
     * @param string $lang 语言
     * @return null
     * @author zyg
     *
     */
    private function _setCount($lang) {
        $redis_key = 'Material_cat_count_' . $lang;
        list($count1, $count2, $count3) = json_decode(redisGet($redis_key), true);
        if ($count1 || $count2 || $count3) {
            $this->setvalue('count1', $count1);
            $this->setvalue('count2', $count2);
            $this->setvalue('count3', $count3);
        } else {
            $countData = ['lang' => $lang];
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

    /**
     * 根据条件获取分类对应的产品数量
     * @param array $cat_no 分类编码
     * @return null
     * @author zyg
     *
     */
    private function _getCount($cat_no, $lang = 'en') {
        $redis_key = 'Material_cat_spucount_' . md5(json_encode($cat_no));
        $data = json_decode(redisGet($redis_key), true);
        $materialcat_product_model = new Model('erui_goods.product');
        if (!$data) {
            $arr = $materialcat_product_model->where(['material_cat_no' => $cat_no, 'status' => 'VALID', 'lang' => $lang])->Count();
            if ($arr) {
                redisSet($redis_key, $arr, 86400);
                return $arr;
            } else {
                return 0;
            }
        }
        return $data;
    }

    /**
     * 根据分类编码获取SPU数量
     * @param array $cats 分类数据
     * @return null
     * @author zyg
     *
     */
    private function _getSpuCount(&$cats = [], $lang = 'en') {

        foreach ($cats AS $key => $one) {
            $one['spucount'] = $this->_getCount($one['cat_no'], $lang);
            $cats[$key] = $one;
        }
    }

    /**
     * 根据条件获取分类列表
     * @return null
     * @author zyg
     *
     */
    public function listAction() {

        $condition = $this->getPut();
        $condition['token'] = null;
        unset($condition['token']);
        $key = 'Material_cat_list_' . md5(json_encode($condition));
        $data = json_decode(redisGet($key), true);

        if (!$data) {
            $arr = $this->_model->getlist($condition);

            if ($arr) {
                $this->_getSpuCount($arr, $condition['lang']);
                redisSet($key, json_encode($arr), 86400);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::ERROR_EMPTY);
                $this->jsonReturn();
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);

        $this->jsonReturn($data);
    }

    /**
     * 分类联动
     * @return null
     * @author zyg
     *
     */
    public function getlistAction() {
        $lang = $this->getPut('lang', 'zh');
        $cat_no = $this->getPut('cat_no', '');
        $name = $this->getPut('name', '');
        $key = 'Material_cat_getlist_' . (!empty($lang) ? '_' . $lang : '')
                . (!empty($cat_no) ? '_' . $cat_no : '')
                . (!empty($name) ? '_' . md5($name) : '');
        $data = json_decode(redisGet($key), true);
        if (!$data) {
            $arr = $this->_model->get_list($cat_no, $lang);

            redisSet($key, json_encode($arr), 86400);
            if ($arr) {
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($arr);
            } else {
                $this->setCode(MSG::ERROR_EMPTY);
                $this->jsonReturn();
            }
        }
        $this->jsonReturn($data);
    }

    /**
     * 分类详情
     * @return null
     * @author zyg
     *
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
            if ($result) {
                if (!$data['cat_no']) {
                    // $data = $result;
                    $data = array_merge($data, $result);
                    $data['name'] = $data['id'] = null;
                    unset($data['name'], $data['id']);
                }
                $data[$lang]['name'] = $result['name'];
            } else {
                $data[$lang]['name'] = '';
            }
        }
        $arr = [$data];
        $this->_setUserName($arr, ['created_by', 'updated_by']);
        $data = $arr[0];
        $data['id'] = $cat_no;
        if ($data) {
            list($parent1, $parent2) = $this->_getparentcats($data);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setvalue('parent1', $parent1);
            $this->setvalue('parent2', $parent2);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);

            $this->jsonReturn(null);
        }
        exit;
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setUserName(&$arr, $fileds) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {

                foreach ($fileds as $filed) {
                    $userids[] = $val[$filed];
                }
            }

            $usernames = $employee_model->getUserNamesByUserids($userids);

            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if ($val[$filed] && isset($usernames[$val[$filed]])) {
                        $val[$filed . '_name'] = $usernames[$val[$filed]];
                    } else {
                        $val[$filed . '_name'] = '';
                    }
                }
                $arr[$key] = $val;
            }
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
//            $parent_cats = $this->_model->get_list($result['parent_cat_no'], 'zh');
//            $top_cats = $this->_model->get_list(0, 'zh');
//            foreach ($parent_cats as $key => $item) {
//                if ($item['cat_no'] == $result['cat_no']) {
//                    $item['checked'] = true;
//                } else {
//                    $item['checked'] = false;
//                }
//                $parent_cats[$key] = $item;
//            }
//            foreach ($top_cats as $key => $item) {
//                if ($item['cat_no'] == $result['parent_cat_no']) {
//                    $item['checked'] = true;
//                } else {
//                    $item['checked'] = false;
//                }
//                $top_cats[$key] = $item;
//            }
        } elseif ($data['level_no'] == 2) {
            $parent1 = $this->_model->info($data['parent_cat_no'], 'zh');
            $parent2 = null;
//            foreach ($top_cats as $key => $item) {
//                if ($item['cat_no'] == $data['parent_cat_no']) {
//                    $item['checked'] = true;
//                } else {
//                    $item['checked'] = false;
//                }
//                $top_cats[$key] = $item;
//            }
        }
        return [$parent1, $parent2];
    }

    /**
     * 获取详情的父类和顶级分类数据
     * @param array $data 详情数据
     * @return null
     * @author zyg
     *
     */
    public function getInfoAction() {
        $cat_no = $this->getPut('cat_no');
        $lang = $this->getPut('lang', 'zh');
        if (!$cat_no) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $result = $this->_model->getinfo($cat_no, $lang);
        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } elseif ($result !== false) {
            $this->setCode(MSG::ERROR_EMPTY);

            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn(null);
        }
        exit;
    }

    /*
     * 删除缓存
     * @author zyg
     */

    private function _delCache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Material_cat*');

        $redis->delete($keys);
    }

    function _exist($data, $lang, $level_no, $cat_no = null, $is_empty = true) {
        $langs = [
            'zh' => '中文',
            'es' => '西文',
            'ru' => '俄文',
            'en' => '英文',
        ];

        if (empty($data[$lang]['name']) && $is_empty) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入' . $langs[$lang]);
            $this->jsonReturn();
        } elseif ($data[$lang]['name']) {

            $flag = $this->_model->MaterialcatExist($data[$lang]['name'], $lang, $level_no, $cat_no);
            if ($flag) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage($langs[$lang] . '物料分类名称【' . $data[$lang]['name'] . '】 在同级物料分类中已存在!');
                $this->jsonReturn();
            }
        }
    }

    /*
     * 新建分类
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

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

        $this->_exist($data, 'zh', $level_no);
        $this->_exist($data, 'en', $level_no);
        $this->_exist($data, 'es', $level_no, null, false);
        $this->_exist($data, 'ru', $level_no, null, false);
        $result = $this->_model->create_data($data);
        if ($result) {
            $this->_delCache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 修改分类
     * @author zyg
     */

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
        $this->_exist($data, 'zh', $level_no, $cat_no);
        $this->_exist($data, 'en', $level_no, $cat_no);
        $this->_exist($data, 'es', $level_no, $cat_no, false);
        $this->_exist($data, 'ru', $level_no, $cat_no, false);
        $result = $this->_model->update_data($data);

        if ($result) {
            $this->_delCache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 删除分类
     * @author zyg
     */

    public function deleteAction() {
        $cat_no = $this->getPut('cat_no');
        $lang = $this->getPut('lang');
        $product_model = new ProductModel();
        /**
         * 更新于2018-07-16
         * $data = $product_model->where(['material_cat_no' => ['like', $cat_no . '%']])->find();
         */
        $data = $product_model->where(['material_cat_no' => $cat_no])->find();

        if ($data) {
            $this->setCode(MSG::DELETE_MATERIAL_CAT_ERR);
            $this->jsonReturn();
        }
        $result = $this->_model->delete_data($cat_no, $lang);
        if ($result) {
            $this->_delCache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 审核分类
     * @author zyg
     */

    public function approvingAction() {
        $cat_no = $this->getPut('cat_no');
        $lang = $this->getPut('lang');
        $result = $this->_model->approving($cat_no, $lang);

        if ($result) {
            $this->_delCache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * 交换分类顺序
     * @author zyg
     */

    public function changeorderAction() {
        $result = $this->_model->changecat_sort_order($this->put_data['cat_no'], $this->put_data['chang_cat_no']);
        if ($result) {
            $this->_delCache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * 导出分类
     */
    public function exportAction() {
        $data = $this->getPut();
        $materialcat_model = new MaterialCatModel();
        $localDir = $materialcat_model->export($data, $this->user);
        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
