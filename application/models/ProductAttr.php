<?php
/**
 * ProductAttrModel
 * klp
 */
class ProductAttrModel extends PublicModel{
    //数据库 表映射
    protected $dbName = 'erui_db_ddl_goods';
    protected $tableName = 'product_attr';

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 根据spu条件获取属性值a
     * @param $data
     * @return mixed
     */

    public function AttrInfoBy($spu='',$lang='')
    {
        if($spu=='')
            return false;
        if($lang=='')
            return false;
        $field = 'attr_no,attr_name,attr_value_type,attr_value,value_unit,goods_flag,spec_flag,logi_flag,hs_flag';
        $condition = array(
            'spu' => $spu,
            'lang' => $lang,
            'status' => self::STATUS_VALID
        );
        try{
            $result = $this->field($field)->where($condition)->select();
            if($result){
                return $result;
            } else{
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    /**k
     * 根据spu获取属性
     * @param string $spu
     * @param string $lang
     * @return array|bool|mixed
     */
    public function getAttrBySpu($spu='',$lang=''){
        if($spu=='')
            return false;

        $field = 'lang,attr_no,attr_name,attr_value_type,attr_value,value_unit,goods_flag,spec_flag,logi_flag,hs_flag';
        $condition = array(
            'spu' => $spu,
            'status' => self::STATUS_VALID
        );
        if($lang!=''){
            $condition['lang'] = $lang;
        }
        $result = $this->field($field)->where($condition)->order('created_at DESC')->select();

        if($result){
            //按语言树形结构
            $data = array();
            foreach($result as $item){
                /**
                 * 属性分组:
                 *   Specs - 规格
                 *   Technical Parameters - 技术参数
                 *   Executive Standard - 技术标准
                 *   Product Information - 简要信息
                 *   Quatlity Warranty - 质量保证
                 *   Others - 其他属性
                 *  Image - 附件
                 *  Documentation - 技术文档　
                 */
                switch($item['attr_group']){
                    case 'Specs':
                        $group = 'Specs';
                        break;
                    case 'Technical Parameters':
                        $group = 'Technical Parameters';
                        break;
                    case 'Executive Standard':
                        $group = 'Executive Standard';
                        break;
                    case 'Product Information':
                        $group = 'Product Information';
                        break;
                    case 'Quatlity Warranty':
                        $group = 'Quatlity Warranty';
                        break;
                    case 'Image':
                        $group = 'Image';
                        break;
                    case 'Documentation':
                        $group = 'Documentation';
                        break;
                    default:
                        $group = 'others';
                        break;
                }
                $data['lang'][$group] = $item;
            }
            $result = $data;
        }
        return $result ? $result : array();
    }
}