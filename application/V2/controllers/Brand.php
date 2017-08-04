<?php

/**
  附件文档Controller
 */
class BrandController extends PublicController {

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function init() {
        // parent::init();
    }

    public function listAction() {

        $condition = $this->getPut();
        $lang = $this->getPut('lang', '');
        unset($condition['token']);

        $brand_key = 'brand_list_' . md5(json_encode($condition));
        $data = json_decode(redisGet($brand_key), true);
        $brand_model = new BrandModel();
        if (!$data) {

            $arr = $brand_model->getlist($condition, $lang);
            foreach ($arr as $key => $item) {
                $brands = json_decode($item['brand'], true);
                foreach ($this->langs as $lang) {
                    $item[$lang] = [];
                }
                foreach ($brands as $val) {
                    $item[$val['lang']] = $val;
                }
                unset($item['brand']);
                $arr[$key] = $item;
            }
            if ($arr) {
                redisSet($brand_key, json_encode($arr), 86400);
                $count = $brand_model->getCount($condition, $lang);
                $this->setvalue('count', $count);
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
        $count = $brand_model->getCount($condition, $lang);
        $this->setvalue('count', $count);
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn($data);
    }

    /*
     * 获取所有品牌
     */

    public function ListAllAction() {

        $condition = $this->getPut();
        $lang = $this->getPut('lang', '');


        $key = 'brand_list_' . md5($name);
        $data = json_decode(redisGet($key), true);
        if (!$data) {
            $brand_model = new BrandModel();
            $arr = $brand_model->listall($condition, $lang);
            foreach ($arr as $key => $item) {
                $brands = json_decode($item['brand'], true);
                foreach ($this->langs as $lang) {
                    $item[$lang] = [];
                }
                foreach ($brands as $val) {
                    $item[$val['lang']] = $val;
                }
                unset($item['brand']);
                $arr[$key] = $item;
            }
            redisSet($key, json_encode($arr), 86400);
            if ($arr) {
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
        $this->jsonReturn($data);
    }

    /**
     * 分类联动
     */
    public function infoAction() {
        $id = $this->get('id');
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
        foreach ($brands as $val) {
            $result[$val['lang']] = $val;
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
        $keys = $redis->getKeys('brand_*');
        $redis->delete($keys);
    }

    public function createAction() {
        $brand_model = new BrandModel();
        $data = $this->getPut();
        $result = $brand_model->create_data($data, $this->user['id']);
        if ($result) {
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
        $result = $brand_model->update_data($data, $this->user['id']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {
        $brand_model = new BrandModel();
        $id = $this->get('id') ?: $this->getPut('id');
        $result = $brand_model->delete_data($id);
        if ($result) {
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
        $ids = $this->get('ids') ?: $this->getPut('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $result = $brand_model->batchdelete_data($ids);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
