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
class PageViewModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'pageview';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //全球区域买家数量统计
    public function getPageViewTrend($lang){
        $start=date('Y-m-d', strtotime("-15 day"));
        $date=date('Y-m-d', strtotime("-1 day"));
        $sql="select t_date pv_date ,sum(t_pv_value) as pv_coount from erui_hall.pageview WHERE t_date BETWEEN '$start' AND '$date' GROUP BY t_date ORDER BY t_date DESC ";
        $pv=$this->query($sql);
        $arr=[];
        for($i=1;$i<=15;$i++){
            $arr[]=date('Y-m-d', strtotime("-$i day"));
        }
        $exist=[];
        foreach($arr as $k => $v){
            foreach($pv as $key => $value){
                if($value['pv_date'] == $v){
                    $exist[]=$value['pv_date'];
                }
            }
        }
        $null=array_diff($arr,$exist);
        $dataNull=[];
        if(!empty($null)){
            foreach($null as $k => $v){
                $dataNull[]=array('pv_date'=>$v,'pv_coount'=>0);
            }
            $info=array_merge($pv,$dataNull);
            $sort=[];
            foreach($info as $k =>$v){
                $sort[$v['pv_date']]=$v;
            }
            krsort($sort);
            $pv=[];
            foreach($sort as $k => $v){
                $pv[]=$v;
            }
        }

        return $pv;
    }

}


