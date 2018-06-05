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
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'total' => (new SupplierProductModel)->getCount($request),
            'data' => $data
        ]);
    }

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
}