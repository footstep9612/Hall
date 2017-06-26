<?php
/**
 * 属性.
 * User: linkai
 * Date: 2017/6/26
 * Time: 13:14
 */
class AttrController extends PublicController{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 商品属性  --公共的
     * 包括产品属性的
     */
    public function bySkuAction(){
        $this->input['sku'] = 'sku003';
        if (!isset($this->input['sku'])) {
            jsonReturn('', 1000);
        }

        $goods = new GoodsAttrModel();
        $attrs = $goods->getAttr($this->input);
        if($attrs){
            jsonReturn(array('data'=>$attrs));
        }else{
            json_encode('',400,'');
        }
    }

    /**
     * 产品属性
     */
    public function bySpuAction(){

    }
}