<?php
/**
 * @desc 报价单明细模型
 * @author 买买提
 */
class QuoteItemModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote_item';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 删除报价单项(一个或多个)
     * @param $where 条件
     * @return bool True|False
     */
    public function delItem($where){
        return $this->where('inquiry_item_id IN('.$where.')')->save(['deleted_flag'=>'Y']);
    }

    /**
     * 获取sku列表
     * @param $request 条件
     * @return mixed 数据
     */
    public function getList($request){

        $where['a.inquiry_id'] = $request['inquiry_id'];
        $where['a.deleted_flag'] = 'N';

        $fields = 'a.id,b.sku,b.id inquiry_item_id,b.buyer_goods_no,b.name,b.name_zh,b.qty,b.unit,b.brand inquiry_brand,b.model,b.remarks,b.category,a.supplier_id,a.brand,a.purchase_unit_price,a.purchase_price_cur_bn,a.gross_weight_kg,a.package_mode,a.package_size,a.stock_loc,a.goods_source,a.delivery_days,a.period_of_validity,a.reason_for_no_quote,a.pn';
        return $this->alias('a')
            ->join('erui_rfq.inquiry_item b ON a.inquiry_item_id = b.id')
            ->field($fields)
            ->where($where)
            ->select();

    }

    public function updateSupplier($data){
        foreach ($data as $key=>$value){
            if(empty($value['period_of_validity'])){
                $value['period_of_validity'] = null;
            }
            $value['updated_at'] = date('Y-m-d H:i:s');
            try{
                $this->save($this->create($value));
            }catch (Exception $exception){
                return [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ];
            }
        }
        return true;
    }

    /**
     * 更新SKU信息
     * @param $data 数据对象
     * @param $user 当前用户
     *
     * @return array|bool
     */
    public function updateItem($data,$user){

        foreach ($data as $key=>$value){

            $value['updated_at'] = date('Y-m-d H:i:s');
            $value['updated_by'] = $user;

            //如果输填写了未报价分析原因
            if (!empty($value['reason_for_no_quote'])){
                try{
                    $this->save($this->create($value));
                }catch (Exception $exception){
                    return [
                        'code' => $exception->getCode(),
                        'message' => $exception->getMessage()
                    ];
                }
            } else {
                /**
                 * 如果是选择了供应商，一下信息是必填字段
                 * 报价产品描述，采购单价，采购币种，毛重，包装体积，包装方式，产品来源，存放地，交货期(天)，报价有效期
                 */

                //采购单价
                if (empty($value['purchase_unit_price'])){
                    return ['code'=>'-104','message'=>'采购单价必填'];
                }
                if (!is_numeric($value['purchase_unit_price'])){
                    return ['code'=>'-104','message'=>'采购单价必须是数字'];
                }
                //采购币种
                if (empty($value['purchase_price_cur_bn'])){
                    return ['code'=>'-104','message'=>'采购币种必选'];
                }
                //毛重
                if (empty($value['gross_weight_kg'])){
                    return ['code'=>'-104','message'=>'毛重必填'];
                }
                if (!is_numeric($value['gross_weight_kg'])){
                    return ['code'=>'-104','message'=>'毛重必须是数字'];
                }
                //包装体积
                if (empty($value['package_size'])){
                    return ['code'=>'-104','message'=>'包装体积必填'];
                }
                if (!is_numeric($value['package_size'])){
                    return ['code'=>'-104','message'=>'包装体积必须是数字'];
                }
                //包装方式
                if (empty($value['package_mode'])){
                    return ['code'=>'-104','message'=>'包装方式必填'];
                }
                //产品来源
                if (empty($value['goods_source'])){
                    return ['code'=>'-104','message'=>'产品来源必填'];
                }
                //存放地
                if (empty($value['stock_loc'])){
                    return ['code'=>'-104','message'=>'存放地必填'];
                }
                //交货期(天)，报价有效期
                if (empty($value['delivery_days'])){
                    return ['code'=>'-104','message'=>'交货期必填'];
                }
                if (!is_numeric($value['delivery_days'])){
                    return ['code'=>'-104','message'=>'交货期必须是数字'];
                }
                //报价有效期
                if (empty($value['period_of_validity'])){
                    return ['code'=>'-104','message'=>'报价有效期必填'];
                }

                $value['status'] = 'QUOTED';
                $value['quote_qty'] = $value['qty'];
                $value['quote_unit'] = $value['unit'];

                $this->save($this->create($value));

            }
        }
        return true;

    }

    /**
     * 保存SKU信息，不加任何必填校验
     * @param $data 数据对象
     * @param $user 当前用户
     *
     * @return array|bool
     */
    public function updateItemBatch($data,$user){

        foreach ($data as $key=>$value){

            if(!empty($value['purchase_unit_price'])){
                if (!is_numeric($value['purchase_unit_price'])){
                    return ['code'=>'-104','message'=>'采购单价必须是数字'];
                }
            }
            if(!empty($value['gross_weight_kg'])) {
                if (!is_numeric($value['gross_weight_kg'])) {
                    return ['code' => '-104', 'message' => '毛重必须是数字'];
                }
            }
            if(!empty($value['package_size'])){
                if (!is_numeric($value['package_size'])){
                    return ['code'=>'-104','message'=>'包装体积必须是数字'];
                }
            }
            if(!empty($value['delivery_days'])) {
                if (!is_numeric($value['delivery_days'])) {
                    return ['code' => '-104', 'message' => '交货期必须是数字'];
                }
            }

            $value['updated_at'] = date('Y-m-d H:i:s');
            $value['updated_by'] = $user;

            $this->save($this->create($value));


        }
        return true;

    }

    public function syncSku($request,$user){

        $quoteModel = new QuoteModel();
        $inquiryItemModel = new InquiryItemModel();
        //查询所有已经添加过的，后面判断是添加还是修改
        $quoteItems = $this->where(['inquiry_id'=>$request['inquiry_id'],'deleted_flag'=>'N'])->getField('inquiry_item_id',true);
        $quoteId = $quoteModel->getQuoteIdByInQuiryId($request['inquiry_id']);

        $inquiryItems = $inquiryItemModel->where(['inquiry_id'=>$request['inquiry_id'],'deleted_flag'=>'N'])->select();

        foreach ($inquiryItems as $inquiry=>$item){
            //判断是添加还是修改
            if (!in_array($item['id'],$quoteItems)){
                $this->add($this->create([
                    'quote_id' => $quoteId,
                    'inquiry_id' => $item['inquiry_id'],
                    'inquiry_item_id' => $item['id'],
                    'sku' => $item['sku'],
                    'quote_qty' => $item['qty'],
                    'quote_unit' => $item['unit'],
                    'created_by' => $user,
                    'created_at' => date('Y-m-d H:i:s')
                ]));
            }

        }

    }

}
