<?php

/**
  附件文档Controller
 */
class LogiperiodController extends PublicController {

  public function init() {
    parent::init();

    $this->_model = new LogiPeriodModel();
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $name = $this->getPut('name', 'en');
    $current_no = $this->getPut('current_no', '1');
    $pagesize = $this->getPut('pagesize', '10');
    $key = 'logi_period_list_' . $lang . md5($name . $current_no . $pagesize);
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

  /**
   * 分类联动
   */
  public function infoAction() {
    $id = $this->getPut('id');
    $result = $this->_model->where(['id' => $id])->find();
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
    $data['logi_no'] = $data['warehouse'] . '_' . substr($data['trans_mode'], 0, 1) . '_' . $data['trade_terms']
            . '_' . $data['from_port'] . '_' . $data['to_port'];
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
    $where['id'] = $condition['id'];
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
