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
     * 搬迁至v1下goods
     */
    public function listAction()
    {
        $condition = array();
        //语言
        $lang = $this->getRequest()->getQuery("lang", '');
        if ($lang != '') {
            $condition['lang'] = strtolower($lang);
        }

        //sku编码
        $sku = $this->getRequest()->getQuery("sku", '');
        if ($sku != '') {
            $condition['sku'] = $sku;
        }

        //skuid
        $id = $this->getRequest()->getQuery("skuid", '');
        if ($id != '') {
            $condition['id'] = $id;
        }

        //来源
        $source = $this->getRequest()->getQuery('source','');
        if ($source != '') {
            $condition['source'] = $source;
        }

        //分页
        $current_num = $this->getRequest()->getQuery('current_num',1);
        $pagesize = $this->getRequest()->getQuery('pagesize',10);

        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($condition,$current_num,$pagesize);
        if($result){
            $this->jsonReturn($result);
        }else{
            $this->jsonReturn('',400,'失败');
        }
        exit;
    }
}