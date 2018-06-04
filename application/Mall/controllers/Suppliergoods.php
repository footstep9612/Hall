<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/30
 * Time: 16:25
 */
class SuppliergoodsController extends SupplierpublicController{

    public function init(){
        //$this->supplier_token = false;
        parent::init();
    }

    //状态
    const STATUS_DRAFT = 'DRAFT'; //草稿；
    const STATUS_APPROVING = 'APPROVING'; //审核中；
    const STATUS_APPROVED = 'APPROVED'; //审核通过；
    const STATUS_REJECTED = 'INVALID'; //驳回；






}