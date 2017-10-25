<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/31
 * Time: 9:25
 */
class BuyerLevelModel extends PublicModel{
    protected $dbName = 'erui2_config';
    protected $tableName = 'buyer_level';

    const STATUS_VALID = 'VALID';
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 会员等级服务 --门户
     */
    public function getLevelService(){
        /* $sql =  "select b.id, c.category, t.term, i.item, m.buyer_level_id
  from erui2_config.service_cat c, erui2_config.service_term t, erui2_config.service_item i
  left join erui2_config.member_service m on i.id = m.service_item_id and m.deleted_flag = 'N' and m.status = 'VALID'
  left join erui2_config.buyer_level b on b.id = m.buyer_level_id and b.deleted_flag = 'N' and b.status = 'VALID'
  where c.id = t.service_cat_id and t.id = i.service_term_id and c.status = 'VALID' and t.status = 'VALID' and i.status = 'VALID'
  order by c.id, t.id, i.id";
  $row = $this->query( $sql );
          jsonReturn($row);*/
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        //redis
        if(redisExist('LevelService')){
            return json_decode(redisGet('LevelService'),true);
        }
        try {
            $result = $this->field($fields)->where($where)->order('id')->group('buyer_level')->select();
            $data = array();
            if($result){
                $MemberServiceModel = new MemberServiceModel();
                foreach($result as $key=>$item) {
                    $whereService = array('deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID, 'buyer_level_id' => $item['id']);
                    $fields_service = 'id, buyer_level_id, service_cat_id, service_term_id, service_item_id';
                    $Ids = $MemberServiceModel->where($whereService)->field($fields_service)->find();
                    $serviceIds[] = $Ids;
                }
                if(!$serviceIds){
                    return false;
                }
                $ServiceCatModel = new ServiceCatModel();
                $ServiceTermModel = new ServiceTermModel();
                $ServiceItemModel = new ServiceItemModel();
                foreach($serviceIds as $buyerLevelIds){
                    //类型
                    $whereCat = array( 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'id'=>$buyerLevelIds['service_cat_id']);
                    $category = $ServiceCatModel->field('id as service_cat_id,category,level_no,sort_order,status')->where($whereCat)->select();
                    //条款
                    $whereTerm = array( 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'service_cat_id'=>$buyerLevelIds['service_cat_id']);
                    $term = $ServiceTermModel->field('id as service_term_id,term,choice_flag,add_flag,status')->where($whereTerm)->select();
                    //内容
                    $whereItem = array( 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'service_cat_id'=>$buyerLevelIds['service_cat_id']);
                    $item = $ServiceItemModel->field('id as service_item_id,service_cat_id,service_term_id,item,status')->where($whereItem)->select();
                    $res = $this->initService($category,$term,$item);
                    $data[] = $res;
                }
                if(!empty($data)){
                    redisSet('LevelService',json_encode($data),3600);
                }
                return $data;
            }
            return $data;
        }catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return array();
        }
    }
    /**
     * 格式化服务
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
                foreach ($item as $im) {
                    $item_r = json_decode($im['item'], true);
                    if ($item_r) {
                        foreach ($item_r as $ir) {
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
                foreach ($category as $item) {
                    $category_ary = json_decode($item['category'], true);
                    if ($category_ary) {
                        foreach ($category_ary as $ite) {
                            $data['service_cat_id'] = $item['service_cat_id'];
                            $ite['status'] = $category['status'];
                            $data[$ite['lang']]['category'] = $ite;
                            $data[$ite['lang']]['term'] = isset($data_term[$ite['lang']]) ? $data_term[$ite['lang']] : array();
                        }
                    }
                }
            }
            return $data;
        } else {
            return false;
        }
    }


    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     */
    public function getlist($order=" id desc") {
        $where = [
            'status'=> self::STATUS_VALID,
            'deleted_flag'=> self::DELETE_N
        ];
         $result = $this->where($where)->order($order)->select();
        $buyerLevel = $list = [];
        if($result) {
            foreach ($result as $item) {
                $item['buyer_level'] = json_decode($item['buyer_level'], true);
                foreach($item['buyer_level'] as $value) {
                    $buyerLevel['buyer_level'][$value['lang']] = $value;
                }
                $list[] = $buyerLevel['buyer_level'];
            }
        }
        return $list;
    }
}