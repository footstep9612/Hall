<?php

/**
  附件文档Controller
 */
class MaterialcatController extends PublicController {

  public function init() {
    parent::init();

    $this->_model = new MaterialcatModel();
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $jsondata = ['lang' => $lang];
    $jsondata['level_no'] = 1;
    $condition = $jsondata;
    $key = 'Material_cat_list_' . $lang;
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->getlist($jsondata);
      if ($arr) {
        $data['code'] = 0;
        $data['message'] = '获取成功!';
        foreach ($arr as $key => $val) {
          $arr[$key]['childs'] = $this->_model->getlist(['parent_cat_no' => $val['cat_no'], 'level_no' => 2, 'lang' => $lang]);

          if ($arr[$key]['childs']) {
            foreach ($arr[$key]['childs'] as $k => $item) {
              $arr[$key]['childs'][$k]['childs'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'],
                  'level_no' => 3,
                  'lang' => $lang]);
            }
          }
        }
        $data['data'] = $arr;
      } else {
        $condition['level_no'] = 2;
        $arr = $this->_model->getlist($condition);
        if ($arr) {
          $data['code'] = 0;
          $data['message'] = '获取成功!';
          foreach ($arr[$key]['childs'] as $k => $item) {
            $arr[$key]['childs'][$k]['childs'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'], 'level_no' => 3, 'lang' => $lang]);
          }
          $data['data'] = $arr;
        } else {
          $condition['level_no'] = 3;
          $arr = $this->_model->getlist($condition);
          if ($arr) {
            $data['code'] = 0;
            $data['message'] = '获取成功!';
            $data['data'] = $arr;
          } else {
            $data['code'] = -1;
            $data['message'] = '数据为空!';
          }
        }
      }
      redisSet($key, json_encode($data), 86400);
    }
    $this->jsonReturn($data);
  }

  public function getlistAction() {
    $lang = $this->getPut('lang', 'en');
    $cat_no = $this->getPut('cat_no', '');
    $key = 'Material_cat_getlist_' . $lang;
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->get_list($cat_no, $lang);
      if ($arr) {
        $data['code'] = 0;
        $data['message'] = '获取成功!';

        $data['data'] = $arr;
      } else {
        $data['code'] = -1;
        $data['message'] = '数据为空!';
      }
      redisSet($key, json_encode($data), 86400);
    }
    $this->jsonReturn($data);
  }

  /**
   * 分类联动
   */
  public function infoAction() {
    $ret_en = $this->_model->info($this->put_data['cat_no'], 'en');
    $ret_zh = $this->_model->info($this->put_data['cat_no'], 'zh');
    $ret_es = $this->_model->info($this->put_data['cat_no'], 'es');
    $ret_ru = $this->_model->info($this->put_data['cat_no'], 'ru');
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
      $this->setCode(1);
      $this->jsonReturn($result);
    } else {
      $this->setCode(-1);

      $this->jsonReturn();
    }
    exit;
  }

  private function delcache() {
    redisDel('Material_cat_getlist_en');
    redisDel('Material_cat_getlist_zh');
    redisDel('Material_cat_getlist_es');
    redisDel('Material_cat_getlist_ru');
    redisDel('Material_cat_list_en');
    redisDel('Material_cat_list_zh');
    redisDel('Material_cat_list_es');
    redisDel('Material_cat_list_ru');
  }

  public function createAction() {

    $result = $this->_model->create_data($this->put_data, $this->user['username']);
    if ($result) {

      $this->delcache();
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
  }

  public function updateAction() {

    $result = $this->_model->update_data($this->put_data, $this->user['username']);
    if ($result) {
      $this->delcache();
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
  }

  public function deleteAction() {

    $result = $this->_model->delete_data($this->put_data['id']);
    if ($result) {
      $this->delcache();
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
  }

  public function approvingAction() {

    $result = $this->_model->approving($this->put_data['id']);
    if ($result) {
      $this->delcache();
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
  }

  /* 交换顺序
   * 
   */

  public function changeorderAction() {

    $result = $this->_model->changecat_sort_order($this->put_data['cat_no'], $this->put_data['chang_cat_no']);
    $this->setCode(1);
    if ($result) {
      $this->delcache();
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
  }

}
