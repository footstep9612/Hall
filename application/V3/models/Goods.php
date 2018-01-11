<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 * 客户
 */
class GoodsModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'goods';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //SKU总统计数量
    public function getGoods($lang){
        $year=date('Y');
        $sql_total="select sum(t_goods_value) as sku_tatalcount from erui_hall.goods";
        $total_count=$this->query($sql_total);
        //各分类下SKU数量
        if($lang=='en'){
            $sql="select goods_code.t_zh_en as sku_name,";
        }else{
            $sql="select goods_code.t_zh_name as sku_name,";
        }
        $sql.="sum(goods.t_goods_value) as sku_count from erui_hall.goods goods join erui_hall.goods_code goods_code on goods.t_group=goods_code.t_goods_code  GROUP BY goods.t_group";
        $goods=$this->query($sql);
        $arr=[];
        $arr+=$total_count[0];
        $arr['sku_count']=$goods;
        return $arr;
    }

}


