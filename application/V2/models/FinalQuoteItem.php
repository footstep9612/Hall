<?php
/**
 * @desc 最终报价单明细模型
 * @author 张玉良
 */
class FinalQuoteItemModel extends PublicModel {

    protected $dbName = 'erui2_rfq';
    protected $tableName = 'final_quote_item';
	protected $joinTable1 = 'erui2_rfq.quote_item b ON a.quote_item_id = b.id';
	protected $joinTable2 = 'erui2_rfq.inquiry_item c ON a.inquiry_item_id = c.id';
	protected $joinField = 'a.id,a.inquiry_id,a.quote_id,a.sku,a.supplier_id,a.exw_unit_price as final_exw_unit_price,a.quote_unit_price as final_quote_unit_price,'.
								'b.quote_qty,b.quote_unit,b.brand,b.exw_unit_price,b.quote_unit_price,b.net_weight_kg,b.gross_weight_kg,'.
								'b.package_mode,b.package_size,b.delivery_days,b.period_of_validity,b.goods_source,b.stock_loc,b.reason_for_no_quote,'.
								'c.buyer_goods_no,c.name,c.name_zh,c.model,c.remarks,c.remarks_zh';


	public function __construct() {
        parent::__construct();
    }

	/**
     * @desc 获取查询条件
 	 * @author 张玉良
     * @param $condition array
     * @return $where array
     */
    public function getWhere($condition) {
    	$where = array();

		if (!empty($condition['id'])) {
			$where['a.id'] = $condition['a.id'];
		}

		if (!empty($condition['inquiry_id'])) {
			$where['a.inquiry_id'] = $condition['inquiry_id'];
		}

    	if (!empty($condition['quote_id'])) {
			$where['a.quote_id'] = $condition['a.quote_id'];
		}

		$where['a.deleted_flag'] = !empty($condition['a.deleted_flag']) ? $condition['a.deleted_flag'] : 'N';
    	return $where;
    }
    
	/**
     * @desc 获取记录总数
 	 * @author 张玉良
     * @param array $condition 
     * @return int $count
     */
    public function getCount($condition) {
    	$where = $this->getWhere($condition);

		$count = $this->alias('a')
				->join($this->joinTable1, 'LEFT')
				->join($this->joinTable2, 'LEFT')
				->where($where)
				->count('a.id');
    	
    	return $count > 0 ? $count : 0;
    }

	/**
     * @desc 获取报价单项目列表
 	 * @author 张玉良
     * @param array $condition
     * @return array
     */
    public function getItemList($condition) {
    	$where = $this->getWhere($condition);

		try {
			$count = $this->getCount($condition);
			$list = $this->alias('a')
					->join($this->joinTable1, 'LEFT')
					->join($this->joinTable2, 'LEFT')
					->field($this->joinField)
					->where($where)
					->order('a.id DESC')
					->select();

			if($list){
				$results['code'] = '1';
				$results['message'] = '成功！';
				$results['count'] = $count;
				$results['data'] = $list;
			}else{
				$results['code'] = '-101';
				$results['message'] = '没有找到相关信息!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
    }

	/**
	 * @desc 添加报价单SKU详情
	 * @author 张玉良
	 * @param array $condition
	 * @return array
	 */
	public function addItem($condition) {
		$data = $this->create($condition);
		$data['status'] = !empty($condition['status'])?$condition['status']:'ONGOING';
		$data['created_at'] = time();

		try {
			$id = $this->add($data);

			if($id){
				$results['code'] = '1';
				$results['message'] = '添加成功！';
				$results['data'] = $id;
			}else{
				$results['code'] = '-101';
				$results['message'] = '添加失败!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
	}

	/**
	 * @desc 获取报价单SKU详情
	 * @author 张玉良
	 * @param array $condition
	 * @return array
	 */
	public function getDetail($condition) {
		if(!empty($condition['id'])){
			$where['id'] = $condition['id'];
		}else{
			$results['code'] = '-103';
			$results['message'] = '没有ID!';
			return $results;
		}

		try {
			$info = $this->where($where)->find();

			if($info){
				$results['code'] = '1';
				$results['message'] = '成功！';
				$results['data'] = $info;
			}else{
				$results['code'] = '-101';
				$results['message'] = '没有找到相关信息!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
	}

	/**
	 * @desc 修改报价单SKU
	 * @author 张玉良
	 * @param array $where , $condition
	 * @return array
	 */
	public function updateItem($condition = []) {
		$data = $this->create($condition);
		if(!empty($condition['id'])){
			$where['id'] = $condition['id'];
		}else{
			$results['code'] = '-103';
			$results['message'] = '没有ID!';
			return $results;
		}
		$data['updated_at'] = $this->getTime();

		try {
			$id = $this->where($where)->save($data);
			if($id){
				$results['code'] = '1';
				$results['message'] = '修改成功！';
			}else{
				$results['code'] = '-101';
				$results['message'] = '修改失败!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
	}

	/**
	 * @desc 删除报价单SKU
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function delItem($condition = []) {
		if(!empty($condition['id'])){
			$where['id'] = array('in',explode(',',$condition['id']));
		}else{

		}

		try {
			$id = $this->where($where)->save(['deleted_flag' => 'Y']);
			if($id){
				$results['code'] = '1';
				$results['message'] = '成功！';
			}else{
				$results['code'] = '-101';
				$results['message'] = '删除失败!';
			}
			return $results;
		} catch (Exception $e) {
			$results['code'] = $e->getCode();
			$results['message'] = $e->getMessage();
			return $results;
		}
	}

	/**
	 * 返回格式化时间
	 * @author zhangyuliang
	 */
	public function getTime() {
		return date('Y-m-d h:i:s',time());
	}
}
