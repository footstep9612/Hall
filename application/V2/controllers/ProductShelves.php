<?php

/**
 * Name: ProductShelves
 * Desc: 产品上下架
 * User: zhangyuliag
 * Date: 2017/7/21
 * Time: 14:28
 */
//class ProductshelvesController extends PublicController {
class ProductshelvesController extends Yaf_Controller_Abstract{
      public function __init() {
//        parent::__init();
      }

      //产品上架
      public function upShelvesAction() {
        /*$this->put_data = [
            'cat_no'=>'123,456',
            'lang'=>'zh',
            'spu'=>'8832211',
            'onshelf_flag'=>'Y',
        ];
        $this->user['id'] = '123';*/
        $showcatproduct = new ShowCatProductModel();
        $showcatgoods = new ShowCatGoodsModel();
        $product = new ProductModel();
        $goods = new GoodsModel();
        $createcondition = $this->put_data;
        $createcondition['created_by'] = $this->user['id'];

        $showcatproduct->startTrans();
        $results = $showcatproduct->addData($createcondition);

        if ($results['code'] == 1) {
          $where['lang'] = $createcondition['lang'];
          $where['spu'] = $createcondition['spu'];
          $where['status'] = 'VALID';
          $goodslist = $goods->field('sku,name')->where($where)->select();
          $condition = $where;
          $condition['skus'] = $goodslist;
          $condition['cat_no'] = $createcondition['cat_no'];
          $condition['onshelf_flag'] = $createcondition['onshelf_flag'];
          $condition['created_by'] = $createcondition['created_by'];

          $goodsres = $showcatgoods->addData($condition);

          if ($goodsres['code'] == 1) {
              $showcatproduct->commit();
          } else {
            $showcatproduct->rollback();
            $results['code'] = '-101';
            $results['message'] = '上架失败!';
          }
        } else {
          $showcatproduct->rollback();
        }
        jsonReturn($results);
      }

      //产品下架
      public function downShelvesAction() {
         /* $this->put_data = [
           'lang'=>'zh',
           'spu'=>'8832211',
          ];*/
        $showcatproduct = new ShowCatProductModel();
        $showcatgoods = new ShowCatGoodsModel();
        $product = new ProductModel();
        $goods = new GoodsModel();
        $createcondition = $this->put_data;

        $showcatproduct->startTrans();
        $results = $showcatproduct->deleteData($createcondition);
        if ($results['code'] == 1) {
          $where['lang'] = $createcondition['lang'];
          $where['spu'] = $createcondition['spu'];
          $where['status'] = 'VALID';

          $goodsres = $showcatgoods->deleteData($createcondition);

          if ($goodsres['code'] == 1) {
              $showcatproduct->commit();
            } else {
            $showcatproduct->rollback();
            $results['code'] = '-101';
            $results['message'] = '下架失败!';
          }
        } else {
          $showcatproduct->rollback();
        }

        jsonReturn($results);
      }

      private function change_ProductShelvesStatus($spu, $status, $lang) {
        $es_product_model = new EsproductModel();
        $es_product_model->changesShelvesstatus($spu, $status, $lang);
      }

}
