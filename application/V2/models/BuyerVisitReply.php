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
                $orgModel = new OrgModel();
                $orgmModel = new OrgMemberModel();
                //$bvrModel = new BuyerVisitReplyModel();
                foreach($result as $index => $r){
                    $userInfo = $userModel->field('user_no,name,mobile')->where(['id'=>$r['created_by']])->find();
                    $result[$index]['user_no'] = $userInfo['user_no'] ? $userInfo['user_no'] : null;
                    $result[$index]['name'] = $userInfo['name'] ? $userInfo['name'] : null;
                    $result[$index]['mobile'] = $userInfo['mobile'] ? $userInfo['mobile'] : null;

                    $morgInfo = $orgmModel->field('org_id')->where(['employee_id' =>$r['created_by']])->find();
                    if($morgInfo){
                        $orgInfo = $orgModel->field('name')->where(['id' => $morgInfo['org_id']])->find();
                    }
                    $result[$index]['org_name'] = $orgInfo ? $orgInfo['name'] : null;

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
                print_r($data);die;
                $result = $this->add($data);
            }
            return $result ? $result : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】edit:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 客户管理-客户信息的统计数据
     * wangs
     */
//    public function singleVisitReplyInfo($buyer_id,$created_by){
//        $cond = "visit.buyer_id=$buyer_id and reply.created_by=$created_by";
//        $info = $this->alias('reply')
//            ->join('erui_buyer.buyer_visit visit on visit.id=reply.visit_id','inner')
//            ->field('reply.reply_at')
//            ->where($cond)
//            ->select();
//        if(empty($info)){
//            $arr['totalReply'] = 0;
//            $arr['week'] = 0;
//            $arr['month'] = 0;
//            $arr['quarter'] = 0;
//            return $arr;
//        }
//        foreach($info as $k => $v){
//            $info[$k]['reply_at'] = substr($v['reply_at'],0,10);
//        }
//        //本周
//        $weekStart = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y")));
//        $weekEnd = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")));
//        //本月
//        $monthStart = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y")));
//        $monthEnd = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y")));
//        //本季度
//        $quarterStart = date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')+1));
//        $quarterEnd = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')+1));
//        //整合数据
//        $weekArr = [];
//        $monthArr = [];
//        $quarterArr = [];
//        foreach($info as $v){
//            if($weekStart <= $v['reply_at'] && $v['reply_at'] <= $weekEnd){
//                $weekArr[]=$v['reply_at'];
//            }
//            if($monthStart <= $v['reply_at'] && $v['reply_at'] <= $monthEnd){
//                $monthArr[]=$v['reply_at'];
//            }
//            if($quarterStart <= $v['reply_at'] && $v['reply_at'] <= $quarterEnd){
//                $quarterArr[]=$v['reply_at'];
//            }
//        }
//        $totalVisit=count($info);    //本周
//        $week=count($weekArr);    //本周
//        $month=count($monthArr);    //本月
//        $quarter=count($quarterArr);    //本季
//        $arr['totalReply'] = $totalVisit;
//        $arr['week'] = $week;
//        $arr['month'] = $month;
//        $arr['quarter'] = $quarter;
//        return $arr;
//    }
}