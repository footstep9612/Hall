<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductSupplier
 * @author  zhongyg
 * @date    2017-11-5 15:45:00
 * @version V2.0
 * @desc
 */
class ProductSupplierController extends PublicController {

    public function init() {
        parent::init();
    }

    /**
     * Description SPU统计
     * @author  zhongyg
     * @date    2017-11-5 15:45:00
     * @version V2.0
     * @desc
     */
    public function listAction() {
        $condition = $this->getPut();
        $productsupplier_model = new ProductSupplierModel();
        $data = $productsupplier_model->getList($condition);
        if ($data) {
            $this->setvalue('count', $productsupplier_model->getCount($condition));
            $this->setvalue('product_count', $productsupplier_model->getproductCount());
            $this->setvalue('supplier_count', $productsupplier_model->getSupplierCount());
            foreach ($data as $key => $item) {
                $productsupplier_model->getInquiryCountAndAvgPriceBySpu($item['spu'], $item);
                $data[$key] = $item;
            }

            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

    /**
     * Description SPU报价统计
     * @author  zhongyg
     * @date    2017-11-5 15:45:00
     * @version V2.0
     * @desc
     */
    public function listProductAndQuoteAction() {
        $condition = $this->getPut();
        $country_bn = $this->getPut('country_bn');
        $productsupplier_model = new ProductSupplierModel();
        $data = $productsupplier_model->getSpuandInquiryCountList($country_bn, $condition);
        if ($data) {
            $this->setvalue('product_count', $productsupplier_model->getproductCount($country_bn));
            $this->setvalue('count', $productsupplier_model->getInquiryCountBySpuCount($country_bn));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

    /**
     * Description 产品查询
     * @author  zhongyg
     * @date    2017-11-5 15:45:00
     * @version V2.0
     * @desc
     */
    public function listProductByCountyAction() {
        $condition = $this->getPut();
        $productsupplier_model = new ProductSupplierModel();
        $data = $productsupplier_model->getList($condition);
        if ($data) {
            $this->setvalue('count', $productsupplier_model->getCount($condition));
            $this->setvalue('product_count', $productsupplier_model->getproductCount());
            $this->setvalue('supplier_count', $productsupplier_model->getSupplierCount());
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

    /**
     * Description 获取产品供应商列表详情
     * @author  zhongyg
     * @date    2017-11-5 15:45:00
     * @version V2.0
     * @desc
     */
    public function infoAction() {
        $spu = $this->getPut('spu');
        if (empty($spu)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('SPU编码不能为空!');
            $this->jsonReturn(null);
        }
        $product_model = new ProductModel();
        $product_zh = $product_model->field('name')->where(['spu' => $spu, 'lang' => 'zh'])->find();
        $data['product_name_zh'] = isset($product_zh['name']) ? $product_zh['name'] : '';
        $product_en = $product_model->field('name')->where(['spu' => $spu, 'lang' => 'en'])->find();
        $data['product_name_en'] = isset($product_en['name']) ? $product_en['name'] : '';
        $data['spu'] = $spu;
        $productsupplier_model = new ProductSupplierModel();
        $supplier_ids = $productsupplier_model->getsupplieridsbyspu($spu);


        if ($supplier_ids) {
            $data_supplier = null;
            $supplier_model = new SupplierModel();
            $suppliers = $supplier_model->field('id,name')
                            ->where(['id' => ['in', $supplier_ids], 'status' => ['in', ['VALID', 'APPROVED', 'DRAFT', 'APPROVING']], 'deleted_flag' => 'N'])->select();


            $supplier_contact_model = new SupplierContactModel();
            $contacts = $supplier_contact_model
                            ->field('supplier_id,first_name,last_name,title,phone,email,station,contact_name')
                            ->where(['supplier_id' => ['in', $supplier_ids]])->select();

            foreach ($suppliers as $supplier) {
                $data_supplier[$supplier['id']]['name'] = $supplier['name'];
            }

            foreach ($contacts as $contact) {
                $data_supplier[$contact['supplier_id']]['contact'][] = $contact;
            }
            rsort($data_supplier);
            $this->setvalue('supplier', $data_supplier);
        } else {
            $this->setvalue('supplier', null);
        }
        if (!empty($data)) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

    /**
     * Description 产品查询
     * @author  zhongyg
     * @date    2017-11-5 15:45:00
     * @version V2.0
     * @desc
     */
    public function productListAction() {
        $condition = $this->getPut();

        $esproduct = new EsProductModel();
        $pagesize = 10;
        $current_no = 1;
        if (isset($condition['current_no'])) {
            $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
        }
        if (isset($condition['pagesize'])) {
            $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        $from = ($current_no - 1) * $pagesize;
        $total = 0;
        $condition['status'] = 'ALL';
        $condition['onshelf_flag'] = 'A';
        $data = $esproduct->getList($condition, ['spu', 'supplier_count', 'name_loc', 'name'], 'zh', $from, $pagesize, $total);
        if ($data) {
            $this->setvalue('count', $total);

            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

}
