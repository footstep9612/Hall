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
class BuyerModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'buyer';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //全球区域买家数量统计
    public function getBuyer($lang){
        if($lang=='en'){
            $sql="SELECT area_code.t_zh_en as area_name,";
        }else{
            $sql="SELECT area_code.t_zh_name as area_name,";
        }
        $sql.="area_code.t_lon_x lon,area_code.t_lat_y lat,SUM(t_buyer_value) as area_buyer from erui_hall.buyer as buyer JOIN erui_hall.area_code as area_code on buyer.t_area=area_code.t_area_code where t_type=0 GROUP BY buyer.t_area";
        $buyer=$this->query($sql);
        return $buyer;
    }

}


