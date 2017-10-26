<?php

/**
 * sku
 * User: linkai
 * Date: 2017/6/15
 * Time: 18:48
 */
class ProductController extends PublicController {

    private $input;

    public function init() {
        $this->token = false;
        parent::init();
        $this->input = $this->getPut();
    }

    /**
     * spu 基本信息
     */
    public function getInfoAction() {
        if (isset($this->input['spu']) && !empty($this->input['spu'])) {
            $spu = $this->input['spu'];
        } else {
            jsonReturn('', '1000', '参数[spu]有误');
        }
        $user_token = $this->getPut('user_token');
        $lang = !empty($this->input['lang']) ? $this->input['lang'] : '';
        if ($lang != '' && !in_array($lang, array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', '1000', '参数[语言]有误');
        }
        $status = isset($this->input['status']) ? strtoupper($this->input['status']) : '';
        if ($status != '' && !in_array($status, array('NORMAL', 'CLOSED', 'VALID', 'TEST', 'CHECKING', 'INVALID', 'DELETED'))) {
            jsonReturn('', '1000', '参数[状态]有误');
        }
        if ($this->_getSysUser($user_token)) {
            $status = null;
        }
        $productModel = new ProductModel();
        $result = $productModel->getInfo($spu, $lang, $status);

        if (!empty($result)) {
            $goods_model = new GoodsModel();
            $attrdata = $goods_model->where('lang="' . $lang . '" and spu="' . $spu . '"and deleted_flag = "N" ')->find();
            if ($attrdata) {
                $result['goodsattr'] = $attrdata;
                $result['minimum_packing_unit'] = $attrdata['min_pack_unit'];
            }
            $data = array(
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '失败');
        }
        exit;
    }

    function _getSysUser($user_token) {
        $config = Yaf_Registry::get("config");
        $rconfig = $config->redis->config->toArray();
        $rconfig['dbname'] = 2;
        if (!empty($user_token)) {
            try {
                $redis2 = new phpredis($rconfig);
                $tokeninfo = JwtInfo($user_token); //解析token
                $userinfo = json_decode($redis2->get('user_info_' . $tokeninfo['id']), true);
                if (empty($userinfo)) {
                    return false;
                } else {
                    return true;
                }
            } catch (Exception $ex) {
                return false;
            }
        }
    }

    /**
     * 获取产品（spu）下的商品（sku）,包括规格 -- 门户产品详页在使用
     * @author link 2017-06-27
     */
    public function getSpecGoodsAction() {
        // $this->_token();
        if (!isset($this->input['spu']) || empty($this->input['spu'])) {
            jsonReturn('', '1000');
        }
        if (isset($this->input['lang']) && !in_array($this->input['lang'], array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', '1000');
        } elseif (!isset($this->input['lang'])) {
            $this->input['lang'] = browser_lang() ? browser_lang() : 'en';
        }
        $this->input['spec_type'] = isset($this->input['spec_type']) ? $this->input['spec_type'] : 0;
        $gmodel = new GoodsModel();
        $result = $gmodel->getSpecGoodsBySpu($this->input['spu'], $this->input['lang'], $this->input['spec_type'], $this->input['current_no']);
        $count = $gmodel->getSpecCountBySpu($this->input['spu'], $this->input['lang'], $this->input['spec_type']);

        if ($result) {
            $this->setvalue('count', $count);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setvalue('count', 0);
            $this->setCode('-1002');
            $this->setMessage('获取失败');
            $this->jsonReturn('');
        }
    }

    /**
     * 获取商品附件
     */
    public function bySpuAction() {
        $pAttach = new ProductAttachModel();
        $attachs = $pAttach->getAttach($this->input);

        if ($attachs || empty($attachs)) {
            jsonReturn(array('data' => $attachs));
        } else {
            jsonReturn('', -1, '失败!');
        }
    }

}
