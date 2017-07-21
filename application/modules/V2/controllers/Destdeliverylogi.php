<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Destdeliverylogi
 *
 * @author zhongyg
 */
class DestdeliverylogiController extends PublicController {

  public function init() {
    //parent::init();

    $this->_model = new DestdeliverylogiModel();
  }

  public function listAction() {
    $condtion = $this->put_data;
    unset($data['token']);
    $key = 'logi_period_list_' . $lang . md5(json_encode($condtion));
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
    $keys = $redis->getKeys('logi_period_list_*');
    $redis->delete($keys);
    $LogiPeriods = $redis->getKeys('LogiPeriod*');
    $redis->delete($LogiPeriods);
  }

  public function createAction() {
    $condition = $this->put_data;
    $data = $this->_model->create($condition);
    $data['logi_no'] = $data['from_loc'] . '_'
            . substr($data['trans_mode'], 0, 1)
            . '_' . $data['to_loc'];
    $data['created_by'] = $this->user['name'];
    $data['created_at'] = date('Y-m-d H:i:s');
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
    $where['logi_no'] = $condition['logi_no'];
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
