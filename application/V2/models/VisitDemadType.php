<?php
/**
 * Description of User
 *
 * @author link
 * @desc 需求种类
 */
class VisitDemadTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_config';
    protected $tableName = 'visit_demand_type';

    const DELETED_Y = 'Y';
    const DELETED_N = 'N';

    public function __construct() {
        parent::__construct();
    }


    /**
     * 列表
     * @param array $_input
     * @return array|bool|mixed
     */
    public function getList($_input = []){
        $length = isset($_input['pagesize']) ? intval($_input['pagesize']) : 20;
        $current_no = isset($_input['current_no']) ? intval($_input['current_no']) : 1;
        $condition = [
            'deleted_flag' => self::DELETED_N
        ];
        try{
            $result = $this->field('id,name,is_show,created_by,created_at')->where($condition)->limit(($current_no-1)*$length, $length)->select();
            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【VisitDemadType】getList:' . $e , Log::ERR);
            return false;
        }
    }


    public function getInfoById($id){
        $condition = [
            'id' => $id
        ];
        try{
            $result = $this->field('id,name,is_show,created_by,created_at')->where($condition)->find();
            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【VisitDemadType】getList:' . $e , Log::ERR);
            return false;
        }
    }


}
