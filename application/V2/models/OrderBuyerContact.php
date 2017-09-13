<?php
/**
 * name: OrderBuyerContact.php
 * desc: 订单采购商模型.
 * User: 郑开强
 * Date: 2017/9/13
 * Time: 11:22
 */
class OrderBuyerContactModel extends PublicModel {

    protected $dbName = 'erui2_order'; //数据库名称
    protected $tableName = 'order_buyer_contact'; //数据表表名
    
	/**
     * 保存
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
	**/
	public function saveData($data,&$insert_id){
		if(!isset($data['order_id']) || !is_numeric($data['order_id'])){
			return ['code'=>-105,'message'=>'订单ID不能为空'];
		}
		$order_id = intval($data['order_id']);
		$contact = $this->where(['order_id'=>$order_id])->find();
		if(empty($contact)){
			$insert_id = $this->add($data);
			return ['code'=>1,'message'=>'更新成功'];
		}else{
			$insert_id = $contact['id'];
			unset($data['id'],$data['create_at'],$data['create_by']);			
			if($this->save($data,['id'=>$insert_id]) !== false){
				return ['code'=>1,'message'=>'更新成功'];
			}else{
				return ['code'=>-108,'message'=>'更新失败'];
			}
		}				
	}
}