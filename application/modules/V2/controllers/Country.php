<?php

/**
  附件文档Controller
 */
class CountryController extends PublicController {

  public function init() {
    //  parent::init();

    $this->_model = new CountryModel();
  }

  /*
   * 营销区域列表
   */

  public function listAction() {
    $data = $this->put_data;
    unset($data['token']);
    if (isset($data['current_no']) && $data['current_no']) {
      $data['current_no'] = intval($data['current_no']) > 0 ? intval($data['current_no']) : 1;
    }
    if (isset($data['pagesize']) && $data['pagesize']) {
      $data['pagesize'] = intval($data['pagesize']) > 0 ? intval($data['pagesize']) : 2;
    }
    $market_area = new CountryModel();
    if (redisGet('Country_list_' . md5(json_encode($data)))) {
      $arr = json_decode(redisGet('Country_list_' . md5(json_encode($data))), true);
    } else {
      $arr = $market_area->getlistBycodition($data); //($this->put_data);
      if ($arr) {
        redisSet('Country_list_' . md5(json_encode($data)), json_encode($arr));
      }
    }

    if (!empty($arr)) {
      $data['code'] = MSG::MSG_SUCCESS;
      $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS);
      $data['data'] = $arr;
    } else {
      $data['code'] = MSG::MSG_FAILED;
      $data['message'] = MSG::getMessage(MSG::MSG_FAILED);
    }
    $data['count'] = $market_area->getCount($data);

    $this->jsonReturn($data);
  }

  /*
   * 营销区域列表
   */

  public function listallAction() {
    $data = $this->put_data;
    unset($data['token']);
    $market_area = new CountryModel();
    if (redisGet('Country_listall_' . md5(json_encode($data)))) {
      $arr = json_decode(redisGet('Market_Area_listall_' . md5(json_encode($data))), true);
    } else {
      $arr = $market_area->getlistBycodition($data); //($this->put_data);
      if ($arr) {
        redisSet('Country_listall_' . md5(json_encode($data)), json_encode($arr));
      }
    }
    if (!empty($arr)) {

      $this->setCode(MSG::MSG_SUCCESS);
    } else {
      $this->setCode(MSG::MSG_FAILED);
    }
    $this->jsonReturn($arr);
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
    $bn = 'Middle Asia';
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
      $result['en']['id'] = $ret_en['id'];
    }
    if ($ret_zh) {
      $result['zh']['name'] = $ret_zh['name'];
      $result['zh']['id'] = $ret_zh['id'];
    }
    if ($ret_ru) {
      $result['ru']['name'] = $ret_ru['name'];
      $result['ru']['id'] = $ret_ru['id'];
    }
    if ($ret_es) {
      $result['es']['name'] = $ret_es['name'];
      $result['es']['id'] = $ret_es['id'];
    }
    unset($result['id']);
    unset($result['lang']);
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
    $keys = $redis->getKeys('Country_list_*');
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
