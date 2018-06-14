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


    /**
     * 获取瑞商产品列表信息
     */
    public function getListAction() {
        $condition = $this->getPut();
        $lang = $this->getLang($condition['lang']);
        $supplier_product_model = new SupplierProductModel();
        $condition['supplier_id'] = $this->getSupplierId($condition['supplier_id']);
        $res = $supplier_product_model->getList($condition);
        $count = $supplier_product_model->getCount($condition);
        if($res){
            foreach($res as &$item){
                $supplier_product_attach_model = new SupplierProductAttachModel();
                $item['attachs'] = $supplier_product_attach_model->getList(['spu'=>$item['spu']]);
                $materialCatModel = new MaterialCatModel();
                $item['material_cat'] = $materialCatModel->getinfo($item['material_cat_no'],$lang);
                if($item['status']==self::STATUS_REJECTED){
                    $supplier_product_checklog_model = new SupplierProductCheckLogModel();
                    $check_log = $supplier_product_checklog_model->getList(['spu'=>$item['spu']]);
                    $item['check_list'] = $check_log;
                }
            }
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
     * 获取瑞商产品详情
     */
    public function getSupplierProductInfoAction(){
        $condition = $this->getPut();
        $condition['lang'] = $this->getLang($condition['lang']);
        $condition['supplier_id'] = $this->getSupplierId($condition['supplier_id']);
        if(!isset($condition['spu']) || empty($condition['spu'])){
            jsonReturn('',-1,'缺少产品参数');
        }
        $supplier_product_model = new SupplierProductModel();
        $res = $supplier_product_model->getDetail($condition);
        $supplier_product_attach_model = new SupplierProductAttachModel();
        $res_attach = $supplier_product_attach_model->getDetail($condition);
        if($res){
            $materialCatModel = new MaterialCatModel();
            $res['material_cat'] = $materialCatModel->getinfo($res['material_cat_no'],$condition['lang']);
            if($res_attach){
                $res['attachs'] = $res_attach;
            }
            $datajson['code'] = MSG::MSG_SUCCESS;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = MSG::MSG_FAILED;
            $datajson['data'] = [];
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
    /**
     * 获取瑞商商品详情
     */
    public function getSupplierGoodsInfoAction(){
        $condition = $this->getPut();
        $condition['lang'] = $this->getLang($condition['lang']);
        $condition['supplier_id'] = $this->getSupplierId($condition['supplier_id']);
        if(!isset($condition['spu']) || empty($condition['spu'])){
            jsonReturn('',-1,'缺少产品参数');
        }
        $supplier_goods_model = new SupplierGoodsModel();
        $res = $supplier_goods_model->getDetail($condition);
        if($res){
            $datajson['code'] = MSG::MSG_SUCCESS;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = MSG::MSG_FAILED;
            $datajson['data'] = [];
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 瑞商产品--新增/编辑
     */
    public function editSupplierProductAction(){
        $data = $this->getPut();
        $lang = $this->getLang($data['lang']);
        $supplier_id = $this->getSupplierId($data['supplier_id']);
        //$supplier_id = '229';//测试使用
        $supplier_product_model = new SupplierProductModel();
        // 校验字段
        $checkFields = ['material_cat_no', 'name', 'brand', 'warranty', 'product_attachs'];
        $resultFields = $this->_checkFields($data, $checkFields, 'required'); //校验字段
        $brand = $this->editBrand($resultFields['brand'],$lang);  //品牌校验
        $spu= $name ='';
        if(!isset($data['spu']) || empty($data['spu'])){
            $name = $this->checkName($resultFields['name'], $lang, 'name');  //产品名称校验
            if(!$name){
                jsonReturn('', -1, '产品名称已存在!');
            }
            $spu = $supplier_product_model->createSpu(strtoupper($resultFields['material_cat_no']));  //不存在生产spu
        }
        $supplier_product_model->startTrans();
        try {
            // 供应商产品信息
            $productData = [
                'lang' => $lang,
                'supplier_id' => $supplier_id,
                'material_cat_no' => $resultFields['material_cat_no'],
                'name' => $resultFields['name'],
                'brand' => $brand,
                'warranty' => $resultFields['warranty'],
                'description' => $data['description'],
                'tech_paras' => $data['tech_paras'],
                'status' =>  self::STATUS_DRAFT,  //默认草稿
                'deleted_flag' => 'N',    // 非删除
                'created_at' => $this->getTime()
            ];
            //是否提交审核
            if(isset($data['check']) && $data['check']=='CHECK'){
                $productData['status'] = self::STATUS_APPROVING;
            }
            if(empty($spu)){
                $product_where['spu'] = $data['spu'];
                $res_product = $supplier_product_model->updateInfo($product_where,$productData);
            } else {
                $productData['spu'] = $spu;
                $res_product = $supplier_product_model->addRecord($productData);
            }
            if (!$res_product) {
                $supplier_product_model->rollback();
            }
            //产品附件
            $product_attachs_model = new SupplierProductAttachModel();
            $del_where['spu'] = empty($spu) ? $data['spu'] : $spu;
            $product_attachs_model->delRecord($del_where);
            $res_pro_attach = $product_attachs_model->uploadattachs($data['product_attachs'], $del_where['spu']);

            if (!$res_pro_attach) {
                $supplier_product_model->rollback();
            }
            //商品信息
            $supplier_goods_model = new SupplierGoodsModel();
            if(isset($data['goods']) && is_array($data['goods'])){
                foreach($data['goods'] as $item){
                    $checkGoodsFields = ['model', 'exw_days', 'min_pack_naked_qty', 'nude_cargo_unit', 'min_pack_unit','min_order_qty'];
                    $resultGoodsFields = $this->_checkFields($item, $checkGoodsFields, 'required'); //校验字段
                    $goodsData = [
                        'spu' => empty($spu) ? $data['spu'] : $spu,
                        'lang' => $lang,
                        'supplier_id' => $supplier_id,
                        'name' => $item['name'] ? $item['name'] : '',
                        'model' => $resultGoodsFields['model'],
                        'exw_days' => $resultGoodsFields['exw_days'],  //出货周期（天）
                        'min_pack_naked_qty' => $resultGoodsFields['min_pack_naked_qty']?$resultGoodsFields['min_pack_naked_qty']:null,  //最小包装内裸货商品数量
                        'nude_cargo_unit' => $resultGoodsFields['nude_cargo_unit'],  //商品裸货单位
                        'min_pack_unit' => $resultGoodsFields['min_pack_unit'],  //最小包装单位
                        'min_order_qty' => $resultGoodsFields['min_order_qty']?$resultGoodsFields['min_order_qty']:null,  //最小订货数量
                        'price' => $resultGoodsFields['price']?$resultGoodsFields['price']:null,  //价格
                        'status' => $item['status'] ? strtoupper($item['status']) : self::STATUS_VALID,  //默认有效状态
                        'deleted_flag' => 'N',    // 非删除
                        'created_at' => $this->getTime()
                    ];
                    $sku='';
                    if(!isset($item['sku']) || empty($item['sku'])){
                        $sku = $supplier_goods_model->setRealSku($goodsData['spu']);;  //不存在生产sku
                    }

                    if(empty($sku)){
                        $sku_where['sku'] = $item['sku'];
                        $res_goods = $supplier_goods_model->updateInfo($sku_where, $goodsData);
                    }else {
                        $goodsData['sku'] = $sku;
                        $res_goods = $supplier_goods_model->addRecord($goodsData);
                    }
                    if (!$res_goods) {
                        $supplier_product_model->rollback();
                    }
                    //商品其他属性

                    if(isset($item['attr']) && !empty($item['attr'])){
                        $supplier_goods_attr_model = new SupplierGoodsAttrModel();
                        $attrData = [
                            'lang' => $lang,
                            'spu' => empty($spu) ? $data['spu'] : $spu,
                            'ex_goods_attrs' => !empty($item['attr']['other_attrs']) ? json_encode($item['attr']['other_attrs'], JSON_UNESCAPED_UNICODE) : null,
                            'other_attrs' => !empty($item['attr']['ex_goods_attrs']) ? json_encode($item['attr']['ex_goods_attrs'], JSON_UNESCAPED_UNICODE) : null,
                            //'status' => self::STATUS_VALID,
                            'deleted_flag' => 'N',
                        ];

                        if(empty($sku)){
                            $checkSku = $supplier_goods_attr_model->field('sku')->where(['sku' => $item['sku']])->find();
                            if(!$checkSku){
                                $attrData['sku'] = $item['sku'];
                                $attrData['created_at'] = $this->getTime();
                                $res_goods_attr = $supplier_goods_attr_model->addRecord($attrData);
                            }else {
                                $sku_attr_where['sku'] = $item['sku'];
                                $attrData['updated_at'] = $this->getTime();
                                $res_goods_attr = $supplier_goods_attr_model->updateInfo($sku_attr_where, $attrData);
                            }
                        }else {
                            $attrData['sku'] = $sku;
                            $attrData['created_at'] = $this->getTime();
                            $res_goods_attr = $supplier_goods_attr_model->addRecord($attrData);
                        }
                        if (!$res_goods_attr) {
                            $supplier_product_model->rollback();
                        }
                    }else {
                        $res_goods_attr = true;
                    }
                }
            } else{
                $res_goods = true;
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
                $this->setMessage(MSG::MSG_FAILED);
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
    public function aeditSupplierProductAction(){
        $data = $this->getPut();
        $lang = $this->getLang($data['lang']);
        $supplier_id = $this->getSupplierId($data['supplier_id']);
        $supplier_product_model = new SupplierProductModel();
        // 校验字段
        $checkFields = ['spu','material_cat_no', 'name', 'brand', 'warranty', 'product_attachs'];
        $resultFields = $this->_checkFields($data, $checkFields, 'required'); //校验字段
        $name = $this->checkName($resultFields['name'], $lang, 'name');  //产品名称校验
        if(!$name){
            jsonReturn('', -1, '产品名称已存在!');
        }
        $brand = $this->editBrand($resultFields['brand'],$lang);  //品牌校验,目前只校验spu的name
        $spu = $resultFields['spu'];
        $supplier_product_model->startTrans();
        try {
            // 供应商产品信息
            $productData = [
                'supplier_id' => $supplier_id,
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
            $product_where['spu'] = $spu;
            $res_product = $supplier_product_model->updateInfo($product_where, $productData);
            if (!$res_product) {
                $supplier_product_model->rollback();
            }
            //产品附件
            $product_attachs_model = new SupplierProductAttachModel();
            $del_where['spu'] = $spu;
            $product_attachs_model->delRecord($del_where);
            $res_pro_attach = $product_attachs_model->uploadattachs($resultFields['product_attachs'],$spu);
            if (!$res_pro_attach) {
                $supplier_product_model->rollback();
            }
            //商品信息
            $supplier_goods_model = new SupplierGoodsModel();
            if(isset($data['goods']) && is_array($data['goods'])) {
                foreach($data['goods'] as $item){
                    $checkGoodsFields = ['model', 'exw_days', 'min_pack_naked_qty', 'nude_cargo_unit', 'min_pack_unit','min_order_qty'];
                    $resultGoodsFields = $this->_checkFields($item, $checkGoodsFields, 'required'); //校验字段
                    $goodsData = [
                        'spu' => $spu,
                        'lang' => $lang,
                        'supplier_id' => $supplier_id,
                        'name' => $item['name'] ? $item['name'] : '',
                        'model' => $resultGoodsFields['model'],
                        'exw_days' => $resultGoodsFields['exw_days'],  //出货周期（天）
                        'min_pack_naked_qty' => $resultGoodsFields['exw_days'],  //最小包装内裸货商品数量
                        'nude_cargo_unit' => $resultGoodsFields['nude_cargo_unit'],  //商品裸货单位
                        'min_pack_unit' => $resultGoodsFields['min_pack_unit'],  //最小包装单位
                        'min_order_qty' => $resultGoodsFields['min_order_qty'],  //最小订货数量
                        'price' => $item['price'],  //价格
                        'status' => $item['status'] ? strtoupper($item['status']) : self::STATUS_VALID,  //默认有效状态
                        'deleted_flag' => 'N',    // 非删除
                        'created_at' => $this->getTime()
                    ];
                    if(isset($item['sku']) && !empty($item['sku'])){
                        $sku_where['sku'] = $item['sku'];
                        $res_goods = $supplier_goods_model->updateInfo($sku_where, $goodsData);
                    }else {
                        $goodsData['sku'] = $supplier_goods_model->setRealSku($spu);
                        $res_goods = $supplier_goods_model->addRecord($goodsData);
                    }
                    if (!$res_goods) {
                        $supplier_product_model->rollback();
                    }
                    //商品其他属性
                    if(isset($item['attr']) && !empty($item['attr'])){
                        $supplier_goods_attr_model = new SupplierGoodsAttrModel();
                        $attrData = [
                            'lang' => $lang,
                            'spu' => $spu,
                            'other_attrs' => !empty($attr['attr']['other_attrs']) ? json_encode($attr['attr']['other_attrs'], JSON_UNESCAPED_UNICODE) : null,
                            'ex_goods_attrs' => !empty($attr['attr']['ex_goods_attrs']) ? json_encode($attr['attr']['ex_goods_attrs'], JSON_UNESCAPED_UNICODE) : null,
                            'status' => self::STATUS_VALID,
                            'deleted_flag' => 'N',
                        ];
                        if(isset($item['sku']) && !empty($item['sku'])){
                            $sku_where['sku'] = $item['sku'];
                            $res_goods_attr = $supplier_goods_attr_model->updateInfo($sku_where, $goodsData);
                        }else {
                            $goodsData['sku'] = $supplier_goods_model->setRealSku($spu);
                            $res_goods_attr = $supplier_goods_attr_model->addRecord($goodsData);
                        }
                        if (!$res_goods_attr) {
                            $supplier_product_model->rollback();
                        }
                    }
                }
            }else{
                $res_goods = true;
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
     * 瑞商产品审核
     */
    public function checkSupplierProductListAction(){
        $condition = $this->getPut();
        $condition['lang'] = $this->getLang($condition['lang']);
        if(!isset($condition['spu']) || empty($condition['spu'])){
            jsonReturn('',-1,'缺少产品参数');
        }
        $supplier_product_model = new SupplierProductModel();
        if(is_array($condition['spu'])){
            foreach($condition['spu'] as $item){
                $res = $supplier_product_model->field('status')->where(['spu'=>$item['spu']])->find();
                if($res && $res['status']==self::STATUS_DRAFT){
                    $product_where['spu'] = $item['spu'];
                    $productData['status'] = self::STATUS_APPROVING;
                    $res_product = $supplier_product_model->updateInfo($product_where,$productData);
                    if(!$res_product){
                        jsonReturn('',-1,'提交失败,请稍后再试!');
                    }
                }
            }
        }else{
            $res = $supplier_product_model->field('status')->where(['spu'=>$condition['spu']])->find();
            if($res && $res['status']==self::STATUS_DRAFT){
                $product_where['spu'] = $condition['spu'];
                $productData['status'] = self::STATUS_APPROVING;
                $res_product = $supplier_product_model->updateInfo($product_where,$productData);
                if(!$res_product){
                    jsonReturn('',-1,'提交失败,请稍后再试!');
                }
            }else{
                jsonReturn('',-1,'仅可状态为草稿时提交审核!');
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn();
    }

    /**
     * 瑞商产品--删除
     */
    public function delSupplierProductInfoAction(){
        $condition = $this->getPut();
        $condition['lang'] = $this->getLang($condition['lang']);
        if(!isset($condition['spu']) || empty($condition['spu'])){
            jsonReturn('',-1,'缺少产品参数');
        }
        $supplier_product_model = new SupplierProductModel();
        $supplier_product_attach_model = new SupplierProductAttachModel();
        $supplier_goods_model = new SupplierGoodsModel();
        $supplier_goods_attr_model = new SupplierGoodsAttrModel();
        $supplier_product_model->startTrans();
        try{
            $res = $supplier_product_model->deleteInfo($condition);
            if(!$res){
                $supplier_product_model->rollback();
            }
            $res_attach = $supplier_product_attach_model->deleteInfo($condition);
            if(!$res_attach){
                $supplier_product_model->rollback();
            }
            $res_good = $supplier_goods_model->deleteInfo($condition);
            if(!$res_good){
                $supplier_goods_model->rollback();
            }
            $res_attr = $supplier_goods_attr_model->deleteInfo($condition);
            if(!$res_attr){
                $supplier_goods_model->rollback();
            }
            if($res && $res_attach && $res_good && $res_attr){
                $supplier_product_model->commit();
                $this->setCode(MSG::MSG_SUCCESS);
            }else{
                $this->setCode(MSG::MSG_FAILED);
            }
            $this->jsonReturn();
        }catch (Exception $e) {
            $supplier_product_model->rollback();
            $res = false;
            $this->jsonReturn($res);
        }
    }

    /**
     * 瑞商商品--删除
     */
    public function delSupplierGoodsInfoAction(){
        $condition = $this->getPut();
        $condition['lang'] = $this->getLang($condition['lang']);
        if(!isset($condition['sku']) || empty($condition['sku'])){
            jsonReturn('',-1,'缺少商品参数');
        }
        $supplier_goods_model = new SupplierGoodsModel();
        $supplier_goods_attr_model = new SupplierGoodsAttrModel();
        $supplier_goods_model->startTrans();
        try{
            $res = $supplier_goods_model->deleteInfo($condition);
            if(!$res){
                $supplier_goods_model->rollback();
            }
            $res_attr = $supplier_goods_attr_model->deleteInfo($condition);
            if(!$res_attr){
                $supplier_goods_model->rollback();
            }
            if($res && $res_attr){
                $supplier_goods_model->commit();
                $this->setCode(MSG::MSG_SUCCESS);
            }else{
                $this->setCode(MSG::MSG_FAILED);
            }
            $this->jsonReturn();
        }catch (Exception $e) {
            $supplier_goods_model->rollback();
            $res = false;
            $this->jsonReturn($res);
        }
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
        $brand_model = new BrandModel();
        if (is_numeric($brand)) {
            $brand_where=[
                'status'=>'VALID',
                'deleted_flag'=>'N'
            ];
            $brand_where['brand'] = ['like',"%\"name\":\"".$brand."\"%"];
            $res_brand = $brand_model->field('id,brand')->where($brand_where)->find();
            if($res_brand){
                $data['brand'] = $res_brand['brand'];
            }else {
                $brand_ary = array(
                    'name' => $brand,
                    'lang' => $lang,
                    'style' => 'TEXT',
                    'label' => $brand,
                    'logo' => '',
                );
                ksort($brand_ary);
                $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                $data['created_at'] = $this->getTime();
                $data['deleted_flag'] = 'N';
                $brand_model->addRecord($data);
            }
            /*$data['brand'] = '';
            $brandInfo = $brand_model->info($brand);
            if ($brandInfo) {
                $brandAry = json_decode($brandInfo['brand'], true);
                foreach ($brandAry as $r) {
                    if ($r['lang'] == $lang) {
                        $brand_ary = array(
                            'name' => $r['name'],
                            'lang' => $lang,
                            'style' => isset($r['style']) ? $r['style'] : 'TEXT',
                            'label' => isset($r['label']) ? $r['label'] : $r['name'],
                            'logo' => isset($r['logo']) ? $r['logo'] : '',
                        );
                        ksort($brand_ary);
                        $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                    }
                }
            }else{
                $brand_ary = array(
                    'name' => $brand,
                    'lang' => $lang,
                    'style' => 'TEXT',
                    'label' => $brand,
                    'logo' => '',
                );
                ksort($brand_ary);
                $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                $data['created_at'] = $this->getTime();
                $data['deleted_flag'] = 'N';
                $brand_model->addRecord($data);

            }*/
        } else {
            if (is_array($brand)) {
                ksort($brand);
                $data['brand'] = json_encode($brand, JSON_UNESCAPED_UNICODE);
            } elseif (!empty($brand)) {
                $brand_where=[
                    'status'=>'VALID',
                    'deleted_flag'=>'N'
                ];
                $brand_where['brand'] = ['like',"%\"name\":\"".$brand."\"%"];
                $res_brand = $brand_model->field('id,brand')->where($brand_where)->find();
                if($res_brand){
                    $data['brand'] = $res_brand['brand'];
                }else {
                    $brand_ary = array(
                        'name' => $brand,
                        'lang' => $lang,
                        'style' => 'TEXT',
                        'label' => $brand,
                        'logo' => '',
                    );
                    ksort($brand_ary);
                    $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                    $data['created_at'] = $this->getTime();
                    $data['deleted_flag'] = 'N';
                    $brand_model->addRecord($data);
                }
            } else {
                $data['brand'] = '';
            }
        }

        return $data['brand'];
    }


    //校验必填字段
    private function _checkFields($param, $field, $name='required'){
        if (empty($param) || empty($field)) {
            jsonReturn('', -1,'缺少参数!');
        }
        foreach ($field as $k => $item) {
            if($name == 'required'){
                if ($param[$item] == '' || empty($param[$item])) {

                    jsonReturn('', -1, $item.'缺少参数!');
                }
            }
            //继续添加...
            $param[$item] = trim($param[$item]);
            continue;
        }
        return $param;
    }

    public function getLang($lang='') {
        $lang = (isset($lang)&&!empty($lang)) ? $lang : 'zh';
        return $lang;
    }

    public function getSupplierId($id) {
        return $id ? $id : ($this->supplier_user['supplier_id']?$this->supplier_user['supplier_id']:SUID);
    }

    public function getTime() {
        return $time = date('Y-m-d H:i:s',time());
    }

}