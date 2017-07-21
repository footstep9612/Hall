<?php

/**
  附件文档Controller
 */
class TradetermsController extends PublicController {

  public function init() {
    //parent::init();

    $this->_model = new TradetermsModel();
  }

  public function listAction() {
    $condtion = $this->put_data;
    unset($condtion['token']);
    $key = 'Tradeterms_list_' .  md5(json_encode($condtion));
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->getListbycondition($condtion);
      if ($arr) {
        $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
        $data['code'] = MSG::MSG_SUCCESS;
        $data['data'] = $arr;
        $data['count'] = $this->_model->getCount($condtion);
        redisSet($key, json_encode($data), 86400);
        $this->jsonReturn($data);
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
    $id = $this->getPut('id');
    if ($id) {
      $result = $this->_model->where(['id' => $id])->find();
    } else {
      $this->setCode(MSG::MSG_FAILED);

      $this->jsonReturn();
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
    $keys = $redis->getKeys('Tradeterms_*');
    $redis->delete($keys);

  }

  public function createAction() {
    $condition = $this->put_data;
    $data = $this->_model->create($condition);
    $result = $this->_model->add($data);
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

    $condition = $this->put_data;
    $data = $this->_model->create($condition);
    $where['id'] = $condition['id'];
    $result = $this->_model->where($where)->update($data);
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

    $condition = $this->put_data;
    if (isset($condition['id']) && $condition['id']) {
      if (is_string($condition['id'])) {
        $where['id'] = $condition['id'];
      } elseif (is_array($condition['id'])) {
        $where['id'] = ['in', $condition['id']];
      }
    } elseif ($condition['terms']) {
      $where['terms'] = $condition['terms'];
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }

    $result = $this->_model->where($where)->delete();
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
