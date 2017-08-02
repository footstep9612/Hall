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
        $result = $goodsModel->editSkuInfo($this->input);
        $this->returnInfo($result);
    }
    /**
     * sku状态更改(审核)/删除  -- 总接口
     * @param    status_type(状态flag ) 存在为修改状态,反之为删除
     *           标志: declare(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码  spu编码   lang语言
     * @example   $this->input=[
                            'status_type'=> 'declare',
                            0=>[
                                'sku'=> '3303060000010001',
                                'spu'=> '3403060000010001',
                                'lang'=> 'en'
                                ],
                            1=>[],...
                        ];
     * @return true or false
     * @author  klp  2017/7-13
     */
    public function modifySkuAction(){
        if(empty($this->input)){
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['checked_by'] = $userInfo['name'];
        $goodsModel = new GoodsModel();
        if(isset($this->input['status_type']) && !empty($this->input['status_type'])){
            $result = $goodsModel->modify($this->input);    //状态更改(暂为报审)
        } else{
            $result = $goodsModel->deleteReal($this->input);//真实删除
        }
        $this->returnInfo($result);
    }


    /**
     * sku新增 (单独) -- BOSS后端
     * @param  sku[]: (必传项) spu(编码)  name(名称)  show_name(展示名称) lang(语言)
     * @return sku编码
     * @author  klp  2017/7-5
     */
    public function addSkuAction(){

        $goodsModel = new GoodsModel();
        $result = $goodsModel->createSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku属性新增 (单独) -- BOSS后端
     * @param  attr[]:  attr_no(属性编码) attr_name(属性名称)
     *                 goods_flag(商品属性)   spec_flag(规格型号)  logi_flag(物流属性)  hs_flag(申报要素)
     *                注:属性添加时带其中一个flag
     * @return id
     * @author  klp  2017/7-5
     */
    public function addSkuAttrAction(){

        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->createAttrSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku附件新增 (单独) -- BOSS后端
     * @param  attach[]:  attach_url(文件地址)
     * @author  klp  2017/7-5
     */
    public function addSkuAttachAction(){

        $goodsAttachModel = new GoodsAttachModel();
        $result = $goodsAttachModel->createAttachSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku更新  (单独)-- BOSS后端
     * @param  sku[]: (必传项) spu(编码)  name(名称)  show_name(展示名称)
     * @author  klp  2017/7-5
     */
    public function updateSkuAction(){

        $goodsModel = new GoodsModel();
        $result = $goodsModel->updateSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku属性更新 (单独) -- BOSS后端
     * @param  attr[]:  attr_no(属性编码) attr_name(属性名称)
     *                 goods_flag(商品属性)   spec_flag(规格型号)  logi_flag(物流属性)  hs_flag(申报要素)
     *                注:属性添加时带其中一个flag
     * @author  klp  2017/7-5
     */
    public function updateSkuAttrAction(){
        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->updateAttrSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku状态更改及删除 (单独) -- BOSS后端
     * @param  []:  status_type(状态flag ) 存在为修改状态,反之为删除
     *           标志: declare(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码    lang语言    checked_desc(审核描述)
     * @param  []: 删除:  sku编码    lang语言
     * @author  klp  2017/7-5
     */
    public function changSkuAction(){
        $goodsModel = new GoodsModel();
        switch($this->input['status_type']){
            case 'declare':    //报审
                $input['status'] = $goodsModel::STATUS_CHECKING;
                break;
            case 'valid':    //审核通过
                $input['status'] = $goodsModel::STATUS_VALID;
                break;
            case 'invalid':    //驳回
                $input['status'] = $goodsModel::STATUS_INVALID;
                break;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['checked_by'] = $userInfo['name'];
       if(isset($this->input['status']) && !empty($this->input['status'])){
           $result = $goodsModel->modifySku($this->input);//状态更改
       } else{
           $result = $goodsModel->deleteRealSku($this->input);//真实删除
       }
        $this->returnInfo($result);
    }

    /**
     * sku属性状态更改及删除 (单独)  -- BOSS后端
     * @param  []:  status_type(状态flag ) 存在为修改状态,反之为删除
     *           标志: declare(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码    lang语言
     * @param  []: 删除:  sku编码    lang语言
     * @author  klp  2017/7-5
     */
    public function modifySkuAttrAction(){
        $goodsAttrModel = new GoodsAttrModel();
        switch($this->input['status_type']){
            case 'declare':    //报审
                $input['status'] = $goodsAttrModel::STATUS_CHECKING;
                break;
            case 'valid':    //审核通过
                $input['status'] = $goodsAttrModel::STATUS_VALID;
                break;
            case 'invalid':    //驳回
                $input['status'] = $goodsAttrModel::STATUS_INVALID;
                break;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['checked_by'] = $userInfo['name'];
        if(isset($this->input['status']) && !empty($this->input['status'])){
            $result = $goodsAttrModel->modifySkuAttr($this->input);//状态更改
        } else{
            $result = $goodsAttrModel->deleteRealAttr($this->input);//真实删除
        }
        $this->returnInfo($result);
    }

    /**
     * sku附件状态更改及删除 (单独)  -- BOSS后端
     * @param  []:  status_type(状态flag ) 存在为修改状态,反之为删除
     *           标志: declare(报审)    valid(通过)     invalid(驳回)
     * @param     sku编码
     * @param  []: 删除:  sku编码
     * @author  klp  2017/7-5
     */
    public function deleteSkuAttachAction(){
        $goodsAttachModel = new GoodsAttachModel();
        switch($this->input['status_type']){
            case 'declare':    //报审
                $input['status'] = $goodsAttachModel::STATUS_CHECKING;
                break;
            case 'valid':    //审核通过
                $input['status'] = $goodsAttachModel::STATUS_VALID;
                break;
            case 'invalid':    //驳回
                $input['status'] = $goodsAttachModel::STATUS_INVALID;
                break;
        }
        if(isset($this->input['status']) && !empty($this->input['status'])){
            $result = $goodsAttachModel->modifySkuAttach($this->input);//状态更改
        } else{
            $result = $goodsAttachModel->deleteRealAttach($this->input);//真实删除
        }
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
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
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