<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 *
 * @author zhongyg
 */
class EsproductController extends ShopMallController {

  protected $index = 'erui_goods';
  protected $es = '';
  protected $langs = ['en', 'es', 'ru', 'zh'];
  protected $version = '1';

  //put your code here
  public function init() {

    ini_set("display_errors", "On");
    error_reporting(E_ERROR | E_STRICT);
    $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
    $lang = $this->getPut('lang', 'en');
    $this->setLang($lang);
    if (!empty($jsondata["token"])) {
      $token = $jsondata["token"];
    }
    $model = new UserModel();
    if (!empty($token)) {
      try {
        $tks = explode('.', $token);
        $tokeninfo = JwtInfo($token); //解析token
        $userinfo = json_decode(redisGet('shopmall_user_info_' . $tokeninfo['id']), true);

        if (empty($userinfo)) {
          $this->put_data['source'] = 'ERUI';
        } else {
          $this->user = array(
              "id" => $userinfo["id"],
              "name" => $tokeninfo["name"],
              'email' => $tokeninfo["email"],
              "token" => $token, //token
          );
        }
      } catch (Exception $e) {
        $this->put_data['source'] = 'ERUI';
      }
    } else {
      $this->put_data['source'] = 'ERUI';
    }
    $this->es = new ESClient();
  }

  public function listAction() {
    $model = new EsproductModel();
    $ret = $model->getproducts($this->put_data, null, $this->getLang());

    if ($ret) {
      $list = [];

      $data = $ret[0];
      $send['count'] = intval($data['hits']['total']);
      $send['current_no'] = intval($ret[1]);
      $send['pagesize'] = intval($ret[2]);
      if (isset($ret[3]) && $ret[3] > 0) {

        $send['allcount'] = $ret[3] > $send['count'] ? $ret[3] : $send['count'];
      } else {
        $send['allcount'] = $send['count'];
      }
      foreach ($data['hits']['hits'] as $key => $item) {
        $list[$key] = $item["_source"];
        $attachs = json_decode($item["_source"]['attachs'], true);
        if ($attachs && isset($attachs['BIG_IMAGE'][0])) {
          $list[$key]['img'] = $attachs['BIG_IMAGE'][0];
        } else {
          $list[$key]['img'] = null;
        }
        $list[$key]['id'] = $item['_id'];
        $show_cats = json_decode($item["_source"]["show_cats"], true);
        if ($show_cats) {
          rsort($show_cats);
        }
        $list[$key]['show_cats'] = $show_cats;
        $list[$key]['attrs'] = json_decode($list[$key]['attrs'], true);
        $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
        $list[$key]['specs'] = json_decode($list[$key]['specs'], true);
        $list[$key]['attachs'] = json_decode($list[$key]['attachs'], true);
        $list[$key]['meterial_cat'] = json_decode($list[$key]['meterial_cat'], true);
        $list[$key]['skus'] = json_decode($list[$key]['skus'], true);
      }
      $material_cat_nos = [];
      foreach ($data['aggregations']['meterial_cat_no']['buckets'] as $item) {
        $material_cats[$item['key']] = $item['doc_count'];
        $material_cat_nos[] = $item['key'];
      }

      ksort($material_cat_nos);
      $catno_key = 'show_cats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang());
      $show_cat_nos = [];
      if ($this->put_data['show_cat_no']) {
        $catno_key .= md5($this->put_data['show_cat_no']);
      }
      $catlist = json_decode(redisGet($catno_key), true);
      if (!$catlist) {
        if ($this->put_data['show_cat_no']) {
          $show_cat_model = new ShowCatModel();
          $info = $show_cat_model->getinfo($this->put_data['show_cat_no'], $this->getLang());
          if ($info['level_no'] == 1) {
            $condition['level_no'] = 3;
            $condition['top_no'] = $this->put_data['show_cat_no'];
            $condition['lang'] = $this->getLang();
            $cat_nos = $show_cat_model->getList($condition, 'cat_no');
          } elseif ($info['level_no'] == 2) {
            $condition['level_no'] = 3;
            $condition['parent_cat_no'] = $this->put_data['show_cat_no'];
            $condition['lang'] = $this->getLang();
            $cat_nos = $show_cat_model->getList($condition, 'cat_no');
          } elseif ($info['level_no'] == 3) {
            $cat_nos = ['cat_no' => $this->put_data['show_cat_no']];
          }
          if ($cat_nos) {
            foreach ($cat_nos as $showcat) {
              $show_cat_nos = $showcat['cat_no'];
            }
          }
        }
        $matshowcatmodel = new ShowmaterialcatModel();
        $showcats = $matshowcatmodel->getshowcatsBymaterialcatno($material_cat_nos, $this->getLang(),$show_cat_nos);
        $new_showcats1 = $new_showcats2 = $new_showcats3 = [];
        $new_showcat2_nos = [];
        $new_showcat1_nos = [];
        foreach ($showcats as $showcat) {
          $material_cat_no = $showcat['material_cat_no'];
          unset($showcat['material_cat_no']);
          $new_showcats3[$showcat['parent_cat_no']][$showcat['cat_no']] = $showcat;
          if (isset($material_cats[$material_cat_no])) {
            $new_showcats3[$showcat['parent_cat_no']][$showcat['cat_no']]['count'] = $material_cats[$material_cat_no];
          } else {
            $new_showcats3[$showcat['parent_cat_no']][$showcat['cat_no']]['count'] = 0;
          }
          $new_showcat2_nos[] = $showcat['parent_cat_no'];
        }

        $showcat2s = $matshowcatmodel->getshowcatsBycatno($new_showcat2_nos, $this->getLang());
        foreach ($showcat2s as $showcat2) {

          $new_showcats2[$showcat2['parent_cat_no']][$showcat2['cat_no']] = $showcat2;
          if (isset($new_showcats3[$showcat2['cat_no']])) {
            foreach ($new_showcats3[$showcat2['cat_no']] as $showcat3) {

              $new_showcats2[$showcat2['parent_cat_no']][$showcat2['cat_no']]['count'] += $showcat3['count'];
            }
            $new_showcats2[$showcat2['parent_cat_no']][$showcat2['cat_no']]['childs'] = $new_showcats3[$showcat2['cat_no']];
          }

          $new_showcat1_nos[] = $showcat2['parent_cat_no'];
        }

        $showcat1s = $matshowcatmodel->getshowcatsBycatno($new_showcat1_nos, $this->getLang());
        foreach ($showcat1s as $showcat1) {

          $new_showcats1[$showcat1['cat_no']] = $showcat1;
          if (isset($new_showcats2[$showcat1['cat_no']])) {
            foreach ($new_showcats2[$showcat1['cat_no']] as $showcat2) {

              $new_showcats1[$showcat1['cat_no']]['count'] += $showcat2['count'];
            }
            $new_showcats1[$showcat1['cat_no']]['childs'] = $new_showcats2[$showcat1['cat_no']];
          }
        }
        $new_showcats = [];
        if ($new_showcats1) {

          foreach ($new_showcats1 as $cat1) {

            $newcat1 = $cat1;
            unset($newcat1['childs']);
            foreach ($cat1['childs'] as $cat2) {
              $newcat2 = $cat2;
              unset($newcat2['childs']);
              foreach ($cat2['childs'] as $cat3) {
                $newcat2['childs'][] = $cat3;
              }
              $newcat1['childs'][] = $newcat2;
            }
            $new_showcats[] = $newcat1;
          }
        }
        $catlist = $new_showcats;
        redisSet($catno_key, json_encode($catlist), 86400);
      }
      $send['catlist'] = $catlist;
      $send['data'] = $list;
      $this->setCode(MSG::MSG_SUCCESS);
      if ($this->put_data['keyword']) {
        $search = [];
        $search['keywords'] = $this->put_data['keyword'];
        $search['user_email'] = $this->user['email'];
        $search['search_time'] = date('Y-m-d H:i:s');
        $usersearchmodel = new BuyersearchhisModel();
        $condition = ['user_email' => $search['user_email'], 'keywords' => $search['keywords']];
        $row = $usersearchmodel->exist($condition);
        if ($row) {
          $search['search_count'] = intval($row['search_count']) + 1;
          $usersearchmodel->update_data($search);
        }
      }

      $send['code'] = $this->getCode();
      $send['message'] = $this->getMessage();
      $this->jsonReturn($send);
    } else {
      $this->setCode(MSG::MSG_FAILED);
      $this->jsonReturn();
    }
  }

}
