<?php
/**
 * 购物车
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/9
 * Time: 21:16
 */
class ShoppingCarModel extends publicModel{
    protected $tableName = 'shopping_car';
    protected $dbName = 'erui_mall';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 我的购物车
     */
    public function myShoppingCar($condition){
        if(empty($condition) || !isset($condition['lang'])){
            return false;
        }
        $condition['type'] = $condition['type'] ? $condition['type'] : 0;
        $condition['deleted_flag'] = 'N';
        try{
            $goodsModel= new GoodsModel();
            $goodsTable = $goodsModel->getTableName();
            $result = $this->field('id,lang,sku,spu,buy_number')->where($condition)->select();
            if($result){
                $skus = [];
                $spus = [];
                foreach($result as $item){
                    $skus[] = $item['sku'];
                    $spus[] = $item['spu'];
                }

                $goodsModel= new GoodsModel();
                $goodsTable = $goodsModel->getTableName();
                $productModel = new ProductModel();
                $productTable =$productModel->getTableName();
                $goods = $goodsModel->field("$goodsTable.spu,$goodsTable.sku,$goodsTable.name,$goodsTable.show_name,$goodsTable.min_pack_naked_qty,$goodsTable.nude_cargo_unit,$goodsTable.min_pack_unit,$productTable.name as spu_name,$productTable.show_name as spu_show_name,$goodsTable.lang,$goodsTable.model,$goodsTable.status")
                    ->join("$productTable ON $productTable.spu=$goodsTable.spu AND $productTable.lang=$goodsTable.lang")->where(["$goodsTable.sku"=>['in',$skus], "$goodsTable.lang"=>$condition['lang'], "$goodsTable.deleted_flag"=>'N'])->select();
				$goodsAry = [];
				foreach($goods as $r){
					$r['name'] = empty($r['show_name']) ? (empty($r['name']) ? (empty($r['spu_show_name']) ? $r['spu_name'] : $r['spu_show_name']) : $r['name']): $r['show_name'];
					$goodsAry[$r['sku']] = $r;
				}
					
                //扩展属性
                $gattrModel = new GoodsAttrModel();
                $condition_attr = ['sku'=>['in', $skus], 'lang'=>$condition['lang'], 'deleted_flag'=>'N'];
                $attrs = $gattrModel->field('sku,spec_attrs')->where($condition_attr)->select();
                $attrAry = [];
                foreach($attrs as $attr){
                    $attrAry[$attr['sku']] = json_decode($attr['spec_attrs'],true);
                }

                //图
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu( $spus );
                $dataAttach = [''];
                foreach ( $attachs as $r ) {
                    if ( isset( $dataAttach[ $r[ 'spu' ] ] ) ) {
                        if ( $r[ 'default_flag' ] == 'Y' ) {
                            $dataAttach[ $r[ 'spu' ] ] = $r[ 'attach_url' ];
                        }
                        continue;
                    }
                    $dataAttach[ $r[ 'spu' ] ] = $r[ 'attach_url' ];
                }
            }
            return $result ? ['skuAry'=>$result, 'infoAry' =>$goodsAry, 'thumbs'=>$dataAttach, 'attrAry'=>$attrAry] : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】 myShoppingCar:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 添加/编辑车
     * @param $input
     * @param type 0 询单车  1购物车
     */
    public function edit($input){
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('',ErrorMsg::NOTNULL_SPU);
        }

        if(!isset($input['skus']) || empty($input['skus']) || !is_array($input['skus'])){
            jsonReturn('',ErrorMsg::NOTNULL_SKU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('',ErrorMsg::NOTNULL_LANG);
        }

        try{
            $userInfo = getLoinInfo();
            $this->startTrans();
            foreach($input['skus'] as $sku => $count){
                $data = [
                    'lang' => $input['lang'],
                    'buyer_id' => isset($input['buyer_id']) ? $input['buyer_id'] : $userInfo['id'],
                    'spu' => trim($input['spu']),
                    'sku' => trim($sku),
                    'buy_number' => trim($count),
                    'type'=>$input['type'] ? $input['type'] : 0,
                    'deleted_flag' => 'N'
                ];

                $condition = [
                    'spu' => trim($input['spu']),
                    'sku' => trim($sku),
                    'lang' => $input['lang'],
                    'buyer_id' => isset($input['buyer_id']) ? $input['buyer_id'] : $userInfo['id']
                ];
                $result = $this->field('id')->where($condition)->find();
                if($result){
                    $data['updated_at'] = date('Y-m-d H:i:s');
                    $result = $this->where(['id'=>$result['id']])->save($data);
                }else{
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $result = $this->add($this->create($data));
                }
                if(!$result) {
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return true;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】edit:' . $e , Log::ERR);
            return false;
        }
    }
	
	/**
	 * 删除
	 */
	public function del($input){
		if(!isset($input['idAry']) || empty($input['idAry'])){
            jsonReturn('','请选择要删除的ID');
        }
		
		$userInfo = getLoinInfo();
		$condition = [
			'id' => ['in',$input['idAry']],
			//'buyer_id' => $userInfo['id']
		];
		try{
			$data = [
				'deleted_flag' => 'Y',
				'updated_at' => date('Y-m-d H:i:s'),
			];
			$result = $this->where($condition)->save($data);
			return $result ? $result : false;
		}catch(Exception $e){
			 Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】del:' . $e , Log::ERR);
             return false;
		}
	}


}