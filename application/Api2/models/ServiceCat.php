<?php
/**
 * @desc 审核日志模型
 * @author liujf 2017-07-01
 */
class ServiceCatModel extends PublicModel {

    protected $dbName = 'erui2_config';
    protected $tableName = 'service_cat';
    
    public function __construct() {
        parent::__construct();
    }
    //状态
    const STATUS_VALID = 'VALID';          //有效
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除

    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

	/**
     * @desc 获取列表
 	 * @author liujf 2017-07-01
     * @param array $condition
     * @return array
     */
    public function getList($condition,$limit,$order='id desc') {
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $fields = 'id,parent_id,level_no,category,sort_order,status,created_by,created_at,updated_by,checked_by,checked_at';
        if(!empty($limit)){
            $result = $this->field($fields)
                         ->where($condition)
                         ->limit($limit['page'] . ',' . $limit['num'])
                         ->order($order)
                         ->select();

        }else{
            $result = $this->field($fields)
                         ->where($condition)
                         ->order($order)
                         ->select();
        }
        $data =array();
        if($result){
            $termModel = new ServiceTermModel();
            foreach($result as $item){
                $count = $termModel->field('id')->where(['service_cat_id'=>$item['id']])->count();
                $item['count']=$count?$count:0;
                $item['category'] = json_decode($item['category']);
                $data[] = $item;
            }
        }
        return $data;
    }

    /**
    * 所有服务的详情
    * @param $input
    * @author klp
    */
    public function getAllService($token) {
        $condition = array(
            'deleted_flag' => self::DELETE_N,
            'status' => self::STATUS_VALID
        );
        if(empty($token['buyer_id'])){
            jsonReturn('',-1002,'[buyer_id]缺少!');
        }
        try{
            $data = array();
            //获取等级
            $buyerLevel = new BuyerModel();
            $buyer_level = $buyerLevel->field('buyer_level')->where(['id'=>$token['buyer_id']])->find();

            $ServiceTermModel = new ServiceTermModel();
            $ServiceItemModel = new ServiceItemModel();
            $result = $this->field('id,category,status')->where($condition)->select();
//            jsonReturn($result);
            if($result){
                foreach($result as $category){
                    //条款
                    $term = $ServiceTermModel->field('id as service_term_id,term,choice_flag,add_flag,status')->where([ 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'service_cat_id'=>$category['id']])->select();
                    //内容
                    $item = $ServiceItemModel->field('id as service_item_id,service_cat_id,service_term_id,item,status')->where([ 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'service_cat_id'=>$category['id']])->select();
                    $result = $this->initService($category,$term,$item);
                    if($result === false) {
                        jsonReturn('',ErrorMsg::FAILED);
                    }
                    $data[] = $result;
                }
                $data['buyer_level'] = $buyer_level['buyer_level'];
                return $data;
            }
            return array();
        }catch (Exception $e){
            return false;
        }
    }


    /**
     * 格式化服务条款
     * @param array $cat
     * @param array $term
     * @param array $item
     */
    protected function initService($category = [] , $term = [] ,$item =[]) {
        if($category !== false && $term !== false && $item !== false){
            $data = $data_term = $data_item = [];
            /**
             * 处理条款内容
             */
            if(!empty($item)) {
                foreach($item as $im){
                    $item_r = json_decode($im['item'],true);
                    if($item_r) {
                        foreach($item_r as $ir){
                            $ir['service_item_id'] = $im['service_item_id'];
                            $ir['status'] = $im['status'];
                            $data_item[$ir['lang']][$im['service_term_id']][] = $ir;
                        }
                    }
                }
            }
            /**
             * 处理条款
             */
            $i=0;
            if(!empty($term)) {
                foreach($term as $tm) {
                    $term_r = json_decode($tm['term'],true);
                    if($term_r) {
                        foreach($term_r as $term_i){
                            $term_i['service_term_id'] = $tm['service_term_id'];
                            $term_i['choice_flag'] = $tm['choice_flag'];
                            $term_i['add_flag'] = $tm['add_flag'];
                            $term_i['status'] = $tm['status'];
                            $term_i['item']=isset($data_item[$term_i['lang']][$tm['service_term_id']]) ? $data_item[$term_i['lang']][$tm['service_term_id']] : array();
                            $data_term[$term_i['lang']][] = $term_i;
                        }
                    }
                }
            }
            /**
             * 处理服务类别
             */

            if(!empty($category)) {
                $data['id'] = $category['id'];
                $category_ary = json_decode($category['category'] , true);
                if($category_ary) {
                    foreach ( $category_ary as $ite ) {
                        $ite['status'] = $category['status'];
                        $data[$ite['lang']]['category'] = $ite;
                        $data[$ite['lang']]['term'] = isset($data_term[$ite['lang']]) ? $data_term[$ite['lang']] : array();
                    }
                }
            }

           return $data;
        } else {
            return false;
        }
    }


}
