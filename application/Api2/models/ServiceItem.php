<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/5
 * Time: 10:59
 */
class ServiceItemModel extends PublicModel{
   //会员服务条款(三级)
    protected $dbName = 'erui_config';
    protected $tableName = 'service_item';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 会员服务条款(三级)
     * @time  2017-08-05
     * @author klp
     */
    public function getInfo($data) {
        if(isset($data['service_cat_id']) && !empty($data['service_cat_id'])) {
            $condition["service_cat_id"] = $data['service_cat_id'];
        }
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $order='id desc';
        $fields = 'id,service_cat_id,service_term_id,item,status,created_by,created_at,updated_by,checked_by,checked_at,deleted_flag';
        try {
            $result = $this->field($fields)
                           ->where($condition)
                           ->select();
            $data = array();
            if($result) {
                foreach($result as $item){
                    $item['item'] = json_decode($item['item'],true);
                    foreach($item['item'] as $value){
                        unset($item['item']);
                        $data[$value['lang']]['item'][] = array_merge($value,$item);
                    }
                }
            }
            return $data;
        } catch(Exception $e) {
            return array();
        }
    }

}