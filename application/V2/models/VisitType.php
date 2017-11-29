<?php
/**
 * Description of User
 *
 * @author link
 * @desc 目的拜访类型
 */
class VisitTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_config';
    protected $tableName = 'visit_type';

    const DELETED_Y = 'Y';
    const DELETED_N = 'N';
    const SHOW_Y = 'Y';
    const SHOW_N = 'N';

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
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【VisitType】getList:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 更加id获取详情
     * @param $id
     * @return array|bool|mixed
     */
    public function getInfoById($id){
        $condition = [
            'id' => $id,
        ];
        try{
            $result = $this->field('id,name,is_show,created_by,created_at')->where($condition)->find();
            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【VisitType】getInfoById:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 编辑（新增/修改）
     * @param array $_input
     * @return bool
     */
    public function edit($_input = []){
        if(!isset($_input['name']) || empty($_input['name'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请输入目的拜访类型');
        }

        $userInfo = getLoinInfo();
        $where = [];
        $data = [];
        $data['name'] = $_input['name'];
        $data['is_show'] = $_input['is_show'];
        try{
            if(isset($_input['id']) && !empty($_input['id'])){
                $data['deleted_flag'] = self::DELETED_N;
                $where['id'] = intval($_input['id']);
                if($this->where($where)->save($data)){
                   $result = $_input['id'];
                }
            }else{
                $data['created_by'] = $userInfo['id'] ? $userInfo['id'] : null;
                $data['created_at'] = date('Y-m-d H:i:s',time());
                $data['deleted_flag'] =  self::DELETED_N;
                $result = $this->add($data);
            }
            return $result ? $result : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【VisitType】edit:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 根据ID删除
     * @param string $id
     * @return bool
     */
    public function deleteById($id){
        if(empty($id)){
            return false;
        }
        if(is_array($id)){
            $condition = [ 'id' => ['in', $id] ];
        }else{
            $condition = [ 'id' => $id ];
        }
        try{
            $data = ['deleted_flag' => self::DELETED_Y];
            $result = $this->where($condition)->save($data);
            return $result ? true : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【VisitType】deleteById:' . $e , Log::ERR);
            return false;
        }
    }

}
