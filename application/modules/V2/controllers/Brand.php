<?php

/**
  附件文档Controller
 */
class BrandController extends PublicController {

  public function init() {
    //parent::init();

    $this->_model = new BrandModel();
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $name = $this->getPut('name', '');
    $current_no = $this->getPut('current_no', '1');
    $pagesize = $this->getPut('pagesize', '10');

    $brand_key = 'brand_list_' . $lang . md5($name . $current_no . $pagesize);
    $data = json_decode(redisGet($brand_key), true);
    if (!$data) {
      $arr = $this->_model->getlist($name, $lang, $current_no, $pagesize);
      $brand_nos = [];
      foreach ($arr as $brandinfo) {
        $brand_nos[] = $brandinfo['brand_no'];
      }
      $Supplier_brand_model = new SupplierBrandModel();
      $Suppliers = $Supplier_brand_model->getlistbybrands($brand_nos);
      $supper_namebybrands = [];
      $supper_idbybrands = [];
      foreach ($Suppliers as $Supplier) {
        $supper_namebybrands[$Supplier['brand']] = $Supplier['name'];
        $supper_idbybrands[$Supplier['brand']] = $Supplier['supplier_id'];
      }
      foreach ($arr as $key => $brand) {
        $arr[$key]['supplier_id'] = $supper_idbybrands[$brand['brand_no']];
        $arr[$key]['supplier_name'] = $supper_namebybrands[$brand['brand_no']];
      }
      if ($arr) {
        redisSet($brand_key, json_encode($arr), 86400);
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
    $this->setCode(MSG::MSG_SUCCESS);
    $this->jsonReturn($data);
  }

  /*
   * 获取所有品牌
   */

  public function ListAllAction() {
    $lang = $this->getPut('lang', 'en');
    $name = $this->getPut('name', '');


    $key = 'brand_list_' . $lang . md5($name);
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->listall($name, $lang);

      redisSet($key, json_encode($arr), 86400);
      if ($arr) {
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn($arr);
      } else {
        $this->setCode(MSG::MSG_FAILED);
        $this->jsonReturn();
      }
    }
    $this->jsonReturn($data);
  }

  /*
   * 验重
   */

  public function checknameAction() {
    $name = $this->getPut('name');
    $exclude = $this->getPut('exclude');

    $lang = $this->getPut('lang', 'en');
    if ($exclude == $name) {
      $this->setCode(1);
      $data = true;
      $this->jsonReturn($data);
    } else {
      $info = $this->model->Exist(['name' => $name, 'lang' => $lang]);

      if ($info) {
        $this->setCode(1);
        $data = false;
        $this->jsonReturn($data);
      } else {
        $this->setCode(1);
        $data = true;
        $this->jsonReturn($data);
      }
    }
  }

  /**
   * 分类联动
   */
  public function infoAction() {
    $brand_no = 'AMCS';
    $this->getPut('brand_no');
    if (!$brand_no) {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
    $ret_en = $this->_model->info($brand_no, 'en');
    $ret_zh = $this->_model->info($brand_no, 'zh');
    $ret_es = $this->_model->info($brand_no, 'es');
    $ret_ru = $this->_model->info($brand_no, 'ru');

    $result = !empty($ret_en) ? $ret_en : (!empty($ret_zh) ? $ret_zh : (empty($ret_es) ? $ret_es : $ret_ru));

    if ($ret_en) {
      $result['en']['name'] = $ret_en['name'];
    }
    if ($ret_zh) {
      $result['zh']['name'] = $ret_zh['name'];
    }
    if ($ret_ru) {
      $result['ru']['name'] = $ret_ru['name'];
    }
    if ($ret_es) {
      $result['es']['name'] = $ret_es['name'];
    }
    unset($result['id']);
    unset($result['lang']);
    unset($result['name']);
    if ($result) {
      $Supplier_brand_model = new SupplierBrandModel();
      $Suppliers = $Supplier_brand_model->getlistbybrand($brand_no);
      $result['Suppliers'] = $Suppliers;
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
    $result = $this->_model->create_data($this->put_data, $this->user['username']);
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
    $result = $this->_model->update_data($this->put_data, $this->user['username']);
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

    $result = $this->_model->delete_data($this->put_data['id']);
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

    $result = $this->_model->batchdelete_data( $this->put_data['ids']);
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
