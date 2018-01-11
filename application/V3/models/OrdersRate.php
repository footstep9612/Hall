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
class OrdersRateModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'orders_rate';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //全球区域买家数量统计
    public function getOrdersRratetrend($lang){
        $start=date('Y-m-d', strtotime("-15 day"));
        $date=date('Y-m-d', strtotime("-1 day"));

        $sql="select t_date rate_date ,sum(t_orders_rate_value) as rate_coount from erui_hall.orders_rate WHERE t_date BETWEEN '$start' AND '$date' GROUP BY t_date ORDER BY t_date DESC ";
        $rate=$this->query($sql);
        $arr=[];
        for($i=1;$i<=15;$i++){
            $arr[]=date('Y-m-d', strtotime("-$i day"));
        }
        $exist=[];
        foreach($arr as $k => $v){
            foreach($rate as $key => $value){
                if($value['rate_date'] == $v){
                    $exist[]=$value['rate_date'];
                }
            }
        }
        $null=array_diff($arr,$exist);
        $dataNull=[];
        if(!empty($null)){
            foreach($null as $k => $v){
                $dataNull[]=array('rate_date'=>$v,'rate_coount'=>0);
            }
            $info=array_merge($rate,$dataNull);
            $sort=[];
            foreach($info as $k =>$v){
                $sort[$v['rate_date']]=$v;
            }
            krsort($sort);
            $rate=[];
            foreach($sort as $k => $v){
                $rate[]=$v;
            }
        }

        return $rate;
    }

}


