<?php
/**
 * SPU
 * User: linkai
 * Date: 2017/6/16
 * Time: 18:18
 */
class ProductController extends PublicController{
    /**
     * spu列表
     */
    public function listAction(){
        $condition = array();
        //语言
        $lang = $this->getRequest()->getQuery("lang",'');
        if($lang!='') {
            $condition['lang'] = strtolower($lang);
        }

        //来源
        $source = $this->getRequest()->getQuery('source','');
        if($source!=''){
            $condition['source'] = $lang;
        }

        //品牌
        $brand = $this->getRequest()->getQuery('brand','');
        if($brand!=''){
            $condition['brand'] = $brand;
        }

        // 名称/编码/创建人的关键词
        $keyword = $this->getRequest()->getQuery('keyword','');
        if($keyword!=''){
            $condition['keyword'] = $keyword;
        }

        //分类
        $meterial_cat_no = $this->getRequest()->getQuery('meterial_cat_no','');
        if($meterial_cat_no!=''){
            $condition['meterial_cat_no'] = $meterial_cat_no;
        }

        //创建时间
        $start_time = $this->getRequest()->getQuery('start_time','');
        if($start_time!=''){
            $condition['start_time'] = $start_time;
        }
        $end_time = $this->getRequest()->getQuery('end_time','');
        if($end_time!=''){
            $condition['end_time'] = $end_time;
        }

        $current_num = $this->getRequest()->getQuery('current_num',1);
            $pagesize = $this->getRequest()->getQuery('pagesize',10);

        $productModel = new ProductModel();
        $result = $productModel->getList($condition,$current_num,$pagesize);
        if($result){
            $this->jsonReturn($result);
        }else{
            $this->jsonReturn('',400,'失败');
        }
        exit;
    }

    /**
     * spu 详情
     */
    public function infoAction(){
        $spu = $this->getRequest()->getQuery('spu','');
        $lang = $this->getRequest()->getQuery('lang','');
        $productModel = new ProductModel();
        $result = $productModel->getInfo($spu,$lang);
        if($result){
            $this->jsonReturn($result);
        }else{
            $this->jsonReturn('',400,'失败');
        }
        exit;
    }
}