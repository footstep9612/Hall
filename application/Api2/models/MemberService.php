<?php

/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 11:39
 */
class MemberServiceModel extends PublicModel {

    protected $dbName = 'erui_config';
    protected $tableName = 't_member_biz_service';

    public function __construct() {
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

    public function levelService($token) {

        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, service_cat_id, service_term_id, service_item_id, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        try {
            //获取会员等级
            $buyerLevel = new BuyerModel();
            $buyer_level = $buyerLevel->field('buyer_level')->where(['id' => $token['buyer_id']])->find();

            $result = $this->field($fields)->where($where)->select();

            $data = array();
            if ($result) {
                $ServiceCatModel = new ServiceCatModel();
                $ServiceTermModel = new ServiceTermModel();
                $ServiceItemModel = new ServiceItemModel();
                foreach ($result as $item) {
                    $catName = $ServiceCatModel->field('category')->where(['id' => $item['service_cat_id'], 'status' => 'VALID'])->find();
                    $termName = $ServiceTermModel->field('term')->where(['id' => $item['service_term_id'], 'status' => 'VALID'])->find();
                    $itemName = $ServiceItemModel->field('item')->where(['id' => $item['service_item_id'], 'status' => 'VALID'])->find();

                    $data[$item['buyer_level']]['catName']['service_cat_id'] = json_decode($catName['category'] ? $catName['category'] : '', true);
                    $data[$item['buyer_level']]['termName'][] = json_decode($termName['term'] ? $termName['term'] : '', true);
                    $data[$item['buyer_level']]['itemName'][] = json_decode($itemName['item'] ? $itemName['item'] : '', true);
                }
                jsonReturn($data);
                return $data;
            }
            return array();
        } catch (Exception $e) {
            var_dump($e);
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return array();
        }
    }

}
