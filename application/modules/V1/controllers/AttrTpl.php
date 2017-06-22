<?php
/**
 * 属性模板
 * User: linkai
 * Date: 2017/6/22
 * Time: 15:16
 */
class AttrTplController extends PublicController{
    private $input;

    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 获取属性模板
     * @param string lang  语言（可选，默认浏览器语言）
     * @param string spu  spu编码（可选）
     * @param string sku  sku编码（可续）
     * @param string meterial_cat_no  物料分类（可选）
     */
    public function getAttrTplAction(){
        $atplModel = new GoodsAttrTplModel();
        $attrs = $atplModel ->getAttrTpl($this->input);
        jsonReturn($attrs);
    }
}