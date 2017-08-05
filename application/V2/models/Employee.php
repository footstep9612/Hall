<?php
/**
 * 员工.
 * User: linkai
 * Date: 2017/8/5
 * Time: 15:22
 */
class EmployeeModel extends PublicModel{
    protected $dbName = 'erui2_sys'; //数据库名称
    protected $tableName = 'employee'; //数据表表名

    const DELETE_Y = 'Y';   //删除
    const DELETE_N = 'N';   //未删除

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件查询信息
     * @param array $condtion 条件数组
     * @param string|array $field 查询字段
     * @return bool|array
     * @author link 2017-08-05
     */
    public function getInfoByCondition($condition = [] ,$field = ''){
        if(empty($condition)) {
            return false;
        }

        if(!isset($condition['deleted_flag'])){
            $condition['deleted_flag'] = self::DELETE_N;
        }

        if(empty($field)) {
            $field = 'id,user_no,email,mobile,password_hash,name,name_en,avatar,gender,mobile2,phone,ext,remarks,status';
        }elseif(is_array($field)){
            $field = implode(',',$field);
        }

        try{
            $result = $this->field($field)->where($condition)->select();
            return $result ? $result : array();
        }catch (Exception $e){
            return false;
        }
    }

}