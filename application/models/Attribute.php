<?php

/**
 * Class AttributeController
 * 属性
 */
class AttributeModel extends PublicModel
{
    public function init()
    {
        $this->_GoodsAttr_Model = new GoodsAttrModel();
        $this->_GoodsAttrTpl_Model = new GoodsAttrTplModel();
        $this->_ProAttr_Model = new ProAttrModel();
    }
    public function __construct() {
        parent::__construct();
    }

    //获取商品属性列表
    public function GetSkuAction($sku, $lang = "en")
    {
        if(empty($sku)){
            return self::OutJson(-2, '无效的请求');
        }

        /*查询商品属性表属性信息*/
        $where['sku'] = $sku;
        $where['lang'] = $lang;
        $res = $this->_GoodsAttr_Model->WhereList( $where);
        if(!$res){
            return self::OutJson(-1, '商品属性数据为空');
        }

        /*查询对应商品模板属性值并合并*/
        for($i=0;$i<count($res);$i++){
            $where_tpl['attr_no'] = $res[$i]['attr_no'];
            $where_tpl['lang'] = $lang;
            $res_attr = $this->_GoodsAttrTpl_Model->WhereAttrlist( $where_tpl);
            if($res_attr){
                $res[$i] = array_merge($res_attr[0], $res[$i]);
            }
        }
        return self::OutJson(0, '获取成功', $res);
    }

    //获取产品属性列表
    public function GetSpu($spu, $lang = "en")
    {
        if(empty($spu)){
            return self::OutJson(-2, '无效的请求');
        }
        $where['spu'] = $spu;
        $where['lang'] = $lang;
        $res = $this->_ProAttr_Model->WhereList( $where);
        if(!$res){
           return self::OutJson(-1, '产品属性数据为空');
        }
        return self::OutJson(0, '获取成功', $res);
    }

/**
 * 按json方式输出通信数据
 * @param integer $code 状态码
 * @param string $message 提示信息
 * @param array $data 数据
 * return
 */
    public static function OutJson($code, $message = '', $data = array(), $type = "json") {

        if(!is_numeric($code)) {
            return '';
        }
        $result = array(
            'code'     => $code,
            'message'  => $message,
            'data'     => $data
        );
        if($type == 'json') {
            return  json_encode($result);
        } elseif($type == 'array'){
            return $result;
        }
        exit;
    }
    //
    public function InsertSpuAttr($data)
    {

    }

    //
    public function InsertSkuAttr($data)
    {

    }

    //
    public function UpdatetSpuAttr()
    {
        //$data = $this->getRequest->getPost();

    }

    //
    public function UpdatetSkuAttr()
    {

    }

    //
    public function DeleteSpuAttr($data)
    {

    }

    //
    public function DeleteSkuAttr($data)
    {

    }
}