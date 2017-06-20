<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
 */
class GoodsAttrModel extends PublicModel
{
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 根据sku条件获取属性值a
     * @param $data
     * @return mixed
     */
    public function AttrInfoBy($sku='',$lang='')
    {
        if($sku=='')
            return false;
        if($lang=='')
            return false;
        $condition = array(
            'sku' => $sku,
            'lang' => $lang,
            'status' => self::STATUS_VALID
        );
        //获取productAttr表名
        $proAttrModel = new ProductAttrModel();
        $pattr_table = $proAttrModel->getTableName();
        //获取本表面
        $this_table = $this->getTableName();


            $field = "$this_table.lang,$this_table.spu,$this_table.attr_name,$this_table.input_type,$this_table.value_type,$this_table.value_unit,$this_table.options,$this_table.input_hint,$this_table.attr_group,$pattr_table.lang,$pattr_table.attr_no,$pattr_table.attr_name,$pattr_table.attr_value_type,$pattr_table.attr_value,$pattr_table.value_unit,$pattr_table.goods_flag,$pattr_table.spec_flag,$pattr_table.logi_flag,$pattr_table.hs_flag";
        try{
            $result = $this->field($field)
                           ->join($pattr_table . " ON $pattr_table.spu = $this_table.spu AND $pattr_table.lang = $this_table.lang", 'LEFT')
                           ->where($condition)
                           ->select();

            if($result){
              return $result;
            } else{
                return array();
            }
        } catch(Exception $e) {
            return false;
        }
    }

    /**
     * 编辑商品属性查询p
     * @param null $where string 条件
     * @return mixed
     */
    public function getAttrBySku($where, $lang='')
    {
        if(''!=$lang){
            $where['lang'] = $lang;
        }
        $where['status'] = self::STATUS_VALID;
        //获取productAttr表名
        $proAttrModel = new ProductAttrModel();
        $pattr_table = $proAttrModel->getTableName();
        //获取本表面
        $this_table = $this->getTableName();

        //关联表查询合并
        $field = "$this_table.lang,$this_table.spu,$this_table.attr_name,$this_table.input_type,$this_table.value_type,$this_table.value_unit,$this_table.options,$this_table.input_hint,$this_table.attr_group,$pattr_table.lang,$pattr_table.attr_no,$pattr_table.attr_name,$pattr_table.attr_value_type,$pattr_table.attr_value,$pattr_table.value_unit,$pattr_table.goods_flag,$pattr_table.spec_flag,$pattr_table.logi_flag,$pattr_table.hs_flag";

        try{
            $result = $this->field($field)
                           ->join($pattr_table . " ON $pattr_table.spu = $this_table.spu AND $pattr_table.lang = $this_table.lang", 'LEFT')
                           ->where($where)
                           ->select();

            //进行属性分组
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
            if($result){
                $res = array();
                foreach($result as $val){
                    switch($val['attr_group']){
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
                    $res['lang'][$group] = $val;
                }
                $result = $res;
            }
            if($result){
                return $result;
            } else {
                return array();
            }
        } catch(Exception $e) {
            return false;
        }
    }

}