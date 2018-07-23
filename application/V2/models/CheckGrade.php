<?php
/**
 *
 */
class CheckGradeModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'check_grade';

    public function __construct() {
        parent::__construct();
    }
    //审核记录
    public function AddCheckGrade($data){
        $res=$this->add($data);
        return $res;

        try {

        } catch (Exception $e) {
            print $e->getMessage();
            exit();
        }
        print_r($this->getLastSql());die;
    }
}
