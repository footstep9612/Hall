<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 9:34
 */
//class GoodsController extends PublicController{
class GoodsController extends Yaf_Controller_Abstract{
    private $input;

    public function init()
    {
//        parent::init();
//        $this->input = json_decode(file_get_contents("php://input"), true);

    }

    /**
     * sku管理列表
     * @pararm 适用于:关联sku列表  审核列表  上架列表
     * @return array
     * @author klp
     */
    public function listAction(){
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku基本详情
     * @pararm  sku编码 lang status
     * @return array
     * @author klp
     */
    public function skuInfoAction(){
//        $this->put_data = [
//                'sku'=> '3303060000010001',
//                'lang'=> 'en',
//        ];
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getSkuInfo($this->put_data);
        $this->returnInfo($result);
    }
    /**
     * sku属性详情
     * @pararm
     * @return array
     * @author klp
     */
    public function skuAttrsInfoAction(){
        $this->put_data = [
            'sku'=> '84877319',

        ];
        $goodsModel = new GoodsAttrModel();
        $result = $goodsModel->getSkuAttrsInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku附件详情
     * @pararm
     * @return array
     * @author klp
     */
    public function skuAttachsInfoAction(){
//        $this->put_data = [
//            'sku'=> '37518005',
//        ];
        $goodsModel = new GoodsAttachModel();
        $result = $goodsModel->getSkuAttachsInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 查找用户信息
     * @pararm  用户id
     * @return
     * @author klp
     */
    public function userInfoAction(){
        if(empty($this->put_data['id'])) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $userModel = new UserModel();
        $result = $userModel->info($this->put_data['id']);
        $this->returnInfo($result);
    }

    /**
     * 商品进货价格/供应商查询
     * @param   sku
     * @author  klp  2017/8/2
     */
    public function supplierCostInfoAction(){
//        $this->put_data = '37518005';
        $GoodsCostPriceModel = new GoodsCostPriceModel();
        $result = $GoodsCostPriceModel->getInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku新增/编辑  -- 总接口
     * @param  sku: sku编码不存在为新建,反之更新
     * @param          spu(编码)  name(名称)  show_name(展示名称) lang(语言数组)
     * @param  attr:  attr_no(属性编码) attr_name(属性名称)
     *                 ex_goods_attrs(商品属性)   spec_attrs(规格型号)  other_attrs(其它属性)  ex_hs_attrs(申报要素)
     *                注:属性添加时带其中一个flag
     * @param  attach:  attach_url(文件地址)
     * @param  supplier_cost:  supplier_id(供应商ID)
     * @example [
     *           sku:'',
     *           en=>[
     *                name:'',...
     *                attrs=>[],
     *           ],
     *          zh=>[],...
     *          attachs=>[]
     *          supplier_cost=>[]
     * ]
     *  @return sku编号
     * @author  klp  2017/7-13
     */
    public function editSkuAction(){
        /*  $this->put_data = [
              "sku"=>'',
              "zh"=>[
                  'lang'        =>'zh',
                  'spu'		  =>'8832211',
                  'name'		  =>'3123',
                  'show_name'   =>'3123',
                  "attrs"=>[
                      'spec_attrs'	  =>[
                          0=>[
                              'attr_name' =>'78121',
                              'attr_value' =>'1',
                              'value_unit' =>'1',
                              'spec_flag' =>'Y',
                          ],

                      ],
                      'ex_goods_attrs'  =>[
                          0=>[
                              'attr_name' =>'79212',
                              'attr_value' =>'2',
                              'value_unit' =>'2',
                              'goods_flag' =>'Y',
                          ],
                      ],
                      'ex_hs_attrs'	  =>[
                          0=>[
                              'attr_name' =>'7333',
                              'attr_value' =>'3',
                              'value_unit' =>'3',
                              'hs_flag' =>'Y',
                          ]
                      ],
                      'other_attrs'	  =>[
                          0=>[
                              'attr_name' =>'7444',
                              'attr_value' =>'4',
                              'value_unit' =>'4',
                          ]
                      ],
                  ],
              ],
              "attachs"=>[
                  0=>[
 //                     'id'=>160,
                      'supplier_id'    =>'33711223',
                      'attach_type'	 =>'',
                      'attach_name'	 =>'',
                      'attach_url'     =>'a/b/c.png',
                      'sort_order'     =>'0',
                  ],

              ],
              'supplier_cost'=>[
                  0=>[
 //                     'id'=>11,
                      'supplier_id'	     =>'22112123',
                      'min_purchase_qty'	 =>1
                  ]
              ],

          ];
         return $this->put_data;*/
        $goodsModel = new GoodsModel();
        $result = $goodsModel->editSku($this->put_data);
        $this->returnInfo($result);
    }
    /**
     * sku状态更改  -- 总接口
     * @param    status_type(状态flag ) 存在为修改状态
     *           标志: check(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码  spu编码   lang语言
     * @example   $this->put_data=[
     *                      'status_type'=> 'check',
     *                      0 => [
     *                           'sku'=> '3303060000010001',
     *                           'spu'=> '340306010001',
     *                           'lang'=> 'zh',
     *                           'remarks' =>  '',
     *                           ],
     *                      1 => [],...
     *                  ];
     * @return true or false
     * @author  klp  2017/8/1
     */
    public function modifySkuAction(){
        /* $this->put_data = [
                 'status_type'=> 'valid',
                  0 => [
                       'sku'=> '77799377',
                       'spu'=> '8832211',
                       'lang'=> 'zh',
                       'remarks' =>  ''
                       ],
                  ];
        return $this->put_data;*/
        if(empty($this->put_data)){
            return false;
        }
        $goodsModel = new GoodsModel();
        $result = $goodsModel->modifySkuStatus($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku删除  -- 总接口
     * @param     sku编码  spu编码   lang语言
     * @example   $this->put_data=[
     *                  0  => [
     *                       'sku'=> '3303060000010001',
     *                       'lang'=> 'zh'
     *                       ],
     *                      1  => [],...
     *                  ];
     * @return true or false
     * @author  klp  2017/8/1
     */
    public function deleteRealSkuAction(){
        /*    $this->put_data=[
  //                     'sku'  => [
  //                           '77799377','55362548'
  //                        ],
                      'sku' =>'59633483',
                      'lang'=> 'zh'
                       ];
        return $this->put_data;*/
        if(empty($this->put_data)){
            return false;
        }
        $goodsModel = new GoodsModel();
        $result = $goodsModel->deleteSkuReal($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku附件新增
     * @author  klp  2017/7-6
     */
    public function addSkuAttachAction(){
        $userInfo = getLoinInfo();
        $this->put_data['user_id'] = $userInfo['id'];
        $gattach = new GoodsAttachModel();
        $resAttach = $gattach->editSkuAttach($this->put_data);
        $this->returnInfo($resAttach);
    }

    /**
     * sku附件删除
     * @param  "sku":['000001'，'000002',...]
     * @author  klp  2017/7-6
     */
    public function delSkuAttachAction(){
        $this->put_data=[
            'sku'  => [
                '37518005'
            ],
        ];
        $gattach = new GoodsAttachModel();
        $resAttach = $gattach->deleteSkuAttach($this->put_data);
        if($resAttach && $resAttach['code'] == 1){
            $this->jsonReturn($resAttach);
        } else{
            $this->setCode(MSG::MSG_FAILED);
            $data['code'] = $this->getCode();
            $data['message'] = '失败!';
            $this->jsonReturn($data);
        }
    }

    /**
     * sku供应商  -- 通过生产商ID或名称获取供应商信息
     * @author  klp  2017/7-6
     */
    public function listSupplierAction(){
        $SupplierModel = new SupplierModel();
        $result = $SupplierModel->getlist($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * sku审核记录查询
     * @param sku
     * @author  klp  2017/8/2
     */
    public function checkInfoAction(){
        $ProductChecklogModel = new ProductChecklogModel();
        $result = $ProductChecklogModel->getRecord($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 审核记录
     * @author link 2017-08-05
     */
    public function checklogAction(){
        if(!isset($this->put_data['sku'])) {
            jsonReturn('',ErrorMsg::NOTNULL_SKU);
        }

        if(!isset($this->put_data['lang'])) {
            jsonReturn('',ErrorMsg::NOTNULL_LANG);
        }

        $pchecklog = new ProductChecklogModel();
        $logs = $pchecklog -> getRecord(array('spu'=>$this->put_data['sku'],'lang'=>$this->put_data['lang']),'sku,lang,status,remarks,approved_by,approved_at');
        if($logs!==false){
            jsonReturn($logs);
        }else{
            jsonReturn('',ErrorMsg::FAILED);
        }
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
            'supplier_cost'=>[
                'supplier_id'	     =>'',
                'price'	             =>'',
                'price_unit'	     =>'',
                'price_cur_bn'	     =>'',
                'min_purchase_qty'	 =>'',
                'pricing_date'	     =>'',
                'price_validity'	 =>'',
            ]
        ];
    }

}