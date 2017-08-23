<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 11:39
 */
class MemberServiceModel extends PublicModel{
    protected $dbName = 'erui2_config';
    protected $tableName = 'member_service';

    public function __construct(){
        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID';          //有效
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除

    /**
     * 会员等级查看
     * @author klp
     */
    public function levelService($token){

        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, service_cat_id, service_term_id, service_item_id, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        try{
            //获取会员等级
            $buyerLevel = new BuyerModel();
            $buyer_level = $buyerLevel->field('buyer_level')->where(['id'=>$token['buyer_id']])->find();

            $result = $this->field($fields)->where($where)->select();

            $data = array();
            if($result) {
                $ServiceCatModel = new ServiceCatModel();
                $ServiceTermModel = new ServiceTermModel();
                $ServiceItemModel = new ServiceItemModel();
                foreach($result as $item){
                    $catName = $ServiceCatModel->field('category')->where(['id'=>$item['service_cat_id'],'status'=>'VALID'])->find();
                    $termName = $ServiceTermModel->field('term')->where(['id'=>$item['service_term_id'],'status'=>'VALID'])->find();
                    $itemName = $ServiceItemModel->field('item')->where(['id'=>$item['service_item_id'],'status'=>'VALID'])->find();

                    $data[$item['buyer_level']]['catName']['service_cat_id'] = json_decode($catName['category']?$catName['category']:'',true);
                    $data[$item['buyer_level']]['termName'][] = json_decode($termName['term']?$termName['term']:'',true);
                    $data[$item['buyer_level']]['itemName'][] = json_decode($itemName['item']?$itemName['item']:'',true);
                }
                jsonReturn($data);
                return $data;
            }
            return array();
        } catch(Exception $e){
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return array();
        }

    }
    /**
     * 会员等级匹配服务
     * @author klp
     */
    public function service($token){
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, service_cat_id, service_term_id, service_item_id, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';

            //获取会员等级
            $buyerLevel = new BuyerModel();
            $buyer_level = $buyerLevel->field('buyer_level')->where(['id'=>$token['buyer_id']])->find();

            $result = $this->field($fields)->where($where)->select();
        //--------------
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $result = $this->field('service_cat_id')->where($where)->group('service_cat_id')->select();
        foreach($result as $key=>$val){
            $where1 = ['service_cat_id' =>$val['service_cat_id'],'status'=>'VALID','deleted_flag'=>'N'];
            $rs =  $this->field('service_term_id')->where($where1)->group('service_term_id')->select();
            foreach($rs as $key1=>$val1){
                $where2 = ['service_term_id'=>$val1['service_term_id'],'status'=>'VALID','deleted_flag'=>'N'];
                $rs1 =  $this->field('service_item_id,id')->where($where2)->group('service_item_id')->select();
                $rs[$key1]['item'] = $rs1;
            }
            $result[$key]['term'] = $rs;
        }
        return $result? $result:array();
    }
}