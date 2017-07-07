<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

  public function init() {
    if ($this->getRequest()->setActionName($action) != 'list') {
      parent::init();
      $this->_model = new ShowCatModel();
    }
  }

  /**
   * 展示分类列表
   */
  public function listAction() {
    $condition = array();
    if ($this->put_data['lang']) {
      $condition['lang'] = $this->put_data['lang'];
    }
    if ($this->put_data['parent_cat_no']) {
      $condition['parent_cat_no'] = $this->put_data['parent_cat_no'];
    }

    $showcat = new ShowCatModel();
    $result = $showcat->getList($condition);
    if ($result) {
      jsonReturn($result);
    } else {
      jsonReturn(array('code' => '400', 'message' => '失败'));
    }
    exit;
  }

  public function listallAction() {

    $condition['level_no'] = 0;
    $arr = $this->_model->getlist($this->put_data, $this->getLang());

    if ($arr) {
      $this->setCode(MSG::MSG_SUCCESS);

      foreach ($arr as $key => $val) {

        $arr[$key]['child'] = $this->_model->getlist(['parent_cat_no' => $val['cat_no'], 'level' => 1], $this->getLang());
        if ($arr[$key]['child']) {
          foreach ($arr[$key]['child'] as $k => $item) {
            $arr[$key]['child'][$k]['child'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'], 'level' => 2]);
          }
        }
      }
    } else {
      $condition['level_no'] = 1;
      $arr = $this->_model->getlist($this->put_data, $this->getLang());
      if ($arr) {
        $this->setCode(MSG::MSG_SUCCESS);
        foreach ($arr[$key]['child'] as $k => $item) {

          $arr[$key]['child'][$k]['child'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'], 'level' => 2], $this->getLang());
        }
      } else {
        $condition['level_no'] = 2;
        $arr = $this->_model->getlist($this->put_data, $this->getLang());
        if ($arr) {
          $this->setCode(MSG::MSG_SUCCESS);
          $this->jsonReturn($arr);
        } else {
          $this->setCode(MSG::MSG_EMPTY);
          $this->jsonReturn();
        }
      }
    }
  }

  public function getlistAction() {

    $arr = $this->_model->get_list($this->put_data['cat_no'], $this->getLang());
    if ($arr) {
      $this->setCode(1);
      $this->jsonReturn($arr);
    } else {
      $this->setCode(MSG::MSG_EMPTY);
      $this->jsonReturn();
    }
  }

  public function infoAction() {
    $arr_en = $this->_model->info($this->put_data['cat_no'], 'en');
    $arr_zh = $this->_model->info($this->put_data['cat_no'], 'zh');
    $arr_es = $this->_model->info($this->put_data['cat_no'], 'es');
    $arr_ru = $this->_model->info($this->put_data['cat_no'], 'ru');
    if (empty($arr_en)) {
      $arr = $arr_en;
    } elseif (!empty($arr_zh)) {
      $arr = $arr_zh;
    } elseif (!empty($arr_es)) {
      $arr = $arr_es;
    } elseif (!empty($arr_ru)) {
      $arr = $arr_ru;
    }
    $arr['lang'] = $arr['name'] = null;
    unset($arr['lang'], $arr['name']);
    $arr['zh'] = isset($arr_zh['name']) ? $arr_zh['name'] : '';
    $arr['ru'] = isset($arr_ru['name']) ? $arr_ru['name'] : '';
    $arr['es'] = isset($arr_es['name']) ? $arr_es['name'] : '';
    $arr['en'] = isset($arr_en['name']) ? $arr_en['name'] : '';
    if ($arr) {
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn($arr);
    } else {
      $this->setCode(MSG::MSG_EMPTY);
      $this->jsonReturn();
    }
  }

  public function createAction() {

    $flag = $this->_model->create_data($this->put_data, $this->user['username']);
    if ($flag) {

      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  public function updateAction() {

    $flag = $this->_model->update_data($this->put_data, $this->user['username']);
    if ($flag) {

      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  public function changeorderAction() {
    $cat_no = $this->put_data['cat_no'];
    $chang_cat_no = $this->put_data['chang_cat_no'];
    $flag = $this->_model->changecat_sort_order($cat_no, $chang_cat_no);
    if ($flag) {

      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

  public function deleteAction() {

    $flag = $this->_model->delete_data($this->put_data['cat_no']);
    if ($flag) {
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_DELETE_FAILED);
      $this->jsonReturn();
    }
  }

  public function approvingAction() {

    $flag = $this->_model->approving($this->put_data['cat_no']);
    if ($flag) {
      $this->setCode(MSG::MSG_SUCCESS);
      $this->jsonReturn();
    } else {
      $this->setCode(MSG::MSG_DELETE_FAILED);
      $this->jsonReturn();
    }
  }

}
