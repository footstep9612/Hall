<?php
/**
 * name: OrderBuyerContact.php
 * desc: 订单供应商模型.
 * User: 郑开强
 * Date: 2017/9/13
 * Time: 11:22
 */
class OrderContactModel extends PublicModel {

    protected $dbName = 'erui2_order'; //数据库名称
    protected $tableName = 'order_contact'; //数据表表名
    
	/**
     * 保存
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
	**/
	public function saveData($data,&$insert_id=null){
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
			$this->save($data,['id'=>$insert_id]);
			return ['code'=>1,'message'=>'更新成功'];			
		}		
	}
}