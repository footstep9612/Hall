<?php

/**
  附件文档Controller
 */
class BrandController extends PublicController {

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function init() {
        parent::init();
    }

    public function listAction() {

        $condition = $this->getPut();
        $lang = $this->getPut('lang', '');
        unset($condition['token']);

        $brand_model = new BrandModel();
        $arr = $brand_model->getlist($condition, $lang);

        foreach ($arr as $key => $item) {
            $brands = json_decode($item['brand'], true);
            foreach ($this->langs as $blang) {
                $brand[$blang] = null;
            }
            $brand = [];
            foreach ($brands as $val) {
                $brand[$val['lang']] = $val;
                $brand[$val['lang']]['id'] = $item['id'];
               
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
            $this->setvalue('count', 0);
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
        $lang = $this->getPut('lang', '');

        $brand_model = new BrandModel();
        $arr = $brand_model->listall($condition, $lang);
        foreach ($arr as $key => $item) {
            $brands = json_decode($item['brand'], true);
            $brand = [];
            foreach ($this->langs as $lang) {
                $brand[$lang] = null;
            }
            foreach ($brands as $val) {
                $brand[$val['lang']] = $val;
                $brand[$val['lang']]['id'] = $item['id'];
              
            }

            $arr[$key] = $brand;
        }


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
        $keys = $redis->getKeys('Brand');
        $redis->delete($keys);
    }

    public function createAction() {
        $brand_model = new BrandModel();
        $data = $this->getPut();
        $result = $brand_model->create_data($data, $this->user['id']);
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
        $result = $brand_model->update_data($data, $this->user['id']);
        if ($result !== false) {
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
        $ids = $this->get('ids') ?: $this->getPut('ids');
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
