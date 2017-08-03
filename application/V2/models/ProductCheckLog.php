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
    public function takeRecord($condition,$status) {
        if(empty($condition) || empty($status)) {
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();

        $arr = array();
        $results = array();
        if($condition && is_array($condition)) {
            try {
                foreach ($condition as $item) {
                    $data = [
                        'spu' => isset($item['spu']) ? $item['spu'] : '',
                        'sku' => isset($item['sku']) ? $item['sku'] : '',
                        'lang' => isset($item['lang']) ? $item['lang'] : '',
                        'status' => $status,
                        'remarks' => isset($item['remarks']) ? $item['remarks'] : '',
                        'approved_by' => isset($userInfo['id']) ? $userInfo['id'] : '',
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
     */
    public function getRecord($sku){
        if(empty($sku)) {
            jsonReturn('',MSG::ERROR_PARAM,MSG::ERROR_PARAM);
        }
        $where = array('sku'=>$sku);
        $fields = 'spu, sku, status, remarks, approved_by, approved_at';
        try{
            $result = $this->field($fields)->where($where)->order('approved_at DESC')->select();
            if($result){
                return $result;
            }
            return array();
        }catch (Exception $e) {
            return false;
        }
    }

}