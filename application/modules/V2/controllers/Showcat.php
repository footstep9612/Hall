<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController {

  public function init() {
     parent::init();
    $this->_model = new ShowCatModel();
  }

  public function treeAction() {
    $lang = $this->getPut('lang', '');
    if (!$lang) {
      $lang = $this->getPut('lang', 'zh');
    }
    $jsondata = ['lang' => $lang];
    $jsondata['level_no'] = 1;
    $condition = $jsondata;
    $redis_key = 'show_cat_tree_' . $lang;
    $data = json_decode(redisGet($redis_key), true);
    if (!$data) {
      $arr = $this->_model->tree($jsondata);

      if ($arr) {
        $this->setCode(MSG::MSG_SUCCESS);
        foreach ($arr as $key => $val) {
          $arr[$key]['children'] = $this->_model->tree(['parent_cat_no' => $val['value'], 'level_no' => 2, 'lang' => $lang]);

          if ($arr[$key]['children']) {
            foreach ($arr[$key]['children'] as $k => $item) {
              $arr[$key]['children'][$k]['children'] = $this->_model->tree(['parent_cat_no' => $item['value'],
                  'level_no' => 3,
                  'lang' => $lang]);
            }
          }
        }
        redisSet($redis_key, json_encode($arr), 86400);
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn($arr);
      } else {
        $condition['level_no'] = 2;
        $arr = $this->_model->getlist($condition);
        if ($arr) {
          $this->setCode(MSG::MSG_SUCCESS);
          foreach ($arr[$key]['children'] as $k => $item) {
            $arr[$key]['children'][$k]['children'] = $this->_model->tree([
                'parent_cat_no' => $item['value'],
                'level_no' => 3,
                'lang' => $lang]);
          }

          redisSet($redis_key, json_encode($arr), 86400);
          $this->setCode(MSG::MSG_SUCCESS);
          $this->jsonReturn($arr);
        } else {
          $condition['level_no'] = 3;
          $arr = $this->_model->tree($condition);
          if ($arr) {
            redisSet($redis_key, json_encode($arr), 86400);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
          } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
          }
        }
      }
    }
    $this->setCode(MSG::MSG_SUCCESS);
    $this->jsonReturn($data);
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

    $lang = $this->getPut('lang', 'en');
    $cat_no = $this->getPut('cat_no', '');
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
   * 分类详情
   */
  public function infoAction() {
    $cat_no =  $this->getPut('cat_no');
    if (!$cat_no) {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
    $data = [];
    $langs = ['en', 'zh', 'es', 'ru'];
    foreach ($langs as $lang) {
      $result = $this->_model->info($cat_no, $lang);
      if ($result) {
        $data = $result;
        $data['name'] = $data['id'] = null;
        unset($data['name'], $data['id']);
        $data[$lang]['name'] = $result['name'];
      }
    }

    if ($data) {
      list($top_cats, $parent_cats) = $this->getparentcats($data);
      $this->setCode(MSG::MSG_SUCCESS);
      $this->setvalue('top_cats', $top_cats);
      $this->setvalue('parent_cats', $parent_cats);
      if ($data['level_no'] == 3) {
        $material_cat_nos = $this->_model->Table('erui_goods.t_show_material_cat')
                ->where(['show_cat_no' => $data['cat_no']])
                ->field('material_cat_no')
                ->select();
        $es_product_model = new EsproductModel();
        $material_cat_nos= array_values($material_cat_nos);
        $material_cats = $es_product_model->getmaterial_cats($material_cat_nos, 'zh');
      } else {
        $material_cats = null;
      }
      $this->setvalue('material_cats', $material_cats);
      $this->jsonReturn($data);
    } else {
      $this->setCode(MSG::ERROR_EMPTY);

      $this->jsonReturn();
    }
    exit;
  }

  private function getparentcats($data) {
    $parent_cats = $top_cats = null;
    if ($data['level_no'] == 3) {
      $result = $this->_model->info($data['parent_cat_no'], 'zh');
      $parent_cats = $this->_model->get_list($result['parent_cat_no'], 'zh');
      $top_cats = $this->_model->get_list(0, 'zh');
      foreach ($parent_cats as $key => $item) {
        if ($item['cat_no'] == $result['cat_no']) {
          $item['checked'] = true;
        } else {
          $item['checked'] = false;
        }
        $parent_cats[$key] = $item;
      }
      foreach ($top_cats as $key => $item) {
        if ($item['cat_no'] == $result['parent_cat_no']) {
          $item['checked'] = true;
        } else {
          $item['checked'] = false;
        }
        $top_cats[$key] = $item;
      }
    } elseif ($data['level_no'] == 2) {
      $top_cats = $this->_model->get_list($data['parent_cat_no'], 'zh');
      foreach ($top_cats as $key => $item) {
        if ($item['cat_no'] == $data['parent_cat_no']) {
          $item['checked'] = true;
        } else {
          $item['checked'] = false;
        }
        $top_cats[$key] = $item;
      }
    }
    return [$top_cats, $parent_cats];
  }

  private function delcache() {
    $redis = new phpredis();
    $keys = $redis->getKeys('show_cat_getlist_*');
    $redis->delete($keys);
    $listkeys = $redis->getKeys('Show_cat_list_*');
    $redis->delete($listkeys);
    $treekeys = $redis->getKeys('show_cat_tree_*');
    $redis->delete($treekeys);
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
