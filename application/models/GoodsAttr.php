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
     * 编辑商品属性查询
     * @param null $where string 条件
     * @return mixed
     */
    public function getAttrBySku($where, $lang)
    {
        $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
        $where['status'] = self::STATUS_VALID;
        $field = 'id,lang,spu,attr_name,input_type,value_type,value_unit,options,input_hint,attr_group';
        $result = $this->field($field)
                       ->where($where)
                       ->select();

        //获取对应产品属性并分组
        $product = new ProductAttrModel();
        $spu = $result[0]['spu'];
        $p_attrs = $product->getAttrBySpu($spu);

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
                $res[$lang][$group] = $val;
            }
            $result = $res;
        }
        //合并sku/spu数组属性
        $data = array();
        $data['sku_attrs'] = $result;
        $data['spu_attrs'] = $p_attrs;
        if($data){
            return $data;
        } else {
            return array();
        }
    }

    /**
     * 添加数据
     * @param $data
     * @return mixed
     */

    public function CreateInfo($data){
        $sta = $this->add($data);
        if($sta){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 修改数据
     * @param $where
     * @param $data
     * @return bool
     */

    public function UpdatedInfo($where, $data)
    {
        $sta = $this->where($where)
                     ->save($data);
        if($sta){
            return true;
        }else{
            return false;
        }
    }


}