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
            'all' => (new SupplierProductModel)->getCount(['status' => ['neq', 'DRAFT']]),
            'approving' => (new SupplierProductModel)->getCount(['status' => 'APPROVING']),
            'approved' => (new SupplierProductModel)->getCount(['status' => 'APPROVED']),
            'invalid' => (new SupplierProductModel)->getCount(['status' => 'INVALID']),
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

        foreach ($detail['goods_list'] as &$good) {
            $attrs = (new SupplierGoodsAttrModel)->getAttr($good);
            if ($attrs) {
                $good = array_merge($good, $attrs);
            }
        }

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

        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功'
        ]);

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