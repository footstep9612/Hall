<?php
/**
 *
 */
class CustomerGradeModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'customer_grade';

    public function __construct() {
        parent::__construct();
    }
    public function buyerGradeList($data){
            $info=$this
            ->field('amount,position,year_keep,re_purchase,credit_grade,purchase,enterprise,income,scale')
            ->where(array('deleted_flag'=>'N'))
            ->select();
            $arr=[];
            foreach($info as $k => $v){
                $arr[$k]['customer_grade']=$v['amount'];  //客户等级
                $arr[$k]['created_name']=$v['position'];    //创建人
                $arr[$k]['created_at']=$v['year_keep'];   //创建时间
                $arr[$k]['updated_at']=$v['re_purchase']; //更新时间
                $arr[$k]['customer_admin']=$v['credit_grade'];    //1客户管理员
                $arr[$k]['checked_at']=$v['purchase'];    //1审核时间
                $arr[$k]['status']=$v['enterprise'];  //1状态
            }
            return $arr;
    }
}
