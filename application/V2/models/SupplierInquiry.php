<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SupplierInquiry
 * @author  zhongyg
 * @date    2017-11-7 13:52:20
 * @version V2.0
 * @desc
 */
class SupplierInquiryModel extends PublicModel {

    //put your code here
    protected $tableName = 'supplier';
    protected $dbName = 'erui_supplier'; //数据库名称
    protected $areas = ['Middle East', 'South America', 'North America', 'Africa', 'Pan Russian', 'Asia-Pacific', 'Europe'];

    public function __construct() {

        parent::__construct();
    }

    private function _getCondition($condition, &$where) {
        $this->_getValue($where, $condition, 'supplier_no', 'string', 'supplier_no');
        $this->_getValue($where, $condition, 'supplier_name', 'like', 'name');
        // $this->_getValue($where, $condition, 'created_at', 'between', 'i.created_at');
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getList($condition) {
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
        $this->_getCondition($condition, $where);
        list($offset, $length) = $this->_getPage($condition);
        $created_at_start = !empty($condition['created_at_start']) ? $condition['created_at_start'] : null;
        $created_at_end = !empty($condition['created_at_end']) ? $condition['created_at_end'] : null;
        $data = $this
                ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->limit($offset, $length)
                ->select();
        $ret = [];

        foreach ($data as $item) {
            $this->getAreaCountBySupplierId($item['supplier_id'], $created_at_start, $created_at_end, $item);
            $ret[] = $item;
        }
        return $ret;
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getAreaCountBySupplierId($supplier_id, $created_at_start, $created_at_end, &$item) {

        $inquiry_ids = $this->getInquiryIdsSupplierId($supplier_id);
        $item['total'] = 0;
        foreach ($this->areas as $area) {
            $item[$area] = 0;
        }

        if (empty($inquiry_ids)) {
            return null;
        }

        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'area_bn' => ['in', $this->areas],
            'id' => ['in', $inquiry_ids]
        ];
        if ($created_at_start && $created_at_end) {
            $where['created_at'] = ['between', $created_at_start . ',' . $created_at_end];
        } elseif ($created_at_start) {
            $where['created_at'] = ['egt', $created_at_start];
        } elseif ($created_at_end) {
            $where['created_at'] = ['elt', $created_at_end];
        }
        $inquiry_model = new InquiryModel();

        $areacounts = $inquiry_model
                ->field('count(\'id\') as area_count,area_bn')
                ->where($where)
                ->group('area_bn')
                ->select();

        foreach ($areacounts as $areacount) {
            $item[$areacount['area_bn']] = $areacount['area_count'];
            $item['total'] += $areacount['area_count'];
        }
    }

    /**
     * 获取供应商询单ID
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function getInquiryIdsSupplierId($supplier_id) {
        $final_quote_item_model = new FinalQuoteItemModel();
        $where = ['supplier_id' => $supplier_id,
            'deleted_flag' => 'N',
            'status' => 'VALID',
        ];
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where($where)->group('inquiry_id')->select();
        $inquiry_ids = [];
        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        return $inquiry_ids;
    }

    /**
     * 供应商数量
     * @return mix
     * @author zyg
     */
    public function getSupplierCount() {
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
        $count = $this
                // ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 询单数量
     * @return mix
     * @author zyg
     */
    public function getInquiryCount($supplier_id = null) {

        $final_quote_item_model = new FinalQuoteItemModel();
        $final_where = ['supplier_id' => ['gt', 0],
            'deleted_flag' => 'N',
            'status' => 'VALID',
        ];
        if ($supplier_id) {
            $final_where['supplier_id'] = $supplier_id;
        }
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where(['supplier_id' => ['gt', 0],
                            'deleted_flag' => 'N',
                            'status' => 'VALID',
                        ])->group('inquiry_id')->select();
        $inquiry_ids = [];

        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        if (empty($inquiry_ids)) {
            return 0;
        }
        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'id' => ['in', $inquiry_ids],
            'area_bn' => ['in', $this->areas]
        ];
        $inquiry_model = new InquiryModel();
        $count = $inquiry_model
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /**
     * 询单列表
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function getInquirysBySupplierId($supplier_id, $condition) {
        list($offset, $length) = $this->_getPage($condition);
        $final_quote_item_model = new FinalQuoteItemModel();
        $inquiryids = $final_quote_item_model->field('inquiry_id')
                        ->where(['supplier_id' => $supplier_id,
                            'deleted_flag' => 'N',
                            'status' => 'VALID',
                        ])->group('inquiry_id')->select();
        $inquiry_ids = [];
        foreach ($inquiryids as $inquiryid) {
            $inquiry_ids[] = $inquiryid['inquiry_id'];
        }
        if (empty($inquiry_ids)) {
            return null;
        }
        $where = [
            'deleted_flag' => 'N',
            'status' => 'QUOTE_SENT',
            'quote_status' => 'COMPLETED',
            'id' => ['in', $inquiry_ids],
            'area_bn' => ['in', $this->areas]
        ];
        $inquiry_model = new InquiryModel();
        $list = $inquiry_model
                ->field('id as inquiry_id,inquiry_no,serial_no,created_at')
                ->where($where)
                ->order('created_at ASC')
                ->limit($offset, $length)
                ->select();
        return $list;
    }

    /**
     * 询单明细
     * @param int $supplier_id
     * @return mix
     * @author zyg
     */
    public function Info($supplier_id) {


        $info = $this
                ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where(['id' => $supplier_id])
                ->find();

        return $info;
    }

}
