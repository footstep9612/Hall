<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/1
 * Time: 14:56
 */
class ProductChecklogModel extends PublicModel{
    protected $dbName = 'erui2_goods'; //数据库名称
    protected $tableName = 'product_check_log'; //数据表表名

    const STATUS_PASS  = 'PASS';    //-通过
    const STATUS_REJECTED = 'REJECTED';    //-不通过

    /**
     * 商品审核记录写入
     * @param array $condition
     * @return bool
     */
    public function takeRecord($condition,$checkStatus) {
        if(empty($condition) || empty($checkStatus)) {
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        switch($checkStatus) {
            case 'VALID':
                $status = 'PASS';
                break;
            case 'INVALID':
                $status = 'REJECTED';
                break;
        }
        $arr = array();
        $results = array();
        if($condition && is_array($condition)) {
            try {
                foreach ($condition as $item) {
                    $data = [
                        'spu' => !empty($item['spu']) ? $item['spu'] : '',
                        'sku' => !empty($item['sku']) ? $item['sku'] : '',
                        'lang' => !empty($item['lang']) ? $item['lang'] : '',
                        'lang' => $item['lang'],
                        'status' => $status,
                        'remarks' => !empty($item['remarks']) ? $item['remarks'] : '',
                        'approved_by' => !empty($userInfo['id']) ? $userInfo['id'] : '',
                        'approved_at' => date('Y-m-d H:i:s', time())
                    ];
                    $arr[] = $data;
                }
                $res = $this->addAll($arr);
                if ($res) {
                    $results['code'] = '1';
                    $results['message'] = '成功！';
                } else {
                    $results['code'] = '-101';
                    $results['message'] = '失败!';
                }
                return $results;
            } catch (Exception $e) {
                $results['code'] = $e->getCode();
                $results['message'] = $e->getMessage();
                return $results;
            }
        }
        return false;
    }

    /**
     * 商品审核记录查询
     * @param  $sku
     * @return array
     * @updater link  2017-08-05
     * @updateDetail  由原来单一对sku查询改为按条件查询，兼容原功能。
     */
    public function getRecord($condition = [], $fields = '') {
        if(empty($condition)) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
        $where = [];
        if(is_array($condition)) {
            if(isset($condition['sku'])) {
                $where['sku'] = $condition['sku'];
            }
            if(isset($condition['spu'])) {
                $where['spu'] = $condition['spu'];
            }
            if(isset($condition['lang']) && in_array(strtolower($condition['lang']),array('zh','en','es','ru'))) {
                $where['lang'] = strtolower($condition['lang']);
            }
        }else{
            $where['sku'] = $condition;
        }

        if(empty($fields)) {
            $fields = 'spu,sku,lang,status,remarks,approved_by,approved_at';
        }elseif(is_array($fields)){
            $fields = implode(',',$fields);
        }

        if(redisHashExist('checkLog',md5(serialize($where).$fields))){
            return json_decode(redisHashGet('checkLog',md5(serialize($where).$fields)),true);
        }

        try{
            $result = $this->field($fields)->where($where)->order('approved_at DESC')->select();
            if($result){
                $employee = new EmployeeModel();
                for($i=0;$i<count($result);$i++){
                    $approveder = $employee->getInfoByCondition(array('id'=>$result[$i]['approved_by']), 'id,name,name_en');
                    if($approveder && isset($approveder[0])) {
                        $result[$i]['approved_by'] = $approveder[0];
                    }
                }
                redisHashSet('checkLog',md5(serialize($where).$fields),json_encode($result));
                return $result;
            }
            return array();
        }catch (Exception $e) {
            return false;
        }
    }

}