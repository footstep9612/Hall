<?php

/**
  附件文档Controller
 */
class MarketareaController extends PublicController {

  public function init() {
  //  parent::init();

    $this->_model = new MarketAreaModel();
  }

  /*
   * 营销区域列表
   */

  public function listAction() {
    $lang = $this->getPut('lang', 'en');

    $condition = $this->put_data;
    unset($condition['token']);
    rsort($condition);
    $key = 'market_area_list_' . $lang . md5(json_encode($condition));
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->getlist($this->put_data, $lang);
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
   * 详情
   */
  public function infoAction() {
    $bn = $this->getPut('bn');
    if (!$bn) {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
    $ret_en = $this->_model->info($bn, 'en');
    $ret_zh = $this->_model->info($bn, 'zh');
    $ret_es = $this->_model->info($bn, 'es');
    $ret_ru = $this->_model->info($bn, 'ru');
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

  /*
   * 删除缓存
   */

  private function delcache() {
    $redis = new phpredis();
    $keys = $redis->getKeys('market_area_list_*');
    $redis->delete($keys);
  }

  /*
   * 创建能力值
   */

  public function createAction() {
    $result = $this->_model->create_data($this->put_data);
    if ($result) {
      $this->delcache();
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  /*
   * 更新能力值
   */

  public function updateAction() {
    $result = $this->_model->update_data($this->put_data);
    if ($result) {
      $this->delcache();
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  /*
   * 删除能力
   */

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

}