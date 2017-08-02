<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 9:34
 */
class GoodsController extends PublicController{
//class GoodsController extends Yaf_Controller_Abstract{
    private $input;

    public function init()
    {
        $this->input = json_decode(file_get_contents("php://input"), true);

    }

    /**
     * sku管理列表
     * @pararm 适用于:关联sku列表  审核列表  上架列表
     * @return array
     * @author klp
     */
    public function listAction(){
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku基本详情
     * @pararm  sku编码 lang status
     * @return array
     * @author klp
     */
    public function skuInfoAction(){
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getSkuInfo($this->input);
        $this->returnInfo($result);
    }
    /**
     * sku属性详情
     * @pararm
     * @return array
     * @author klp
     */
    public function skuAttrsInfoAction(){
        $goodsModel = new GoodsAttrModel();
        $result = $goodsModel->getSkuAttrsInfo($this->input);
        $this->returnInfo($result);
    }
    /**
     * sku附件详情
     * @pararm
     * @return array
     * @author klp
     */
    public function skuAttachsInfoAction(){
        $goodsModel = new GoodsAttachModel();
        $result = $goodsModel->getSkuAttachsInfo($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku新增/编辑  -- 总接口
     * @param  sku: sku编码不存在为新建,反之更新
     * @param          spu(编码)  name(名称)  show_name(展示名称) lang(语言数组)
     * @param  attr:  attr_no(属性编码) attr_name(属性名称)
     *                 goods_flag(商品属性)   spec_flag(规格型号)  logi_flag(物流属性)  hs_flag(申报要素)
     *                注:属性添加时带其中一个flag
     * @param  attach:  attach_url(文件地址)
     * @example [
     *           sku:'',
     *           en=>[
     *                name:'',...
     *                attrs=>[],
     *           ],
     *          zh=>[],...
     *          attachs=>[]
     * ]
     *  @return sku编号
     * @author  klp  2017/7-13
     */
    public function editSkuAction(){
        $goodsModel = new GoodsModel();
        $result = $goodsModel->editSku($this->input);
        $this->returnInfo($result);
    }
    /**
     * sku状态更改  -- 总接口
     * @param    status_type(状态flag ) 存在为修改状态
     *           标志: check(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码  spu编码   lang语言
     * @example   $this->input=[
     *                      'status_type'=> 'check',
     *                      0 => [
     *                           'sku'=> '3303060000010001',
     *                           'spu'=> '340306010001',
     *                           'lang'=> 'zh
     *                           ],
     *                      1 => [],...
     *                  ];
     * @return true or false
     * @author  klp  2017/8/1
     */
    public function modifySkuAction(){
        if(empty($this->input)){
            return false;
        }
        $goodsModel = new GoodsModel();
        $result = $goodsModel->modifySkuStatus($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku删除  -- 总接口
     * @param     sku编码  spu编码   lang语言
     * @example   $this->input=[
     *                  0  => [
     *                       'sku'=> '3303060000010001',
     *                      'spu'=> '340306010001',
     *                       'lang'=> 'en'
     *                       ],
     *                      1  => [],...
     *                  ];
     * @return true or false
     * @author  klp  2017/8/1
     */
    public function deleteRealSkuAction(){
        if(empty($this->input)){
            return false;
        }
        $goodsModel = new GoodsModel();
        $result = $goodsModel->deleteSku($this->input);
        $this->returnInfo($result);
    }


    /**
     * sku供应商  -- BOSS后端      待完善
     * @author  klp  2017/7-6
     */
    public function listSupplierAction(){
        $SupplierAccountModel = new SupplierAccountModel();
        $result = $SupplierAccountModel->getInfo($this->input);
        $this->returnInfo($result);
    }


    /**
     *   通过spu查询四种语言name
     * @author  klp  2017/7-22
     */
    public function getNameAction(){
        $productModel = new ProductModel();
        $result = $productModel->getName($this->put_data);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    //统一回复调用方法
    function returnInfo($result){
        if($result && !isset($result['code'])){
            jsonReturn($result);
        }else{
            jsonReturn('',MSG::MSG_FAILED,'失败');
        }
        exit;
    }

    public function ediAction(){
//        $edi = new Edi();
//        $data = $edi->test();var_dump($data);die;
    }

    public function testInput()
    {
        $data=[
            "sku"=>'',
            "en"=>[
                'lang'        =>'',
                'spu'		  =>'',
                'sku'		  =>'',
                'qrcode'      =>'',
                'name'	      =>'',
                'show_name'   =>'',
                'model'		  =>'',
                'description' =>'',

                'status'        =>'',
                'created_by'    =>'',
                'created_at'    =>'',
                'updated_by'    =>'',
                'updated_at'    =>'',
                'checked_by'    =>'',
                'checked_at'    =>'',

                //固定商品属性
                'exw_days'            =>'',
                'min_pack_naked_qty'  =>'',
                'nude_cargo_unit'     =>'',
                'min_pack_unit'       =>'',
                'min_order_qty'       =>'',
                'purchase_price'      =>'',
                'purchase_price_cur_bn'=>'',
                'nude_cargo_l_mm'     =>'',
                //固定物流属性
                'nude_cargo_w_mm'     =>'',
                'nude_cargo_h_mm'     =>'',
                'min_pack_l_mm'       =>'',
                'min_pack_w_mm'       =>'',
                'min_pack_h_mm'       =>'',
                'net_weight_kg'       =>'',
                'gross_weight_kg'     =>'',
                'compose_require_pack'=>'',
                'pack_type'=>'',
                //固定申报要素属性
                'name_customs'        =>'',
                'hs_code'             =>'',
                'tx_unit'             =>'',
                'tax_rebates_pct'     =>'',
                'regulatory_conds'    =>'',
                'commodity_ori_place' =>'',

                'attrs' =>[
                    'spec_attrs'	  =>[
                        0=>[
                            'attr_name' =>'',
                            'attr_value' =>'',
                            'value_unit' =>'',
                            'spec_flag' =>'Y',

                            'attr_group' =>'',
                            'attr_no' =>'',
                            'attr_value_type' =>'',
                            'goods_flag' =>'N',
                            'logi_flag' =>'N',
                            'hs_flag' =>'N',
                            'required_flag' =>'',
                            'search_flag' =>'',
                            'sort_order' =>'',
                            'status' =>'',
                        ],
                        1=>[

                        ],
                    ],
                    'ex_goods_attrs'  =>[
                        0=>[
                            'attr_name' =>'',
                            'attr_value' =>'',
                            'value_unit' =>'',
                            'goods_flag' =>'Y',
                        ],
                    ],
                    'ex_hs_attrs'	  =>[
                        0=>[
                            'attr_name' =>'',
                            'attr_value' =>'',
                            'value_unit' =>'',
                            'hs_flag' =>'Y',
                        ]
                    ],
                    'other_attrs'	  =>[
                        0=>[
                            'attr_name' =>'',
                            'attr_value' =>'',
                            'value_unit' =>'',
                        ]
                    ],

                ]
            ],
            "zh"=>[

            ],
            "es"=>[

            ],
            "ru"=>[

            ],
            "attachs"=>[
                0=>[
                    'supplier_id'    =>'',
                    'attach_type'	 =>'',
                    'attach_name'	 =>'',
                    'attach_url'     =>'',
                    'sort_order'     =>'',
                    'status'         =>'',
                    'created_by'	 =>'',
                    'created_at'	 =>'',
                    'updated_by'	 =>'',
                    'updated_at'	 =>'',
                    'checked_by'	 =>'',
                    'checked_at'	 =>'',

                ],
                1=>[

                ],
            ],
        ];
    }

}