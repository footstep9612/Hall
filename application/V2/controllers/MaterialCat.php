<?php

/**
 * 产品与物料分类对应表
 * zyg
 */
class MaterialCatController extends PublicController {

    public function init() {
        // parent::init();
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
        $lang = $this->get('lang', 'zh');

        $jsondata = ['lang' => $lang];
        $jsondata['level_no'] = 1;
        $condition = $jsondata;
        $redis_key = 'Material_cat_tree_' . $lang;
        $data = json_decode(redisGet($redis_key), true);
        if (!$data) {
            $arr = $this->_model->tree($jsondata);
            if ($arr) {
                $this->setCode(MSG::MSG_SUCCESS);
                foreach ($arr as $key => $val) {
                    $arr[$key]['children'] = $this->_model->tree(['parent_cat_no' => $val['value'], 'level_no' => 2, 'lang' => $lang]);

                    if ($arr[$key]['children']) {
                        foreach ($arr[$key]['children'] as $k => $item) {
                            $arr[$key]['children'][$k]['children'] = $this->_model->tree(['parent_cat_no' => $item['value'],
                                'level_no' => 3,
                                'lang' => $lang]);
                        }
                    }
                }
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
    private function _getCount($cat_no) {
        $redis_key = 'Material_cat_spucount_' . md5(json_encode($cat_no));
        $data = json_decode(redisGet($redis_key), true);
        $materialcat_product_model = new MaterialcatproductModel();
        if (!$data) {
            $arr = $materialcat_product_model->getCount($cat_no);
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
    private function _getSpuCount(&$cats = []) {

        foreach ($cats AS $key => $one) {
            $one['spucount'] = $this->_getCount($one['cat_no']);
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

        $condition = $this->get();
        $condition['token'] = null;
        unset($condition['token']);
        $key = 'Material_cat_list_' . md5(json_encode($condition));
        $data = json_decode(redisGet($key), true);

        if (!$data) {
            $arr = $this->_model->getlist($condition);
            if ($arr) {
                $this->_getSpuCount($arr);
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
        $lang = $this->get('lang', 'en');
        $cat_no = $this->get('cat_no', '');
        $key = 'Material_cat_getlist_' . $lang . '_' . $cat_no;
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
        $cat_no = $this->get('id');
        if (!$cat_no) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $data = [];
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $result = $this->_model->info($cat_no, $lang);
            if ($result) {
                $data = $result;
                $data['name'] = $data['id'] = null;
                unset($data['name'], $data['id']);
                $data[$lang]['name'] = $result['name'];
            }
        }

        if ($data) {
            list($top_cats, $parent_cats) = $this->_getparentcats($data);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setvalue('top_cats', $top_cats);
            $this->setvalue('parent_cats', $parent_cats);
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
    private function _getparentcats($data) {
        $parent_cats = $top_cats = null;
        if ($data['level_no'] == 3) {
            $result = $this->_model->info($data['parent_cat_no'], 'zh');
            $parent_cats = $this->_model->get_list($result['parent_cat_no'], 'zh');
            $top_cats = $this->_model->get_list(0, 'zh');
            foreach ($parent_cats as $key => $item) {
                if ($item['cat_no'] == $result['cat_no']) {
                    $item['checked'] = true;
                } else {
                    $item['checked'] = false;
                }
                $parent_cats[$key] = $item;
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
            $top_cats = $this->_model->get_list($data['parent_cat_no'], 'zh');
            foreach ($top_cats as $key => $item) {
                if ($item['cat_no'] == $data['parent_cat_no']) {
                    $item['checked'] = true;
                } else {
                    $item['checked'] = false;
                }
                $top_cats[$key] = $item;
            }
        }
        return [$top_cats, $parent_cats];
    }

    /*
     * 删除缓存
     * @author zyg
     */

    private function _delCache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Material_cat*');
        var_dump($keys);
        $redis->delete($keys);
    }

    /*
     * 新建分类
     * @author zyg
     */

    public function createAction() {
        $result = $this->_model->create_data($this->put_data, $this->user['username']);
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
        $result = $this->_model->update_data($this->put_data, $this->user['username']);

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
        $cat_no = $this->get('id');
        $lang = $this->get('lang');
        $product_model = new ProductModel();
        $data = $product_model->where(['meterial_cat_no' => ['like', $cat_no . '%']])
                ->find();

        if ($data) {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('该分类下存在产品,不能删除');
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

        $result = $this->_model->approving($this->put_data['cat_no'], $this->getLang());
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

}
