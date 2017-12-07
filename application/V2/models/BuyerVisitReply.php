<?php
/**
 * 需求反馈
 * User: linkai
 * Date: 2017/11/30
 * Time: 15:26
 */
class BuyerVisitReplyModel extends PublicModel{
    //put your code here
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_visit_reply';

    public function __construct() {
        parent::__construct();
    }

    public function getReplyById($data){
        $id = $data['visit_id'];
        $length = isset($_input['pagesize']) ? intval($_input['pagesize']) : 20;
        $current_no = isset($_input['current_no']) ? intval($_input['current_no']) : 1;
        $condition = [
            'visit_id' => $id
        ];
        try{
            //总记录数
            $total = $this->field('id')->where($condition)->count();
            $data = [
                'current_no' => $current_no,
                'pagesize' => $length,
                'total' => $total,
                'result' => []
            ];
            if($total<=0){
                return $data;
            }
            $id_ary = $this->field('id')->where($condition)->order('id')->limit(($current_no-1)*$length,$length)->select();
            $ids = '';
            foreach($id_ary as $r){
                $ids.= ','.$r['id'];
            }
            $ids = substr($ids,1);
            $condition['id'] = ['in', $ids];
            $result = $this->field('id,visit_id,visit_reply,created_by,created_at')->where($condition)->select();
            if($result){
                $userModel = new UserModel();
                //$bvrModel = new BuyerVisitReplyModel();
                foreach($result as $index => $r){
                    $userInfo = $userModel->field('user_no,name,mobile')->where(['id'=>$r['created_by']])->find();
                    $result[$index]['user_no'] = $userInfo['user_no'] ? $userInfo['user_no'] : null;
                    $result[$index]['name'] = $userInfo['name'] ? $userInfo['name'] : null;
                    $result[$index]['mobile'] = $userInfo['mobile'] ? $userInfo['mobile'] : null;
                  /*  $result[$index]['reply'] = 'N';
                    $result[$index]['reply_time'] = null;
                    $bvrInfo = $bvrModel->field('created_at')->where(['visit_id'=>$r['id']])->order('created_at')->find();
                    if($bvrInfo){
                        $result[$index]['reply'] = 'Y';
                        $result[$index]['reply_time'] = $bvrInfo['created_at'];
                    }*/
                }
            }
            $data['result'] = $result ? $result : [];
            return $data;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisitReply】getReplyById:' . $e , Log::ERR);
            return false;
        }
    }


    /**
     * 编辑（新增/修改）
     * @param array $_input
     * @return bool
     */
    public function edit($_input = []){
        if(!isset($_input['visit_id']) || empty($_input['visit_id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '拜访记录ID不能为空');
        }

        if(!isset($_input['visit_reply']) || empty($_input['visit_reply'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '内容不能为空');
        }

        $userInfo = getLoinInfo();
        $data = $where = [];
        $data['visit_id'] = $_input['visit_id'];
        $data['visit_reply'] = $_input['visit_reply'];
        try{
            if(isset($_input['id']) && !empty($_input['id'])) {
                //$data['deleted_flag'] = self::DELETED_N;
                $where[ 'id' ] = intval( $_input[ 'id' ] );
                if ( $this->where( $where )->save( $data ) ) {
                    $result = $_input[ 'id' ];
                }
            }else{
                $data['created_by'] = $userInfo['id'] ? $userInfo['id'] : null;
                $data['created_at'] = date('Y-m-d H:i:s',time());
                //$data['deleted_flag'] =  self::DELETED_N;
                $result = $this->add($data);
            }
            return $result ? $result : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】edit:' . $e , Log::ERR);
            return false;
        }
    }

}