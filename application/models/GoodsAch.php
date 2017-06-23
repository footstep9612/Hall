<?php
/**
* Description of GoodsAchModel
*
 * @author  klp
*/
class GoodsAchModel extends PublicModel
{
    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attach'; //数据表表名

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
        $key_redis = md5(json_encode($where));
        if(redisExist($key_redis)) {
            $result = json_decode(redisGet($key_redis));
            return $result ? $result : array();
        }

        $field = 'attach_type,attach_name,attach_url,sort_order,status';
        try {
            $result = $this->field($field)->where($where)->select();
            if ($result) {
                $data = array();
                //附件分组
                /**
                 * SMALL_IMAGE-小图；
                 * MIDDLE_IMAGE-中图；
                 * BIG_IMAGE-大图；
                 * DOC-文档（包括图片和各种文档类型）
                 * */
                foreach ($result as $val) {
                    $group = 'OTHERS';
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
                    $data[$group][] = $val;
                }
                redisSet($key_redis,json_encode($data));
                return $data;
            }
        }catch (Exception $e){
            return array();
        }
    }


}