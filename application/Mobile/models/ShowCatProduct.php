<?php

/**
 * 展示分类与产品映射
 * User: linkai
 * Date: 2017/6/15
 * Time: 19:24
 */
class ShowCatProductModel extends PublicModel {

    const STATUS_DRAFT = 'DRAFT';    //草稿
    const STATUS_APPROVING = 'APPROVING';    //审核
    const STATUS_VALID = 'VALID';    //生效
    const STATUS_DELETED = 'DELETED';    //删除
    const STATUS_ONSHELF = 'Y';    //上架
    const STATUS_UNSHELF = 'N';    //未上架

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'show_cat_product';

    public function __construct() {


        parent::__construct();
    }

    /*
     * 根据SPUS 获取产品展示分类信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  展示分类信息列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getShowcatnosByspu($spu, $country_bn, $lang = 'en') {

        $show_cat_model = new ShowCatModel();
        $show_cat_table = $show_cat_model->getTableName();
        $show_cat_products = $this->alias('scp')
                ->join($show_cat_table . ' sc on scp.cat_no=sc.cat_no', 'left')
                ->field('sc.cat_no,sc.name,sc.parent_cat_no')
                ->where(['scp.spu' => $spu,
                    'scp.status' => 'VALID',
                    'scp.lang' => $lang,
                    'sc.status' => 'VALID',
                    'sc.lang' => $lang,
                    'sc.id>0',
                    'sc.country_bn' => $country_bn,
                ])
                ->find();
        return $show_cat_products;
    }

}
