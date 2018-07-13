<?php
/**
 * 价格策略折扣
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:50
 */
class PriceStrategyDiscountModel extends PublicModel{
    //put your code here
    protected $tableName = 'price_strategy_discount';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 列表
     * @param $input
     * @return bool|mixed
     */
    public function getList($input,$order){
        $where = [
            'deleted_at'=>['exp', 'is null']
        ];
        if(isset($input['group'])){
            $where['group'] = trim($input['group']);
        }
        if(isset($input['group_id'])){
            $where['group_id'] = trim($input['group_id']);
        }
        if(isset($input['sku'])){
            $where['sku'] = is_array($input['sku']) ? ['in', $input['sku']] : $input['sku'];
        }

        try{
            return $this->field('id,group,group_id,sku,discount,promotion_price,min_purchase_qty,max_purchase_qty,created_by,created_at')->where($where)->order($order)->select();
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 更新
     * @author link
     * @param $group string 来源：STOCK现货， SPECIAL专题， MALL商城
     * @param $group_id int 来源id与group相对应
     * @param $sku string
     * @param $price_range 二维array 策略信息
     * @return bool|mixed
     */
    public function updateData($group,$group_id,$sku,$price_range,$price_strategy_type,$price){
        try{
            $insertAll = [];

            //清除折扣
            $find = $this->where(['group'=>$group,'group_id'=>$group_id,'sku'=>$sku])->find();
            if($find){
                $rm = $this->where(['group'=>$group,'group_id'=>$group_id,'sku'=>$sku])->delete();
                if(!$rm){
                    return false;
                }
            }

            foreach($price_range as $k => $item){
                $data = [
                    'group' => $group,
                    'group_id' => $group_id,
                    'sku' => $sku,
                    'discount' => (isset($item['discount']) && $item['discount'] != '') ? trim($item['discount']) : 10,
                    'promotion_price' => (isset($item['promotion_price']) && $item['promotion_price'] != '') ? trim($item['promotion_price']) : 0,
                    'min_purchase_qty' => (isset($item['min_purchase_qty']) && $item['min_purchase_qty'] != '') ? intval($item['min_purchase_qty']) : 1,
                    'max_purchase_qty' => (isset($item['max_purchase_qty']) && $item['max_purchase_qty'] != '') ? intval($item['max_purchase_qty']) : null,
                ];
                if($price_strategy_type=='Z' && $price){
                    $data['promotion_price'] = $price*($data['discount']/10);
                }
               /* if(isset($item['id'])){
                    $data['updated_at'] = date('Y-m-d H:i:s',time());
                    $data['updated_by'] = defined('UID') ? UID : 0;
                    $updated =$this->where(['id'=>intval($item['id'])])->save($data);
                    if(!$updated){
                        return false;
                    }
                    $update_id[] = intval($item['id']);
                }else{*/
                    $data['created_at'] = date('Y-m-d H:i:s',time());
                    $data['created_by'] = defined('UID') ? UID : 0;
                    $insertAll[] = $data;
                //}
                unset($data);
            }

            if(!empty($insertAll)){
                return $this->addAll($insertAll);
            }
            return true;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @author link
     * @param $input
     * @return bool|mixed
     */
    public function deleteData($input){
        if(isset($input['id'])){
            $where['id'] = is_array($input['id']) ? ['in', $input['id']] : $input['id'];
        }else{
            if(empty($input['group'])){
                jsonReturn('',MSG::MSG_FAILED, 'group不能为空');
            }
            if(empty($input['group_id'])){
                jsonReturn('',MSG::MSG_FAILED, 'group_id不能为空');
            }
            if(empty($input['sku'])){
                jsonReturn('',MSG::MSG_FAILED,'sku不能为空');
            }
            $where = ['group'=>$input['group'],'group_id'=>$input['group_id'],'sku'=>$input['sku']];
        }

        try{
            $data = [];
            $data['deleted_at'] = date('Y-m-d H:i:s',time());
            $data['deleted_by'] = defined('UID') ? UID : 0;

            $flag = $this->where($where)->save($data);
            return $flag ? $flag : false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 检测是否存在
     * @param array $where
     * @return bool|mixed
     */
    public function getExist($where=[]){
        try{
            $result = $this->field('id')->where($where)->find();
            return $result ? true : false;
        }catch (Exception $e){
            return ['code'=>0, 'error'=>$e];
        }
    }
}