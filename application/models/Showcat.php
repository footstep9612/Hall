<?php
/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 15:52
 */
class ShowCatModel extends PublicModel
{
    //状态
    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct()
    {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj=Yaf_Registry::get("config");
        $config_db=$config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'show_cat';

        parent::__construct();
    }

    /**
     * 展示分类列表
     * @param array $condition  条件
     * @param string $field     检索字段
     * @return array|bool
     */
    public function getList($condition=[],$field=''){
        return array();die;
        $field = empty($field) ? 'cat_no,name' :$field ;
        if(empty($condition)){
            $condition['parent_cat_no'] = '';
        }

        //语言默认取en 统一小写
        $condition['lang'] = isset($condition['lang']) ? strtolower($condition['lang']) : ( browser_lang() ? browser_lang() : 'en') ;
        $condition['status'] = self::STATUS_VALID;

        try{
            //后期优化缓存的读取

            //这里需要注意排序的顺序（注意与后台一致）
            $resouce  = $this->field($field)->where($condition)->order('sort_order DESC')->select();
            $data = array(
                'count' => 0,
                'data' => array()
            );
            if($resouce){
                $data['data'] = $resouce;
                $data['count'] = count($resouce);
            }
            return $data;
        }catch (Exception $e){
            return false;
        }
    }



}