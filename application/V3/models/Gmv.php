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
class GmvModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'gmv';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }

    public function getgmv($lang){
        $year=date('Y');
        $day=date('Y-m-d');

        //当年年累计总量
        $sql_year = "select sum(t_gmv_value) year_gmv_tatalcount from erui_hall.gmv where DATE_FORMAT(t_date, '%Y')='$year'";
        $info_year=$this->query($sql_year);
        //当日最新成交总量
        $sql_day = "select sum(t_gmv_value) day_gmv_tatalcount from erui_hall.gmv where t_date='$day'";
        $info_day=$this->query($sql_day);
        //区域成交累计

        if($lang=='en'){
            $sql_area="SELECT area_code.t_zh_en as area_name,";
        }else{
            $sql_area="SELECT area_code.t_zh_name as area_name,";
        }
        $sql_area.="SUM(t_gmv_value) as gmv_per from erui_hall.gmv as gmv JOIN erui_hall.area_code as area_code on gmv.t_area=area_code.t_area_code where DATE_FORMAT(gmv.t_date, '%Y')='$year' and area_code.t_type=0 GROUP BY gmv.t_area";
        $info_area=$this->query($sql_area);
        $arr=[];
        $arr+=$info_year[0];
        $arr+=$info_day[0];
        $arr['percent']=$info_area;
        if(empty($arr['year_gmv_tatalcount'])){
            $arr['year_gmv_tatalcount']=0;
        }
        if(empty($arr['day_gmv_tatalcount'])){
            $arr['day_gmv_tatalcount']=0;
        }
        return $arr;
    }
    public function getGmvTrend($lang){
        $start=date('Y-m-d', strtotime("-15 day"));
        $date=date('Y-m-d', strtotime("-1 day"));

        $sql="select t_date gmv_date,sum(t_gmv_value) gmv_count from erui_hall.gmv WHERE t_date BETWEEN '$start' AND '$date' GROUP BY t_date ORDER BY t_date DESC ";
        $gmv=$this->query($sql);
        $arr=[];
        for($i=1;$i<=15;$i++){
            $arr[]=date('Y-m-d', strtotime("-$i day"));
        }
        $exist=[];
        foreach($arr as $k => $v){
            foreach($gmv as $key => $value){
                if($value['gmv_date'] == $v){
                    $exist[]=$value['gmv_date'];
                }
            }
        }
        $null=array_diff($arr,$exist);
        $dataNull=[];
        if(!empty($null)){
            foreach($null as $k => $v){
                $dataNull[]=array('gmv_date'=>$v,'gmv_count'=>0);
            }
            $info=array_merge($gmv,$dataNull);
            $sort=[];
            foreach($info as $k =>$v){
                $sort[$v['gmv_date']]=$v;
            }
            krsort($sort);
            $gmv=[];
            foreach($sort as $k => $v){
                $gmv[]=$v;
            }
        }
        return $gmv;
    }

}
