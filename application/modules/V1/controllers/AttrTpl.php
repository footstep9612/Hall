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
     * 获取属性模板sku
     * @param string lang  语言（可选，默认浏览器语言）
     * @param string spu  spu编码（可选）
     * @param string sku  sku编码（可续）
     * @param string meterial_cat_no  物料分类（可选）
     */
    public function getAttrTplAction(){
        $attrTplModel = new GoodsAttrTplModel();
        $result = $attrTplModel ->getAttrTpl($this->input);
        if(!empty($result)){
            $data = array(
                'code' => 1,
                'message' => '获取模板成功',
                'data' => $result
            );
            jsonReturn($data);
        }
        jsonReturn('','-1009','获取模板失败');
    }
}