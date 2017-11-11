<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProductSupplier
 * @author  zhongyg
 * @date    2017-8-4 11:37:17
 * @version V2.0
 * @desc
 */
class ProductSupplierModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'product_supplier'; //数据表表名

//put your code here

    public function __construct() {
        parent::__construct();
    }

    /* 通过SPU供应商列表
     * @param mix $condition // 搜索条件
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    private function _getCondition(&$where, $condition) {
        $this->_getValue($where, $condition, 'spu', 'string', 'pzh.spu');
        if (!empty($condition['product_name'])) {
            $product_name = trim($condition['product_name']);
            $where[] = 'pzh.name like \'%' . $product_name . '%\' or pen.name like \'%' . $product_name . '%\'';
        }
        $where['pzh.deleted_flag'] = 'N';
        $where['pzh.lang'] = 'zh';

        //$where['s.deleted_flag'] = 'N';
        // $where['ps.deleted_flag'] = 'N';
        $where['pzh.status'] = ['in', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'DRAFT', 'INVALID']];

        //  $where['s.status'] = ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']];

        $this->_getValue($where, $condition, 'created_at', 'between', 'pzh.created_at');
        //$this->_getValue($where, $condition, 'supplier_name', 'like', 's.name');
    }

    /* 通过SPU供应商列表
     * @param mix $condition // 搜索条件
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getList($condition) {
        $where = [];
        $this->_getCondition($where, $condition);
        $product_model = new ProductModel();
        //$supplier_model = new SupplierModel();
        $product_table = $product_model->getTableName();
        // $supplier_table = $supplier_model->getTableName();
        list($starrow, $pagesize) = $this->_getPage($condition);
        return $product_model->alias('pzh')
                        ->field('pzh.name as product_name_zh'
                                . ',pen.name as product_name_en,pzh.spu,pzh.view_count')
                        ->where($where)
                        ->join($product_table . ' as pen on pen.spu=pzh.spu and pen.lang=\'en\''
                                . ' and pen.deleted_flag=\'N\''
                                . ' and  pen.`status` in(\'NORMAL\', \'VALID\', \'TEST\', \'CHECKING\', \'DRAFT\', \'INVALID\')'
                                , 'left')
                        // ->join($supplier_table . ' as s on s.id=ps.supplier_id', 'left')
                        ->limit($starrow, $pagesize)
                        ->select();
    }

    /* 通过SPU供应商数量
     * @param mix $condition // 搜索条件
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getCount($condition) {
        $where = [];
        $this->_getCondition($where, $condition);
        $product_model = new ProductModel();
        // $supplier_model = new SupplierModel();
        $product_table = $product_model->getTableName();
        //   $supplier_table = $supplier_model->getTableName();
        $count = $product_model->alias('pzh')
                ->where($where)
                ->join($product_table . ' as pen on pen.spu=pzh.spu and pen.lang=\'en\''
                        . ' and pen.deleted_flag=\'N\''
                        . ' and  pen.`status` in(\'NORMAL\', \'VALID\', \'TEST\', \'CHECKING\', \'DRAFT\', \'INVALID\')'
                        , 'left')
                ->count('pzh.id');

        return $count;
    }

    /* 获取供应商数量
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getSupplierCount() {
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['APPROVED', 'VALID', 'DRAFT', 'APPLING']]
        ];
        $supplier_model = new SupplierModel();
        $count = $supplier_model
// ->field('supplier_no,name as supplier_name,id as supplier_id')
                ->where($where)
                ->count();
        return $count > 0 ? $count : 0;
    }

    /* 获取产品数量
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getproductCount() {
        $where = [
            'deleted_flag' => 'N',
            'status' => ['in', ['NORMAL', 'VALID', 'TEST', 'CHECKING', 'DRAFT', 'INVALID']],
            'lang' => 'zh'
        ];
        $product_model = new ProductModel();
        $data = $product_model
                ->field('spu')
                ->where($where)
                //  ->group('spu')
                ->count();

        return $data > 0 ? $data : 0;
    }

    /* 通过SPU获取供应商信息
     * @param mix $SPUs // 商品SPU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getsuppliersbyspus($spus, $lang = 'en') {
        try {
            if (!$spus) {
                return [];
            }
            $product_attrs = $this->alias('ps')
                    ->field('ps.spu,ps.supplier_id,ps.view_count,'
                            . '(select name from  erui_supplier.supplier where id=ps.supplier_id ) as supplier_name')
                    ->where(['ps.spu' => ['in', $spus],
                        'ps.status' => 'VALID',
                        'ps.deleted_flag' => 'N'
                    ])
//   ->group('ps.supplier_id,ps.spu')
                    ->select();


            if (!$product_attrs) {
                return [];
            }
            $ret = [];
            foreach ($product_attrs as $item) {
                $spu = $item['spu'];
                unset($item['spu']);
                $ret[$spu][] = ['supplier_id' => $item['supplier_id'],
                    'supplier_name' => $item['supplier_name'],
                    'view_count' => $item['view_count'],];
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * spu价格策略新增/编辑 -- 公共
     * @author klp
     * @return array
     */
    public function editSupplier($input, $spu = '', $admin = '') {
        if (empty($input) || empty($spu) || empty($spu)) {
            return false;
        }

        $this->where(['spu' => $spu])->save(['deleted_flag' => 'Y', 'status' => 'DELETED']);
        $results = array();
        try {
            foreach ($input as $value) {
                $data = $this->checkParam($value, $spu);
                $data['deleted_flag'] = 'N';
                $data['spu'] = $spu;
                $data['status'] = 'VALID';
                $data['supplier_id'] = $data['supplier_id'];
                if (isset($data['supplier_id']) && $data['supplier_id']) {

                    $product_supplier = $this->field('id')->where(['supplier_id' => $data['supplier_id'], 'spu' => $spu])->find();
                }
                $product_model = new ProductModel();
                $product = $product_model->where(['spu' => $spu, 'lang' => 'zh'])->find();
                if (empty($product)) {
                    $product = $product_model->where(['spu' => $spu, 'lang' => 'en'])->find();
                }
//存在SPU编辑,反之新增,后续扩展性
                $data['brand'] = isset($product['brand']) ? $product['brand'] : '{"lang": "zh", "name": "", "logo": "", "manufacturer": ""}';
                if ($product_supplier) {
                    $data['updated_by'] = $admin;
                    $data['updated_at'] = date('Y-m-d H:i:s');

                    $where = [
                        'id' => $product_supplier['id'],
                    ];
                    $res = $this->where($where)->save($data);
                    if ($res) {
                        $results['code'] = '1';
                        $results['message'] = '成功！';
                    } else {
                        $results['code'] = '-101';
                        $results['message'] = '失败!';
                    }
                } else {

                    $data['spu'] = $spu;
                    $data['created_by'] = $admin;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $res = $this->add($data);
                    if ($res) {
                        $results['code'] = '1';
                        $results['message'] = '成功！';
                    } else {
                        $results['code'] = '-101';
                        $results['message'] = '失败!';
                    }
                }
            }

            return $results;
        } catch (Exception $e) {
            Log::write($e->getMessage());
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * spu 新增浏览数量
     * @author klp
     * @return array
     */
    public function updateView($spu = '') {
        if (empty($spu)) {
            return false;
        }
        try {

            $results = $this->where(['spu' => $spu])->setInc('view_count', 1);


            return $results;
        } catch (Exception $e) {
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
    public function checkParam($checkout, $spu) {
        if (empty($checkout)) {
            return false;
        }
        $results = $data = array();
        if (empty($spu)) {
            $results['code'] = '-1001';
            $results['message'] = '[spu]缺失!';
        }
        unset($checkout['id']);
        if (empty($checkout['supplier_id'])) {
            $results['code'] = '-1001';
            $results['message'] = '[supplier_id]缺失!';
        }
        if (!empty($checkout['supplier_id'])) {
            $data['supplier_id'] = $checkout['supplier_id'];
        }

        if ($results) {
            return $results;
        }
        return $data;
    }

    /* 通过SPU获取供应商信息
     * @param mix $SPUs // 商品SPU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getsupplieridsbyspu($spu) {
        try {
            if (!$spu) {
                return [];
            }

            $supplier_ids = $this->alias('ps')
                    ->field('ps.supplier_id')
                    ->where(['ps.spu' => $spu,
                        'ps.status' => 'VALID',
                        'ps.deleted_flag' => 'N'
                    ])
                    ->select();

            $supplierids = [];
            if (!$supplier_ids) {
                return [];
            } else {

                foreach ($supplier_ids as $supplierid) {
                    $supplierids[] = $supplierid['supplier_id'];
                }
            }

            return $supplierids;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    public function deleteByspu($spu, $supplier_id = null) {
        $sku_where['g.spu'] = $where['spu'] = $spu;
        $sku_where['gs.deleted_flag'] = 'N';
        $sku_where['g.deleted_flag'] = 'N';
        $data['deleted_flag'] = 'Y';
        $data['updated_by'] = defined(UID) ? UID : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($supplier_id) {
            $where['supplier_id'] = $supplier_id;
            $sku_where['gs.supplier_id'] = $supplier_id;
        }
        $goods_model = new GoodsModel();
        $goods_supplier_model = new GoodsSupplierModel();
        $goods_supplier_table = $goods_supplier_model->getTableName();
        $sku_where = $where;

        $skuinfo = $goods_model->alias('g')
                        ->join($goods_supplier_table . ' as gs on gs.sku=g.sku')
                        ->field('g.sku')
                        ->where($sku_where)->find();

        if (!$skuinfo) {
            $this->where($where)->save($data);
        }
    }

    /**
     * 供应商询单统计
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getCountryBySpu($supplier_id, $created_at_start, $created_at_end, &$item) {

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
            //  'area_bn' => ['in', $this->areas],
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
                ->group('country_bn')
                ->select();

        foreach ($areacounts as $areacount) {
            $item[$areacount['area_bn']] = $areacount['area_count'];
            $item['total'] += $areacount['area_count'];
        }
    }

    /**
     * 获取供应商询单ID
     * @param int $spu
     * @return mix
     * @author zyg
     */
    public function getInquiryCountAndAvgPriceBySpu($spu, &$item) {
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName();
        $goods_model = new GoodsModel();
        $goods_table = $goods_model->getTableName();
        $tmp_table = '(SELECT fqi.inquiry_id,sum(if(fqi.total_quote_price>0,fqi.total_quote_price,0)) as total_quote_price,'
                . '(SELECT g.spu from ' . $goods_table . ' as g where g.sku=fqi.sku and lang=\'zh\' and deleted_flag=\'N\' '
                . 'GROUP BY g.sku )  as spu '
                . 'from ' . $final_quote_item_table . ' fqi where fqi.deleted_flag=\'N\' '
                . 'and fqi.`status`=\'VALID\' GROUP BY fqi.inquiry_id,spu)  tmp_table';
        $inquiryinfo = $this->query('select count(inquiry_id) as quote_num,avg(total_quote_price) as avg_quote_price from '
                . $tmp_table . ' where spu=\'' . $spu . '\'');
        $item['avg_price'] = isset($inquiryinfo['avg_quote_price']) ? $inquiryinfo['avg_quote_price'] : 0;
        $item['quote_num'] = count($inquiryinfo['quote_num']);
    }

    /**
     * 获取spu 询单次数
     * @param string $country_bn
     * @return mix
     * @author zyg
     */
    public function getInquiryCountBySpuCount($country_bn) {
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName();
        $goods_model = new GoodsModel();
        $goods_table = $goods_model->getTableName();

        $product_model = new ProductModel();
        $product_table = $product_model->getTableName();
        $tmp_table = '(SELECT fqi.inquiry_id,sum(if(fqi.total_quote_price>0,fqi.total_quote_price,0)) as total_quote_price,'
                . '(SELECT g.spu from ' . $goods_table . ' as g where g.sku=fqi.sku and lang=\'zh\' and deleted_flag=\'N\' '
                . 'GROUP BY g.sku )  as spu '
                . 'from ' . $final_quote_item_table . ' fqi where fqi.deleted_flag=\'N\' '
                . 'and fqi.`status`=\'VALID\' GROUP BY fqi.inquiry_id,spu)  tmp_table';
        $where = [
            'i.status' => 'QUOTE_SENT',
            'i.quote_status' => 'COMPLETED',
            'i.deleted_flag' => 'N',
            1 => 'tmp_table.spu is not null',
        ];

        if ($country_bn) {
            $where['i.country_bn'] = $country_bn;
        }
        $inquiry_model = new InquiryModel();
        $inquirys = $inquiry_model
                ->alias('i')
                ->join($tmp_table . '  on tmp_table.inquiry_id =i.id')
                ->field('i.country_bn, count(i.id) as quote_num , tmp_table.spu,'
                        . '(select name from ' . $product_table . ' as p where '
                        . 'p.lang=\'zh\' and p.spu=tmp_table.spu and p.deleted_flag=\'N\' group by  p.spu) as product_name')
                ->where($where)
                ->count();
        return $inquirys;
    }

    /**
     * 获取spu 询单次数
     * @param string $country_bn
     * @return mix
     * @author zyg
     */
    public function getSpuandInquiryCountList($country_bn, $condition) {
        $final_quote_item_model = new FinalQuoteItemModel();
        $final_quote_item_table = $final_quote_item_model->getTableName();
        $goods_model = new GoodsModel();
        $goods_table = $goods_model->getTableName();

        $country_model = new CountryModel();
        $country_table = $country_model->getTableName();

        $product_model = new ProductModel();
        $product_table = $product_model->getTableName();
        $tmp_table = '(SELECT fqi.inquiry_id,sum(if(fqi.total_quote_price>0,fqi.total_quote_price,0)) as total_quote_price,'
                . '(SELECT g.spu from ' . $goods_table . ' as g where g.sku=fqi.sku and lang=\'zh\' and deleted_flag=\'N\' '
                . 'GROUP BY g.sku )  as spu '
                . 'from ' . $final_quote_item_table . ' fqi where fqi.deleted_flag=\'N\' '
                . 'and fqi.`status`=\'VALID\' GROUP BY fqi.inquiry_id,spu)  tmp_table';
        $where = [
            'i.status' => 'QUOTE_SENT',
            'i.quote_status' => 'COMPLETED',
            'i.deleted_flag' => 'N',
            1 => 'tmp_table.spu is not null',
        ];

        if ($country_bn) {
            $where['i.country_bn'] = $country_bn;
        }
        $inquiry_model = new InquiryModel();
        list($starrow, $pagesize) = $this->_getPage($condition);
        $inquirys = $inquiry_model
                ->alias('i')
                ->join($tmp_table . ' on tmp_table.inquiry_id =i.id')
                ->field('(select name from ' . $country_table . ' as c where lang=\'zh\' and c.bn=i.country_bn group by c.bn ) as country_name,i.country_bn, '
                        . 'count(i.id) as quote_num , tmp_table.spu,'
                        . '(select name from ' . $product_table . ' as p where '
                        . 'p.lang=\'zh\' and p.spu=tmp_table.spu and p.deleted_flag=\'N\' group by  p.spu) as product_name')
                ->where($where)
                ->group('tmp_table.spu')
                ->limit($starrow, $pagesize)
                ->select();
        return $inquirys;
    }

}
