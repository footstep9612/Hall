<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/30
 * Time: 16:25
 */
class SupplierproductController extends SupplierpublicController{

    public function init(){
        //$this->supplier_token = false;
        parent::init();
    }

    //状态
    const STATUS_DRAFT = 'DRAFT'; //草稿；
    const STATUS_APPROVING = 'APPROVING'; //审核中；
    const STATUS_APPROVED = 'APPROVED'; //审核通过；
    const STATUS_REJECTED = 'INVALID'; //驳回；
    const STATUS_VALID = 'APPROVED'; //有效；


    //$sql = 'SELECT * FROM erui_dict.brand where brand like '%\"name":\""LONGHUI"%' AND `status`="VALID";
    //$sql2="select * from table where content like '%\"actid":\""$id"\"%\'";
    /**
     * 获取瑞商产品列表信息
     */
    public function getListAction() {
        $condition = $this->getPut();
        $supplier_product_model = new SupplierProductModel();
        $res = $supplier_product_model->getList($condition);
        $count = $supplier_product_model->getCount($condition);
        if($res){
            $datajson['code'] = MSG::MSG_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = MSG::MSG_FAILED;
            $datajson['data'] = [];
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 瑞商产品--新增
     */
    public function addSupplierProductAction(){
        $data = $this->getPut();
        $lang = $this->getLang($data['lang']);
        $supplier_product_model = new SupplierProductModel();
        // 校验字段
        $checkFields = ['material_cat_no', 'name', 'brand', 'warranty', 'product_attachs'];
        $resultFields = $this->_checkFields($data, $checkFields, 'required'); //校验字段
        $spu = $supplier_product_model->createSpu(strtoupper($resultFields['material_cat_no'])); //不存在生产spu
        $name = $this->checkName($resultFields['name'], $lang, 'name');  //产品名称校验
        if(!$name){
            jsonReturn('', -1, '产品名称已存在!');
        }
        $brand = $this->editBrand($resultFields['brand'],$lang);  //品牌校验

        $supplier_product_model->startTrans();
        try {
            // 供应商产品信息
            $productData = [
                'spu' => $spu,
                'lang' => $lang,
                'material_cat_no' => $resultFields['material_cat_no'],
                'name' => $name,
                'brand' => $brand,
                'warranty' => $resultFields['warranty'],
                'description' => $data['description'],
                'tech_paras' => $data['tech_paras'],
                'status' => $data['status'] ? strtoupper($data['status']) : self::STATUS_DRAFT,  //默认草稿
                'deleted_flag' => 'N',    // 非删除
                'created_at' => $this->getTime()
            ];
            //是否提交审核
            if(isset($data['check']) && $data['check']=='CHECK'){
                $productData['status'] = self::STATUS_APPROVING;
            }
            $res_product = $supplier_product_model->addRecord($productData);
            if (!$res_product) {
                $supplier_product_model->rollback();
            }
            //产品附件
            $product_attachs_model = new SupplierProductAttachModel();
            $res_pro_attach = $product_attachs_model->uploadattachs($resultFields['product_attachs'],$spu);
            if (!$res_pro_attach) {
                $supplier_product_model->rollback();
            }
            //商品信息
            $supplier_goods_model = new SupplierGoodsModel();
            $sku = $supplier_goods_model->setRealSku($spu); //生成新的sku
            $checkGoodsFields = ['model', 'exw_days', 'min_pack_naked_qty', 'nude_cargo_unit', 'min_pack_unit','min_order_qty','price'];
            $resultGoodsFields = $this->_checkFields($data['goods'], $checkGoodsFields, 'required'); //校验字段
            $goodsData = [
                'spu' => $spu,
                'sku' => $sku,
                'lang' => $lang,
                'name' => $data['name'] ? $data['name'] : '',
                'model' => $resultGoodsFields['model'],
                'exw_days' => $resultGoodsFields['exw_days'],  //出货周期（天）
                'min_pack_naked_qty' => $resultGoodsFields['exw_days'],  //最小包装内裸货商品数量
                'nude_cargo_unit' => $resultGoodsFields['nude_cargo_unit'],  //商品裸货单位
                'min_pack_unit' => $resultGoodsFields['min_pack_unit'],  //最小包装单位
                'min_order_qty' => $resultGoodsFields['min_order_qty'],  //最小订货数量
                'price' => $resultGoodsFields['price'],  //价格
                'status' => $data['status'] ? strtoupper($data['status']) : self::STATUS_VALID,  //默认有效状态
                'deleted_flag' => 'N',    // 非删除
                'created_at' => $this->getTime()
            ];
            $res_goods = $supplier_goods_model->addRecord($goodsData);
            if (!$res_goods) {
                $supplier_product_model->rollback();
            }
            //商品其他属性
            if(isset($data['goods']['attr']) && !empty($data['goods']['attr'])){
                $supplier_goods_attr_model = new SupplierGoodsAttrModel();
                $attrData = [
                    'lang' => $lang,
                    'spu' => $spu,
                    'sku' => $sku,
                    'other_attrs' => !empty($attr['other_attrs']) ? json_encode($attr['other_attrs'], JSON_UNESCAPED_UNICODE) : null,
                    'ex_goods_attrs' => !empty($attr['ex_goods_attrs']) ? json_encode($attr['ex_goods_attrs'], JSON_UNESCAPED_UNICODE) : null,
                    'status' => self::STATUS_VALID,
                    'deleted_flag' => 'N',
                ];
                $res_goods_attr = $supplier_goods_attr_model->addRecord($attrData);
                if (!$res_goods_attr) {
                    $supplier_product_model->rollback();
                }
            }else {
                $res_goods_attr = true;
            }

            if($res_product && $res_pro_attach && $res_goods && $res_goods_attr){
                $supplier_product_model->commit();
                $this->setCode(MSG::MSG_SUCCESS);
                $datajson['data'] = $res_product;
                $this->jsonReturn($datajson);
            }else {
                $supplier_product_model->rollback();
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }catch (Exception $e) {
            $supplier_product_model->rollback();
            $res = false;
            $this->jsonReturn($res);
        }
    }

    /**
     * 瑞商产品--编辑
     */
    public function editSupplierProductAction(){
        $data = $this->getPut();
        $lang = $this->getLang($data['lang']);


    }


    //产品名称校验
    private function checkName($param, $lang, $name='name'){
        if (empty($param)) {
            return false;
        }
        $supplier_product_model = new SupplierProductModel();
        $condition = array($name => $param, 'lang'=>$lang);
        $result = $supplier_product_model->field($name)->where($condition)->find();
        if($result){
            return false;
        }
        return true;
    }

    //品牌校验
    private function editBrand($brand,$lang){
        if (is_numeric($brand)) {
            $data['brand'] = '';
            $brand_model = new BrandModel();
            $brandInfo = $brand_model->info($brand);
            if ($brandInfo) {
                $brandAry = json_decode($brandInfo['brand'], true);
                foreach ($brandAry as $r) {
                    if ($r['lang'] == $lang) {
                        $brand_ary = array(
                            'name' => $r['name'],
                            'style' => isset($r['style']) ? $r['style'] : 'TEXT',
                            'label' => isset($r['label']) ? $r['label'] : $r['name'],
                            'logo' => isset($r['logo']) ? $r['logo'] : '',
                        );
                        ksort($brand_ary);
                        $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                        break;
                    }
                }
            }
        } else {
            if (is_array($brand)) {
                ksort($brand);
                $data['brand'] = json_encode($brand, JSON_UNESCAPED_UNICODE);
            } elseif (!empty($brand)) {
                $brand_ary = array(
                    'name' => $brand,
                    'style' => 'TEXT',
                    'label' => $brand,
                    'logo' => '',
                );
                ksort($brand_ary);
                $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
            } else {
                $data['brand'] = '';
            }
        }
        return $data['brand'];
    }


    //校验必填字段
    private function _checkFields($param, $field, $name='required'){
        if (empty($param) || empty($field)) {
            return array();
        }
        foreach ($field as $k => $item) {
            if($name == 'required'){
                if ($param[$k] == '' || empty($param[$k])) {
                    jsonReturn('', -1, '缺少参数!');
                }
            }
            //继续添加...
            $param[$k] = trim($param[$k]);
            continue;
        }
        return $param;
    }

    public function getLang($lang) {
        $lang = $lang ? $lang : 'zh';
        return $lang;
    }

    public function getSupplierId($id) {
        return $id ? $id : ($this->supplier_user['supplier_id']?$this->supplier_user['supplier_id']:SUID);
    }

    public function getTime() {
        return $time = date('Y-m-d H:i:s',time());
    }

}