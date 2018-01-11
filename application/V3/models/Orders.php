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
class OrdersModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'orders';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //全球区域买家数量统计
    public function getOrders($lang){
        $start=date('Y-m-d', strtotime("-15 day"));
        $date=date('Y-m-d', strtotime("-1 day"));

        $sql="select t_date order_date ,sum(t_orders_value) as order_count from erui_hall.orders WHERE t_date BETWEEN '$start' AND '$date' GROUP BY t_date ORDER BY t_date DESC ";
        $orders=$this->query($sql);
        $arr=[];
        for($i=1;$i<=15;$i++){
            $arr[]=date('Y-m-d', strtotime("-$i day"));
        }
        $exist=[];
        foreach($arr as $k => $v){
            foreach($orders as $key => $value){
                if($value['order_date'] == $v){
                    $exist[]=$value['order_date'];
                }
            }
        }
        $null=array_diff($arr,$exist);
        $dataNull=[];
        if(!empty($null)){
            foreach($null as $k => $v){
                $dataNull[]=array('order_date'=>$v,'order_count'=>0);
            }
            $info=array_merge($orders,$dataNull);
            $sort=[];
            foreach($info as $k =>$v){
                $sort[$v['order_date']]=$v;
            }
            krsort($sort);
            $orders=[];
            foreach($sort as $k => $v){
                $orders[]=$v;
            }
        }

        return $orders;
    }

}


