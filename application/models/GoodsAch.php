<?php
/**
* Description of GoodsAchModel
*
 * @author  klp
*/
class GoodsAchModel extends PublicModel
{
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'goods_attach'; //数据表表名

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST';    //测试
    const STATUS_CHECKING = 'CHECKING';  //审核中
    const STATUS_INVALID = 'INVALID';   //无效
    const STATUS_DELETED = 'DELETED';    //-删除

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 根据条件获取商品附件
     * @param null $where string 条件
     * @return mixed
     */
    public function getInfoByAch($where)
    {
        $field = 'attach_type,attach_name,attach_url,sort_order';
        try {
            $result = $this->field($field)
                            ->where($where)
                            ->select();
            if ($result) {
                //附件分组
                /**
                 * SMALL_IMAGE-小图；
                 * MIDDLE_IMAGE-中图；
                 * BIG_IMAGE-大图；
                 * DOC-文档（包括图片和各种文档类型）
                 * */
                $data = array();
                foreach ($result as $val) {
                    switch ($val['attach_type']) {
                        case 'SMALL_IMAGE':
                            $group = 'SMALL_IMAGE';
                            break;
                        case 'MIDDLE_IMAGE':
                            $group = 'MIDDLE_IMAGE';
                            break;
                        case 'BIG_IMAGE':
                            $group = 'BIG_IMAGE';
                            break;
                        case 'DOC':
                            $group = 'DOC';
                            break;
                        default:
                            $group = 'OTHERS';
                            break;
                    }
                    $data[$group] = $val;
                }
                return $data;
            } else {
                return array();
            }
        }catch (Exception $e){
            return false;
        }
    }


}