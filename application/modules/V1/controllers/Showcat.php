<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

  private $input;

  public function init() {

    parent::init();
  }

  /**
   * 分类联动
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
    $result = $showcat->get_list($condition, $this->getLang());

    if ($result) {
      $this->setCode(1);
      jsonReturn($result);
    } else {
      jsonReturn(array('code' => '400', 'message' => '失败'));
    }
    exit;
  }

  /**
   * 分类联动
   */
  public function infoAction() {
    $condition = array();
    $showcat = new ShowCatModel();
    $ret_en = $showcat->info($this->put_data['cat_no'], 'en');
    $ret_zh = $showcat->info($this->put_data['cat_no'], 'zh');
    $ret_es = $showcat->info($this->put_data['cat_no'], 'es');
    $ret_ru = $showcat->info($this->put_data['cat_no'], 'ru');
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
      jsonReturn($result);
    } else {
      jsonReturn(array('code' => '400', 'message' => '失败'));
    }
    exit;
  }

  /**
   * 分类列表
   */
  public function listallAction() {
    $showcat = new ShowCatModel();
    $result = $showcat->getlist($this->put_data, $this->getLang());
    if ($result) {
      $this->setCode(1);
      jsonReturn($result);
    } else {
      jsonReturn(array('code' => '400', 'message' => '失败'));
    }
    exit;
  }

  /**
   * 更新分类
   */
  public function updateAction() {

    $showcat = new ShowCatModel();
    $result = $showcat->update_data($this->put_data, $this->user['name']);
    if ($result) {
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
    exit;
  }

  /**
   * 新增分类
   */
  public function createAction() {

    $showcat = new ShowCatModel();
    $result = $showcat->create_data($this->put_data, $this->user['name']);
    if ($result) {
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
    exit;
  }

  /**
   * 删除分类
   */
  public function deleteAction() {

    $showcat = new ShowCatModel();
    $result = $showcat->delete_data($this->put_data['cat_no'], $this->getLang());
    if ($result) {
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
    exit;
  }

  /**
   * 交换排序
   */
  public function changeorderAction() {

    $showcat = new ShowCatModel();
    $result = $showcat->changecat_sort_order($this->put_data['cat_no'], $this->put_data['chang_cat_no']);
    if ($result) {
      $this->setCode(1);
      jsonReturn($result);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      jsonReturn();
    }
    exit;
  }

}
