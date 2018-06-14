<?php

/**
 * 供应商(瑞商)产品相关接口
 * Class SupplierproductController
 * @author stilly <742163033#qq.com>
 */

class SupplierproductController extends PublicController
{

    public function init()
    {
        parent::init();
    }

    /**
     * 供应商(瑞商)产品
     */
    public function listAction()
    {
        $request = $this->validateRequestParams();

        $data = (new SupplierProductModel)->getList($request);

        foreach ($data as &$datum) {
            $datum['material_cat_name'] = $this->setMaterialCatFor($datum['material_cat_no']);
            $datum['brand'] = $this->setBrand($datum['brand']);

            if ($datum['status'] == 'INVALID') {
                $datum['invalid_list'] = (new SupplierProductCheckLogModel)->getList($datum);
            }

        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'total' => (new SupplierProductModel)->getCount($request),
            'data' => $data
        ]);
    }

    /**
     * 供应商(瑞商)产品详情
     */
    public function detailAction()
    {
        $request = $this->validateRequestParams('id');

        $detail = (new SupplierProductModel)->getDetail($request['id']);
        $detail['material_cat_name'] = $this->setMaterialCatFor($detail['material_cat_no']);
        $detail['brand'] = $this->setBrand($detail['brand']);
        $detail['description'] = htmlspecialchars_decode($detail['description']);
        $detail['tech_paras'] =  htmlspecialchars_decode($detail['tech_paras']);

        //attachs
        $detail['attach_list'] = (new SupplierProductAttachModel)->getList($detail);

        //goods
        $detail['goods_list'] = (new SupplierGoodsModel)->getList($detail);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $detail
        ]);

    }

    /**
     * 供应商(瑞商)产品审核
     */
    public function reviewAction()
    {
        $request = $this->validateRequestParams('id');


        $reviewList = explode(',' ,$request['id']);

        foreach ($reviewList as $item) {

            $isApproving = (new SupplierProductModel)->checkIsApproving($item);

            if ($isApproving !== 'APPROVING') {
                $this->jsonReturn([
                    'code' => 0,
                    'message' => '只可以批量通过审核中状态的产品'
                ]);
            }

            //TODO 这里可以用事务来优化当前逻辑

            (new SupplierProductModel)->updateStatusFor($item, 'APPROVED', $this->user['id']);

            //记录审核日志
            (new SupplierProductCheckLogModel)->createReviewLogFor($item, $this->user['id']);

            //同步到正式产品/商品库
            $this->syncToRegular($item);

        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功'
        ]);

    }

    public function syncToRegular($item)
    {
        //Products
        $supplierProduct = (new SupplierProductModel)->where(['id' => $item])->find();
        //p($supplierProduct);
        $supplierProductAttach = (new SupplierProductAttachModel)->where(['spu' => $supplierProduct['spu'], 'deleted_flag' => 'N'])->select();

        //Goods
        $supplierGoods = (new SupplierGoodsModel)->where(['spu' => $supplierProduct['spu'], 'deleted_flag' => 'N'])->select();
        $supplierGoodsAttr = ( new SupplierGoodsAttrModel)->where(['spu' => $supplierProduct['spu'], 'deleted_flag' => 'N'])->select();

        //Sync product
        $regularProduct = new ProductModel();
        $regularProduct->startTrans();

        $productData = [
            'lang' => $supplierProduct['lang'],
            'material_cat_no' => $supplierProduct['material_cat_no'],
            'spu' => $supplierProduct['spu'],
            'name' => $supplierProduct['name'],
            'show_name' => $supplierProduct['show_name'],
            'brand' => $supplierProduct['brand'],
            'keywords' => $supplierProduct['keywords'],
            'tech_paras' => strip_tags(htmlspecialchars_decode($supplierProduct['tech_paras'])),
            'description' => strip_tags(htmlspecialchars_decode($supplierProduct['description'])),
            'warranty' => $supplierProduct['warranty'],
            'status' => 'VALID',
            'created_by' => $this->user['id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s'),
            'checked_by' => $this->user['id'],
            'checked_at' => date('Y-m-d H:i:s')
        ];

        $syncProduct = $regularProduct->add($regularProduct->create($productData));
        //更新Es

        //Product supllier
        $productSupplier = new ProductSupplierModel();
        $productSupplier->startTrans();
        $syncProductSupplier = $productSupplier->add($productSupplier->create([
            'spu' => $supplierProduct['spu'],
            'supplier_id' => $supplierProduct['supplier_id'],
            'brand' => $supplierProduct['brand'],
            'status' => 'VALID',
            'created_by' => $this->user['id'],
            'created_at' => date('Y-m-d H:i:s'),
            'checked_by' => $this->user['id'],
            'checked_at' => date('Y-m-d H:i:s')
        ]));


        if ($syncProduct && $syncProductSupplier) {
            $regularProduct->commit();
            $productSupplier->commit();

            //product attach
            $productAttach = new ProductAttachModel();
            foreach ($supplierProductAttach as $attach) {
                $productAttach->add($productAttach->create([
                    'spu' => $attach['spu'],
                    'attach_type' => $attach['attach_type'],
                    'attach_name' => $attach['attach_name'],
                    'attach_url' => $attach['attach_url'],
                    'default_flag' => $attach['default_flag'],
                    'sort_order' => $attach['sort_order'],
                    'created_by' => $this->user['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'checked_by' => $this->user['id'],
                    'checked_at' => date('Y-m-d H:i:s')
                ]));
            }

            //更新Es
            (new EsProductModel)->create_data($productData, 'zh');

            //Sync goods
            $goods = new GoodsModel();
            $goodsSupplier = new GoodsSupplierModel();

            foreach ($supplierGoods as $good) {
                $goodData = [
                    'lang' => $good['lang'],
                    'spu' => $good['spu'],
                    'sku' => $good['sku'],
                    'name' => $good['name'],
                    'show_name' => $good['show_name'],
                    'model' => $good['model'],
                    'description' => $good['description'],
                    'exw_days' => $good['exw_days'],
                    'min_pack_naked_qty' => $good['min_pack_naked_qty'],
                    'nude_cargo_unit' => $good['nude_cargo_unit'],
                    'min_pack_unit' => $good['min_pack_unit'],
                    'min_order_qty' => $good['min_order_qty'],
                    'purchase_price' => $good['price'],
                    'purchase_price_cur_bn' => $good['price_cur_bn'],
                    'source' => $good['source'],
                    'status' => 'VALID',
                    'created_by' => $this->user['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'checked_by' => $this->user['id'],
                    'checked_at' => date('Y-m-d H:i:s')
                ];
                $goods->add($goods->create($goodData));

                (new EsGoodsModel)->create_data($goodData, 'zh');

                //Sync goods Supplier
                $goodsSupplier->add($goodsSupplier->create([
                    'spu' => $good['spu'],
                    'sku' => $good['sku'],
                    'supplier_id' => $good['supplier_id'],
                    'status' => 'VALID',
                    'created_by' => $this->user['id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'checked_by' => $this->user['id'],
                    'checked_at' => date('Y-m-d H:i:s')
                ]));
            }

            //Sync supplier goods attr
            $goodsAttr = new GoodsAttrModel();
            foreach ($supplierGoodsAttr as $supplierGoodAttr) {
                $goodsAttr->add($goodsAttr->create([
                    'lang' => $supplierGoodAttr['lang'],
                    'spu' => $supplierGoodAttr['spu'],
                    'sku' => $supplierGoodAttr['sku'],
                    'ex_goods_attrs' => $supplierGoodAttr['ex_goods_attrs'],
                    'other_attrs' => $supplierGoodAttr['other_attrs'],
                    'created_by' => $this->user['id'],
                    'created_at' => date('Y-m-d H:i:s')
                ]));
            }

        }else{
            $regularProduct->rollback();
            $productSupplier->rollback();
        }
    }

    /**
     * 供应商(瑞商)产品驳回
     */
    public function rejectAction()
    {
        $request = $this->validateRequestParams('id,remarks');


        $reviewList = explode(',' ,$request['id']);

        foreach ($reviewList as $item) {

            $isApproving = (new SupplierProductModel)->checkIsApproving($item);

            if ($isApproving !== 'APPROVING') {
                $this->jsonReturn([
                    'code' => 0,
                    'message' => '只可以批量驳回审核中状态的产品'
                ]);
            }

            (new SupplierProductModel)->updateStatusFor($item, 'INVALID', $this->user['id']);

            (new SupplierProductCheckLogModel)->createReviewLogFor($item, $this->user['id'], $request);

        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功'
        ]);
    }

    /**
     * 设置物料分类名称
     * @param $materialNo 物料分类编码
     * @return string 物料分类名称
     */
    protected function setMaterialCatFor($materialNo)
    {
        $materialCat = (new MaterialCatModel)->where(['deleted_flag' => 'N', 'lang' => 'zh', 'cat_no' => $materialNo])->find();

        if ($materialCat['level_no']) {
            $parentCat = (new MaterialCatModel)->where(['deleted_flag' => 'N', 'lang' => 'zh', 'cat_no' => $materialCat['parent_cat_no']])->find();
            if ($parentCat['level_no']) {
                $motherCat = (new MaterialCatModel)->where(['deleted_flag' => 'N', 'lang' => 'zh', 'cat_no' => $parentCat['parent_cat_no']])->find();
            }
        }

        $materialCatName = '';
        if ($materialCat['level_no'] == 3 ) {
            $materialCatName = $motherCat['name'].'/'.$parentCat['name'].'/'.$materialCat['name'];
        }elseif ($materialCat['level_no'] == 2) {
            $materialCatName = $motherCat['name'].'/'.$parentCat['name'];
        }

        return $materialCatName;
    }

    protected function setBrand($brandObj)
    {
        $data = json_decode($brandObj, true);

        if (count($data)) {
            foreach ($data as $datum) {
                if ($datum['lang'] =='zh') {
                    return $datum['name'];
                }
            }
        }

        return $data['name'];
    }
}