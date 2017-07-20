<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

  public function init() {
    $this->_model = new ShowCatModel();
    parent::init();
  }

  public function listAction() {
    $lang = $this->getPut('lang', 'en');
    $jsondata = ['lang' => $lang];
    $jsondata['level_no'] = 1;
    $condition = $jsondata;
    $key = 'Show_cat_list_' . $lang;
    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->getlist($jsondata);

      if ($arr) {
        $this->setCode(MSG::MSG_SUCCESS);
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
        redisSet($key, json_encode($arr), 86400);
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn($arr);
      } else {
        $condition['level_no'] = 2;
        $arr = $this->_model->getlist($condition);
        if ($arr) {

          foreach ($arr[$key]['childs'] as $k => $item) {
            $arr[$key]['childs'][$k]['childs'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'], 'level_no' => 3, 'lang' => $lang]);
          }
          redisSet($key, json_encode($arr), 86400);
          $this->setCode(MSG::MSG_SUCCESS);
          $this->jsonReturn($arr);
        } else {
          $condition['level_no'] = 3;
          $arr = $this->_model->getlist($condition);
          if ($arr) {
            redisSet($key, json_encode($arr), 86400);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
          } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
          }
        }
      }
    }
    $this->jsonReturn($data);
  }

  public function getlistAction() {

    $lang = $this->getPut('lang', '');
    $cat_no = $this->getPut('cat_no', '');
    if (!$cat_no) {
      $cat_no = $this->get('cat_no', '');
    }
    if (!$lang) {
      $lang = $this->get('lang', 'en');
    }

    $key = 'Show_cat_getlist_' . $lang . '_' . $cat_no;

    $data = json_decode(redisGet($key), true);
    if (!$data) {
      $arr = $this->_model->get_list($cat_no, $lang);
      if ($arr) {
        redisSet($key, json_encode($arr), 86400);
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
    unset($result['lang']);
    if ($result) {

      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  private function delcache() {
    $redis = new phpredis();
    $keys = $redis->getKeys('show_cat_getlist_*');
    $redis->delete($keys);
    $listkeys = $redis->getKeys('Show_cat_list_*');
    $redis->delete($listkeys);
  }

  public function createAction() {

    $result = $this->_model->create_data($this->put_data, $this->user['username']); 
    if ($result) {
      $this->delcache();
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn($result);
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
      $this->jsonReturn($result);
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
      $this->jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  public function approvingAction() {

    $result = $this->_model->approving($this->put_data['id']);
    if ($result) {
      $this->delcache();
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  /* 交换顺序
   * 
   */

  public function changeorderAction() {

    $result = $this->_model->changecat_sort_order($this->put_data['cat_no'], $this->put_data['chang_cat_no']);
    if ($result) {
      $this->delcache();
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

}
