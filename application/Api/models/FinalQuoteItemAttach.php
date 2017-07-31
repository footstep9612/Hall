<?php
/**
 * @desc 最终报价单明细附件模型
 * @author liujf 2017-06-21
 */
class FinalQuoteItemAttachModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote_item_attach';
    
    public function __construct() {
        parent::__construct();
    }
    
	/**
     * @desc 获取查询条件
 	 * @author liujf 2017-06-17
     * @param $condition array
     * @return $where array
     */
    public function getWhere($condition = array()) {
    	$where = array();
    	
    	if (!empty($condition['id'])) {
			$where['id'] = $condition['id'];
		}
    	
    	if (!empty($condition['quote_no'])) {
            $where['quote_no'] = $condition['quote_no'];
        }

		if (!empty($condition['quote_item_id'])) {
			$where['quote_item_id'] = $condition['quote_item_id'];
		}
    	
    	if (!empty($condition['quote_sku'])) {
            $where['quote_sku'] = $condition['quote_sku'];
        }
        
    	if (!empty($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
    	
    	if (!empty($condition['attach_name'])) {
            $where['attach_name'] = $condition['attach_name'];
        }
    
    	if (!empty($condition['attach_url'])) {
            $where['attach_url'] = $condition['attach_url'];
        }
    	
    	return $where;
    }

	/**
     * @desc 获取记录总数
 	 * @author liujf 2017-06-27
     * @param array $condition 
     * @return int $count
     */
    public function getCount($condition) {
    	$where = $this->getWhere($condition);
    	
    	$count = $this->where($where)->count('id');
    	
    	return $count > 0 ? $count : 0;
    }

	/**
     * @desc 获取报价单明细附件
 	 * @author liujf 2017-06-17
     * @param $condition array
     * @return array
     */
    public function getAttachList($condition = array()) {
    	
    	$where = $this->getWhere($condition);
    	
    	if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
    		return $this->where($where)->page($condition['currentPage'], $condition['pageSize'])->select();
    	} else {
    		return $this->where($where)->select();
    	}
    }

	/**
	 * @desc 添加报价单附件详情
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function addAttach($condition) {
		$data = $this->create($condition);

		return $this->add($data);
	}

	/**
	 * @desc 删除报价单附件
	 * @author zhangyuliang 2017-06-29
	 * @param array $condition
	 * @return array
	 */
	public function delAttach($condition = []) {
		$where = $this->getWhere($condition);

		return $this->where($where)->delete();
	}
}
