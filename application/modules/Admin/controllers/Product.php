<?php
/**
 * SPU
 * User: linkai
 * Date: 2017/6/16
 * Time: 18:18
 */
class ProductController extends PublicController{
    public function listAction(){
        $this->jsonReturn();
        $resolt = ProductModel::getList();
    }
}