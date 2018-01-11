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
class WareHouseModel extends Model {
    protected $dbName = 'erui_hall';
    protected $tableName = 'warehouse';

//    const STATUS = '';

    public function __construct() {
        parent::__construct();
    }
    //全球区域买家数量统计
    public function getWarehouse($lang){
        if($lang=='en'){
            $sql="select area_code.t_zh_en country_name,";
        }else{
            $sql="select area_code.t_zh_name country_name,";
        }
        $sql.="t_buyer_value as warehouse, area_code.t_lon_x lon,area_code.t_lat_y lat from erui_hall.warehouse warehouse join erui_hall.area_code area_code on warehouse.t_country=area_code.t_area_code WHERE t_type=1";
        $warehouse=$this->query($sql);
        return $warehouse;
    }

}


