<?php
/**
 * Name: ProductShelves
 * Desc: 产品线管理
 * User: zhangyuliag
 * Date: 2017/7/21
 * Time: 14:28
 */
class ProductShelvesController extends PublicController {

    public function __init() {
        parent::__init();
    }

    //产品上架
    public function upShelvesAction(){
        $showcatproduct = new ShowCatProductModel();
        $showcatgoods = new ShowCatGoodsModel();
        $product = new ProductModel();
        $goods = new GoodsModel();
        $createcondition = $this->put_data;

        $showcatproduct->startTrans();
        $results = $showcatproduct->addData($createcondition);
        if($results['code']==1){
            $where['lang'] = $createcondition['lang'];
            $where['spu'] = $createcondition['spu'];
            $where['status'] = 'VALID';
            $goodslist = $goods->field('sku')->where($where)->select();
            $condition = $where;
            $condition['skus'] = $goodslist;
            $condition['show_cat'] = $createcondition['show_cat'];
            $condition['show_name'] = $createcondition['show_name'];
            $condition['created_by'] = $createcondition['created_by'];

            $goodsres = $showcatgoods->addData($condition);

            if($goodsres['code']==1){
                $productstatus = $product->where(['spu'=>$createcondition['spu']])->update(['shelves_status'=>'VALID']);
                $goodsstatus = $product->where($where)->update(['shelves_status'=>'VALID']);
                if($productstatus && $goodsstatus){
                    $showcatproduct->commit();
                }else{
                    $showcatproduct->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '添加失败!';
                }
            }else{
                $showcatproduct->rollback();
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
        }else{
            $showcatproduct->rollback();
        }

    }

    //产品下架

}