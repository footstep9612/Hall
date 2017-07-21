<?php

/**
  附件文档Controller
 */
class BrandController extends PublicController {

  public function init() {
    parent::init();

    $this->_model = new BrandModel();
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $name = $this->getPut('name', 'en');
    $current_no = $this->getPut('current_no', '1');
    $pagesize = $this->getPut('pagesize', '10');

    $key = 'brand_list_' . $lang . md5($name . $current_no . $pagesize);
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->getlist($name, $lang, $current_no, $pagesize);
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
   * 获取所有品牌
   */

  public function ListAllAction() {
    $lang = $this->getPut('lang', 'en');
    $name = $this->getPut('name', 'en');


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
    $cat_no = $this->getPut('brand_no');
    if (!$cat_no) {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
    $ret_en = $this->_model->info($cat_no, 'en');
    $ret_zh = $this->_model->info($cat_no, 'zh');
    $ret_es = $this->_model->info($cat_no, 'es');
    $ret_ru = $this->_model->info($cat_no, 'ru');
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
    if ($result) {
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn($result);
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

    $result = $this->_model->delete_data($this->put_data['brand_no'], $this->getLang());
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

    $result = $this->_model->batchdelete_data($this->put_data['brand_nos'], $this->getLang());
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
