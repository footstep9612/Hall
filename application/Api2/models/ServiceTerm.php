<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/5
 * Time: 11:00
 */
class ServiceTermModel extends PublicModel{
    //会员服务条款(二级,条目)
    protected $dbName = 'erui2_config';
    protected $tableName = 'service_term';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 会员服务条款(二级)
     * @time  2017-08-05
     * @author klp
     */
    public function getInfo($data) {
        if(isset($data['id']) && !empty($data['id'])) {
            $condition["service_cat_id"] = $data['id'];
        }
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $order='id desc';
        $fields = 'id,service_cat_id,term,choice_flag,add_flag,status,created_by,created_at,updated_by,checked_by,checked_at,deleted_flag';
        try {
            $result = $this->field($fields)
                           ->where($condition)
                           ->select();
            $data = array();
            if($result) {
                foreach($result as $item){
                    $item['term'] = json_decode($item['term'],true);
                    $ServiceItemModel = new ServiceItemModel();
                    $resultItem = $ServiceItemModel->getInfo($item);
                     foreach($item['term'] as $value){
                         if(empty($resultItem)){
                             $resultItem[$value['lang']] = $resultItem;
                         }
                        unset($item['term']);
                        $data[$value['lang']]['term'][] = array_merge($value,$item,$resultItem[$value['lang']]);
                    }
                }
            }
            return $data;
        } catch(Exception $e) {
           // var_dump($e);
            return array();
        }
    }

}