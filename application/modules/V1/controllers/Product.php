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
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * spu列表
     */
    public function getListAction() {
        $productModel = new ProductModel();
        $result = $productModel->getList($this->input);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', 400, '失败');
        }
        exit;
    }

    /**
     * spu 详情
     */
    public function getInfoAction() {
        $spu = isset($this->input['spu']) ? $this->input['spu'] : '';
        $lang = isset($this->input['lang']) ? $this->input['lang'] : '';
        $productModel = new ProductModel();
        $result = $productModel->getInfo($spu, $lang);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', 400, '失败');
        }
        exit;
    }

    public function editAction() {
        $productModel = new ProductModel();
        $result = $productModel->editInfo($this->input);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
        exit;
    }

    /**
     * 展示分类 - SKU列表
     */
    public function listAction() {
        if (!isset($this->input['show_cat_no'])) {
            jsonReturn('',"-1002","SKU编码不能为空");
        }
        $lang = isset($this->input['lang']) ? strtolower($this->input['lang']) : (browser_lang() ? browser_lang() : 'en');
        $page = isset($this->input['current_no']) ? $this->input['current_no'] : 1;
        $pagesize = isset($this->input['pagesize']) ? $this->input['pagesize'] : 10;

        $product = new ShowCatProductModel();
        $return = $product->getSkuByCat($this->input['show_cat_no'], $lang, $page, $pagesize);
        if ($return) {
            $return['code'] = 1;
            $return['message'] = '成功';
            jsonReturn($return);
        } else {
            jsonReturn('','-1005', '获取失败');
        }
        exit;
    }

    /**
     * SKU详情
     */
    public function infoAction() {
        if (!isset($this->input['sku'])) {
            jsonReturn('',"-1002","SKU编码不能为空");
        }
        $lang = isset($this->input['lang']) ? strtolower($this->input['lang']) : (browser_lang() ? browser_lang() : 'en');

            $goodsModel = new GoodsModel();
            $result = $goodsModel->getInfo($this->input['sku'], $lang);
            if ($result) {
                $data = array(
                    'code' => 0,
                    'message' => '成功',
                    'data' => $result
                );
                jsonReturn($data);
            } else {
                jsonReturn(array('code' => 400, 'message' => '失败'));
            }
            exit;
    }

    /**
     * SPU属性详情p
     */
    public function getAttrInfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['spu'])) {
            $spu = $data['spu'];
        } else{
            jsonReturn('',"-1002","spu不可以为空");
        }
        //获取产品属性
        $goods = new ProductAttrModel();
        $result = $goods->getAttrBySpu($spu, $this->lang);


        if ($result) {
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('','-1005', '获取失败');
        }
        exit;
    }

    /**
     * SPU属性详情a
     */
    public function attrInfoAction() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['spu'])) {
            $spu = $data['spu'];
        } else{
            jsonReturn('',"-1002","spu不可以为空");
        }
        $lang = !empty($data['lang']) ? $data['lang'] : '';
        //获取产品属性
        $goods = new ProductAttrModel();
        $result = $goods->getAttrBySpu($spu, $lang);

        if ($result) {
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('','-1005', '获取失败');
        }
        exit;
    }

}
