<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/2
 * Time: 14:04
 */
class GoodsCostPriceModel extends PublicModel {
    protected $tableName = 'goods_cost_price'; //数据表名称
    protected $dbName = 'erui2_goods';         //数据库名称

    public function __construct($str = '') {

        parent::__construct();
    }

    //状态--INVALID,CHECKING,VALID,DELETED
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const STATUS_CHECKING = 'CHECKING'; //审核；
    const STATUS_DRAFT = 'DRAFT';       //草稿

    /**
     * 通过商品sku编码获取价格策略信息
     * @param array $sku
     * @return array
     * @author klp
     */
    public function getInfo($input){
       if(empty($input['sku'])){
           return false;
       }
        $fields = 'id, sku, supplier_id, price, price_unit, price_cur_bn, min_purchase_qty, pricing_date, price_validity, status, created_by, created_at';
        try{
            $result = $this->field($fields)->where(['sku'=>$input['sku']])->select();
            $data = array();
            if($result) {
                //通过supplier_id查询供应商名称
                $SupplierModel = new SupplierModel();
                foreach($result as $item) {
                    $item['supplier_name'] ='';
                    $info = $SupplierModel->field('name')->where(['id'=>$item['supplier_id']])->find();
                    if($info){
                        $item['supplier_name'] = $info['name'];
                    }
                    $data[] = $item;
                }
            }
            return $data;
        } catch(Exception $e) {
            return false;
        }
    }


    /**
     * sku价格策略新增/编辑 -- 公共
     * @author klp
     * @return array
     */
    public function editCostprice($input,$sku='',$admin=''){
        if(empty($input) || empty($sku)) {
            return false;
        }
        $results = array();
        try {
            foreach ($input as $key => $value) {
                $checkout = $this->checkParam($value,$sku);
                $data = [
                    'supplier_id' => isset($checkout['supplier_id']) ? $checkout['supplier_id'] : '',
                    'contact_first_name' => isset($checkout['contact_first_name']) ? $checkout['contact_first_name'] : '',
                    'contact_last_name' => isset($checkout['contact_last_name']) ? $checkout['contact_last_name'] : '',
                    'price' => isset($checkout['price']) ? $checkout['price'] : null,
                    'price_unit' => isset($checkout['price_unit']) ? $checkout['price_unit'] : '',
                    'price_cur_bn' => isset($checkout['price_cur_bn']) ? $checkout['price_cur_bn'] : '',
                    'min_purchase_qty' => isset($checkout['min_purchase_qty']) ? $checkout['min_purchase_qty'] : 1,
                    'pricing_date' => isset($checkout['pricing_date']) ? $checkout['pricing_date'] : null,
                    'price_validity' => isset($checkout['price_validity']) ? $checkout['price_validity'] : null
                ];
                //存在sku编辑,反之新增,后续扩展性

                if(isset($checkout['id']) && !empty($checkout['id'])) {
                        $data['updated_by'] = $admin;
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                        $where = [
                            'sku' => $sku,
                            'id' => $checkout['id']
                        ];
                        $res = $this->where($where)->save($data);
                    if (!$res) {
                        return false;
                    }
                } else {
                    $data['status'] = self::STATUS_VALID;
                    $data['sku'] = $sku;
                    $data['created_by'] = $admin;
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $res = $this->add($data);
                    if (!$res) {
                        return false;
                    }
                }
            }
            if($res) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }
            return $results;
        }catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }
    /**
     * 参数校验,目前只测必须项
     * @author klp
     * @return array
     */
    public function checkParam($data,$sku){
        if(empty($data)) {
            return false;
        }
        $results = array();
        if(empty($sku)) {
            $results['code'] = '-1';
            $results['message'] = 'sku缺失';
        }
        if(empty($data['min_purchase_qty'])) {
            $results['code'] = '-1';
            $results['message'] = '[最小购买量]缺失';
        }
        if(empty($data['supplier_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[supplier_id]缺失';
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
    }

}