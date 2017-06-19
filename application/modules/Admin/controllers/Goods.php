<?php
/**
 * SKU
 * User: linkai
 * Date: 2017/6/17
 * Time: 18:30
 */
class GoodsController extends PublicController{
    /**
     * spu列表
     */
    public function listAction()
    {
        $condition = array();
        //语言
        $lang = $this->getRequest()->getQuery("lang", '');
        if ($lang != '') {
            $condition['lang'] = strtolower($lang);
        }

        //spu编码
        $spu = $this->getRequest()->getQuery("spu",'');
        if ($spu != '') {
            $condition['spu'] = $spu;
        }

        //sku name
        $sku = $this->getRequest()->getQuery("name", '');
        if ($sku != '') {
            $condition['name'] = $sku;
        }

        //sku
        $id = $this->getRequest()->getQuery("sku", '');
        if ($id != '') {
            $condition['id'] = $id;
        }

        //来源
        $source = $this->getRequest()->getQuery('source','');
        if ($source != '') {
            $condition['source'] = $source;
        }

        //定价
        $pricing_flag = $this->getRequest()->getQuery('pricing_flag','');
        if($pricing_flag !=''){
            $condition['pricing_flag'] = $pricing_flag;
        }

        //规格型号
        $model = $this->getRequest()->getQuery('model','');
        if($model !=''){
            $condition['model'] = $model;
        }

        //分页
        $current_num = $this->getRequest()->getQuery('current_num',1);
        $pagesize = $this->getRequest()->getQuery('pagesize',10);

        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($condition,$current_num,$pagesize);
        if($result){
            jsonReturn($result);
        }else{
            jsonReturn('',400,'失败');
        }
        exit;
    }
}