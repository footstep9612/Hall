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
class ShipmentModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'shipment';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //全球区域买家数量统计
    public function getShipmentrend($lang){
        $start=date('Y-m-d', strtotime("-15 day"));
        $date=date('Y-m-d', strtotime("-1 day"));

        $sql="select t_date shipment_date ,sum(t_shipment_value) as shipment_coount from erui_hall.shipment WHERE t_date BETWEEN '$start' AND '$date' GROUP BY t_date ORDER BY t_date DESC ";
        $shipment=$this->query($sql);
        $arr=[];
        for($i=1;$i<=15;$i++){
            $arr[]=date('Y-m-d', strtotime("-$i day"));
        }
        $exist=[];
        foreach($arr as $k => $v){
            foreach($shipment as $key => $value){
                if($value['shipment_date'] == $v){
                    $exist[]=$value['shipment_date'];
                }
            }
        }
        $null=array_diff($arr,$exist);
        $dataNull=[];
        if(!empty($null)){
            foreach($null as $k => $v){
                $dataNull[]=array('shipment_date'=>$v,'shipment_coount'=>0);
            }
            $info=array_merge($shipment,$dataNull);
            $sort=[];
            foreach($info as $k =>$v){
                $sort[$v['shipment_date']]=$v;
            }
            krsort($sort);
            $shipment=[];
            foreach($sort as $k => $v){
                $shipment[]=$v;
            }
        }

        return $shipment;
    }

}


