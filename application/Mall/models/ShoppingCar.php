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
    public function myShoppingCar($lang,$country_bn){
        $userInfo = getLoinInfo();
        $userInfo['id'] = 1;
        $lang = 'en';
        $condition = ['buyer_id'=>$userInfo['id'], 'type'=>0, 'deleted_flag'=>'N'];
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
                    ->join("$productTable ON $productTable.spu=$goodsTable.spu AND $productTable.lang=$goodsTable.lang")->where(["$goodsTable.sku"=>['in',$skus], "$goodsTable.lang"=>$lang, "$goodsTable.deleted_flag"=>'N'])->select();
				$goodsAry = [];
				foreach($goods as $r){
					$r['name'] = empty($r['show_name']) ? (empty($r['name']) ? (empty($r['spu_show_name']) ? $r['spu_name'] : $r['spu_show_name']) : $r['name']): $r['show_name'];
					$goodsAry[$r['sku']] = $r;
				}
					
                //扩展属性
                $gattrModel = new GoodsAttrModel();
                $condition_attr = ['sku'=>['in', $skus], 'lang'=>$lang, 'deleted_flag'=>'N'];
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
     */
    public function edit($input){
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('',ErrorMsg::NOTNULL_SPU);
        }

        if(!isset($input['sku']) || empty($input['sku'])){
            jsonReturn('',ErrorMsg::NOTNULL_SKU);
        }
        $userInfo = getLoinInfo();
        $data = [
            'buyer_id' => $userInfo['id'],
            'spu' => $input['spu'],
            'sku' => $input['sku'],
            'buyer_id' => $input['buyer_id'],
            'buy_number' => $input['buy_number'],
            'type'=>$input['type'] ? $input['type'] : 0,
            'deleted_flag' => 'N'
        ];
        if(isset($_input['id']) && !empty($input['id'])){
            $condition = ['id' => $_input['id']];
            $data['updated_at'] = date('Y-m-d H:i:s');
            $result = $this->where($condition)->save($data);
            if($result){
                $result = $input['id'];
            }
        }else{
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->add($this->create($data));
        }
        return $result ? $result : false;
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