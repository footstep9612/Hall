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
        if ($this->getRequest()->getModuleName() == 'V1' &&
                $this->getRequest()->getControllerName() == 'User' &&
                in_array($this->getRequest()->getActionName(), ['login', 'register', 'es', 'kafka', 'excel'])) {
            
        } else {

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
                $list[$key]['id'] = $item['_id'];
            }
            $material_cat_nos = [];
            foreach ($data['aggregations']['meterial_cat_no']['buckets'] as $item) {
                $material_cats[$item['key']] = $item['doc_count'];
                $material_cat_nos[] = $item['key'];
            }

            $material_cat_nos = ksort($material_cat_nos);
            $catno_key = 'show_cats_' . md5(http_build_query($material_cat_nos) . '&lang=' . $this->getLang());
            $catlist = json_decode(redisGet($catno_key), true);
            if (!$catlist) {
                $matshowcatmodel = new ShowmaterialcatModel();

                $showcats = $matshowcatmodel->getshowcatsBymaterialcatno($material_cat_nos, $this->getLang());

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

                $catlist = $new_showcats1;
                redisSet($catno_key, json_encode($catlist), 86400);
            }
            $send['catlist'] = $new_showcats1;
            $send['list'] = $list;
            $this->setCode(MSG::MSG_SUCCESS);
            if ($this->put_data['keyword']) {
                $search = [];
                $search['keyword'] = $this->put_data['keyword'];
                $search['user_email'] = $this->user['email'];
                $search['search_time'] = date('Y-m-d H:i:s');
                $usersearchmodel = new UsersearchhisModel();
                if ($row = $usersearchmodel->exist($condition)) {
                    $search['search_count'] = intval($row['search_count']) + 1;
                    $usersearchmodel->update_data($search);
                }
            }


            $this->jsonReturn($send);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
