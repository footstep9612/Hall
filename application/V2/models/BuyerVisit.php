<?php
/**
 * Description of User
 *
 * @author link
 * @desc    客户拜访记录
 */
class BuyerVisitModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_visit';

    const DELETED_Y = 'Y';
    const DELETED_N = 'N';

    const DEMAND_Y = 'Y';
    const DEMAND_N = 'N';

    public function __construct() {
        parent::__construct();
    }


    /**
     * 列表
     * @param array $_input
     * @return array|bool|mixed
     */
    public function getList($_input = []){
        $vtModel = new VisitTypeModel();
        $vpModel = new VisitPositionModel();
        $vlModel = new VisitLevelModel();
        $buyerModel = new BuyerModel();
        $dpModel = new VisitDemadTypeModel();
        $bvrModel = new BuyerVisitReplyModel();
        $length = isset($_input['pagesize']) ? intval($_input['pagesize']) : 20;
        $current_no = isset($_input['current_no']) ? intval($_input['current_no']) : 1;
        $condition = [];
        if(!empty($_input['all_id'])){
            $condition['id']=['in', $_input['all_id']];
        }
        $cond = "1=1";
        //客户名称，客户编码为条件
        if(isset($_input['buyer_name']) || !empty($_input['buyer_name'])){
            $cond .= " and name like '%$_input[buyer_name]%'";
        }
        if(isset($_input['buyer_code']) && !empty($_input['buyer_code'])){
            $cond .= " and buyer_code like '%$_input[buyer_code]%'";
        }
        if(!empty($_input['buyer_name']) || !empty($_input['buyer_code'])){
            $buyer_ids = $buyerModel->field('id')->where($cond)->order('id desc')->select();
            $buyer_id = [];
            foreach($buyer_ids as $v){
                $buyer_id[]=$v['id'];
            }
            $condition['buyer_id']=['in', $buyer_id];
        }
        if(isset($_input['visit_level']) && !empty($_input['visit_level'])){
            $condition['visit_level']=['exp', 'regexp \'"'.$_input['visit_level'].'"\''];
        }
        if(isset($_input['visit_position']) && !empty($_input['visit_position'])){
            $condition['visit_position']=['exp', 'regexp \'"'.$_input['visit_position'].'"\''];
        }
        //	visit_at_start开始时间   visit_at_end结束时间
        $this->_getValue($condition, $_input,'visit_at','between');
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
             $id_ary = $this->field('id')->where($condition)->order('id desc')->limit(($current_no-1)*$length,$length)->select();
             $ids = '';
             foreach($id_ary as $r){
                 $ids.= ','.$r['id'];
             }
             $ids = substr($ids,1);
             $condition['id'] = ['in', $ids];
             $result = $this->field('id,buyer_id,name,phone,visit_at,visit_type,visit_level,visit_position,demand_type,demand_content,visit_objective,visit_personnel,visit_result,is_demand,created_by,created_at')->where($condition)->order('id desc')->select();
             foreach($result as $index => $r){
                 //客户信息
                 $buyInfo = $buyerModel->field('name,buyer_code,buyer_no')->where(array('id'=>$r['buyer_id']))->find();
                 $result[$index]['buyer_name'] = $buyInfo ? $buyInfo['name'] : '';
                 $result[$index]['buyer_code'] = $buyInfo ? $buyInfo['buyer_code'] : '';
                 $result[$index]['buyer_no'] = $buyInfo ? $buyInfo['buyer_no'] : '';
             }
             foreach($result as $index => $r){
                 //业务部门反馈时间
                 $replyInfo = $bvrModel->field('created_at')->where(['visit_id'=>$r['id']])->order('created_at')->find();
                 $result[$index]['reply_time'] =$replyInfo['created_at'];
             }
            foreach($result as $index => $r){
                //业务部门反馈时间
                $replyInfo = $bvrModel->field('created_at')->where(['visit_id'=>$r['id']])->order('created_at')->find();
                $result[$index]['reply_time'] =$replyInfo['created_at'];
            }
            foreach($result as $index => $r){
                //目的拜访类型
                $vtype = json_decode($r['visit_type']);
                $visitTypeInfo = $vtModel->field('name')->where(['id'=>['in',$vtype]])->select();
                $visit_type = '';
                foreach($visitTypeInfo as $info){
                    $visit_type.= '、'.$info['name'];
                }
                $result[$index]['visit_type'] = $visit_type ? mb_substr($visit_type,1) : '';
            }
            foreach($result as $index => $r){
                //职位拜访类型
                $vposition = json_decode($r['visit_position']);
                $vpInfo = $vpModel->field('name')->where(['id'=>['in',$vposition]])->select();
                $visit_position = '';
                foreach($vpInfo as $info){
                    $visit_position.= '、'.$info['name'];
                }
                $result[$index]['visit_position'] = $visit_position ? mb_substr($visit_position,1) : '';

            }
            foreach($result as $index => $r){
                //拜访级别
                $vlevel = json_decode($r['visit_level']);
                $vlInfo = $vlModel->field('name')->where(['id'=>['in',$vlevel]])->select();
                $visit_level = '';
                foreach($vlInfo as $info){
                    $visit_level.= '、'.$info['name'];
                }
                $result[$index]['visit_level'] = $visit_level ? mb_substr($visit_level,1) : '';
            }
            foreach($result as $index => $r){
                //客户需求类型
                $dtype = json_decode($r['demand_type']);
                $dpInfo = $dpModel->field('name')->where(['id'=>['in',$dtype]])->select();
                $demand_type = '';
                foreach($dpInfo as $info){
                    $demand_type.= '、'.$info['name'];
                }
                $result[$index]['demand_type'] = $demand_type ? mb_substr($demand_type,1) : '';
            }
            $data['result'] = $result ? $result : [];
             return $data;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】getList:' . $e , Log::ERR);
            return false;
        }
    }


    /**
     * 需求列表
     * @param array $_input
     * @return array|bool|mixed
     */
    public function getDemadList($_input = []){
        $length = isset($_input['pagesize']) ? intval($_input['pagesize']) : 20;
        $current_no = isset($_input['current_no']) ? intval($_input['current_no']) : 1;
        $condition = [
            'is_demand' => self::DEMAND_Y
        ];
        //根据条件查询用户信息
        $condition_user = [];
        if(isset($_input['name']) && !empty($_input['name'])){
            $condition_user['name'] = trim($_input['name']);
        }
        if(isset($_input['user_no']) && !empty($_input['user_no'])){
            $condition_user['user_no'] = trim($_input['user_no']);
        }
        if(isset($_input['mobile']) && !empty($_input['mobile'])){
            $condition_user['mobile'] = trim($_input['mobile']);
        }
        try{
            if(!empty($condition_user)){
                $userModel = new UserModel();
                $userInfo = $userModel->field('id')->where($condition_user)->find();
                if($userInfo){
                    $condition['created_by'] = $userInfo['id'];
                }else{
                    return [];
                }
            }
            //	visit_at_start开始时间   visit_at_end结束时间
//            if(!empty($_input['created_at_start']) && !empty($_input['created_at_start'])){
                $this->_getValue($condition, $_input,'created_at','between');
//                $ex = $condition['created_at'][1];
//                $exArr = explode(',',$ex);
//                $a = date('Y-m-d H:i:s', strtotime($exArr[0]));
//                $b = date('Y-m-d H:i:s', strtotime($exArr[1])+86400);
//                $condition['created_at']=array(
//                    'between',
//                    "$a,$b"
//                );
//            }
//            if(isset($_input['created_at_start']) && !empty($_input['created_at_start'])){
//                $condition['created_at']=['EGT', $_input['created_at_start']];
//            }
//            if(isset($_input['created_at_end']) && !empty($_input['created_at_end'])){
//                $condition['created_at']=['EGT', $_input['created_at_end']];
//            }
            //总记录数
            $total = $this->field('id')->where($condition)->count();
            $data = [
                'current_no' => $current_no,
                'pagesize' => $length,
                'total' => $total,
                'result' =>[]
            ];
            if($total<=0){
                return $data;
            }
            $id_ary = $this->field('id')->where($condition)->order('id desc')->limit(($current_no-1)*$length,$length)->select();
            $ids = '';
            foreach($id_ary as $r){
                $ids.= ','.$r['id'];
            }
            $ids = substr($ids,1);
            $condition['id'] = ['in', $ids];
            $result = $this->field('id,created_by,created_at')->order('id desc')->where($condition)->select();
            if($result){
                $userModel = new UserModel();
                $bvrModel = new BuyerVisitReplyModel();
                foreach($result as $index => $r){
                    $userInfo = $userModel->field('user_no,name,mobile')->where(['id'=>$r['created_by']])->find();
                    if($userInfo){
                        $result[$index]['user_no'] = $userInfo['user_no'];
                        $result[$index]['name'] = $userInfo['name'];
                        $result[$index]['mobile'] = $userInfo['mobile'];
                    }
                    $result[$index]['reply'] = 'N';
                    $result[$index]['reply_time'] = null;
                    $bvrInfo = $bvrModel->field('created_at')->where(['visit_id'=>$r['id']])->order('created_at desc')->find();
                    if($bvrInfo){
                        $result[$index]['reply'] = 'Y';
                        $result[$index]['reply_time'] = $bvrInfo['created_at'];
                    }
                }
            }
            $data['result'] = $result ? $result : [];
            return $data;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】getDemadList:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 更加id获取详情
     * @param $id
     * @return array|bool|mixed
     */
    public function getInfoById($id,$is_show_name=false){
        $condition = [
            'id' => $id,
        ];
        try{
            $result = $this->field('id,buyer_id,name,phone,visit_at,visit_type,visit_level,visit_position,demand_type,demand_content,visit_objective,visit_personnel,visit_result,is_demand,created_by,created_at')->where($condition)->find();

            if($result){
                //user
                $user_model = new UserModel();
                $userInfo = $user_model->field('name,user_no')->where(['id'=>$result['created_by']])->find();
                $result['created_by_name'] = $userInfo['name'];
                //回复
                $reply = new BuyerVisitReplyModel();
                $replyInfo = $reply->field('visit_reply')->where(['visit_id'=>$result['id']])->find();
                //客户
                $buyer_model = new BuyerModel();
                $buyerInfo = $buyer_model->field('buyer_no,buyer_code,name')->where(['id'=>$result['buyer_id']])->find();
                $result['buyer_name'] = $buyerInfo['name'];
                $result['buyer_no'] = $buyerInfo['buyer_no'];
                $result['buyer_code'] = $buyerInfo['buyer_code'];
                $result['demand_content'] = $result['demand_content'];
                $result['visit_type'] = json_decode( $result['visit_type']);
                $result['visit_level'] = json_decode( $result['visit_level']);
                $result['visit_position'] = json_decode( $result['visit_position']);
                $result['demand_type'] = json_decode( $result['demand_type']);
                $result['visit_reply'] = $replyInfo['visit_reply'];
                if($is_show_name){
                    $vdt_model = new VisitDemadTypeModel();
                    $result['demand_type'] = $vdt_model->field('name')->where(['id'=>['in', $result['demand_type']]])->select();

                    $vp_model = new VisitPositionModel();
                    $result['visit_position'] = $vp_model->field('name')->where(['id'=>['in', $result['visit_position']]])->select();

                    $vl_model = new VisitLevelModel();
                    $result['visit_level'] = $vl_model->field('name')->where(['id'=>['in', $result['visit_level']]])->select();

                    $vt_model = new VisitTypeModel();
                    $result['visit_type'] = $vt_model->field('name')->where(['id'=>['in', $result['visit_type']]])->select();
                }
            }
            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】getInfoById:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 编辑（新增/修改）
     * @param array $_input
     * @return bool
     */
    public function edit($_input = []){
        if(!isset($_input['buyer_id']) || empty($_input['buyer_id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '客户不能为空');
        }

        if(!isset($_input['visit_at']) || empty($_input['visit_at'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请输入拜访时间');
        }

        if(!isset($_input['visit_type']) || empty($_input['visit_type']) || !is_array($_input['visit_type'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请选择目的拜访类型');
        }

        if(!isset($_input['visit_level']) || empty($_input['visit_level']) || !is_array($_input['visit_level'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请选择拜访级别');
        }

        if(!isset($_input['visit_position']) || empty($_input['visit_position']) || !is_array($_input['visit_position'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请选择职位拜访类型');
        }

        if(!isset($_input['demand_type']) || empty($_input['demand_type']) || !is_array($_input['demand_type'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请选择需求反馈种类');
        }

        if(!isset($_input['visit_objective']) || empty($_input['visit_objective'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请输入拜访目的');
        }

        if(!isset($_input['visit_result']) || empty($_input['visit_result'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请输入拜访结果');
        }

        $userInfo = getLoinInfo();
        $data = $where = [];
        $data['visit_at'] = $_input['visit_at'];
        $data['buyer_id'] = $_input['buyer_id'];
        $data['name'] = trim($_input['name']);
        $data['phone'] = trim($_input['phone']);
        $data['visit_type'] = json_encode( $_input['visit_type']);    //目的拜访类型
        $data['visit_level'] = json_encode( $_input['visit_level']);    //拜访级别
        $data['visit_position'] = json_encode( $_input['visit_position']);    //拜访职位
        $data['demand_type'] = json_encode( $_input['demand_type']);    //需求类型

        $data['visit_objective'] = trim($_input['visit_objective']);    //拜访目的
        $data['visit_personnel'] = trim($_input['visit_personnel']);    //拜访陪同人员
        $data['visit_result'] = trim($_input['visit_result']);    //拜访结果
        if(isset($_input['is_demand']) && !empty($_input['is_demand'])){
            $data['is_demand'] = self::DEMAND_Y;    //是否有需求
        }
        $data['demand_content'] = trim($_input['demand_content']);    //需求内容
        //$data['visit_reply'] = trim($_input['visit_reply']);    //需求答复

        try{
            if(isset($_input['id']) && !empty($_input['id'])) {
                //$data['deleted_flag'] = self::DELETED_N;
                $where[ 'id' ] = intval( $_input[ 'id' ] );
                $this->where( $where )->save( $data );
                $result = $_input[ 'id' ];
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

    /**
     * 根据ID删除    --  无deleted_flag是否允许删除待定
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
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】deleteById:' . $e , Log::ERR);
            return false;
        }
    }
    //客户管理搜索列表页获取拜访次数信息-wangs
    public function getVisitCount($ids){
        $arr = [];
        foreach($ids as $k => $id){
            $arr[$k] = $this -> singleVisitInfo($id);
        }
        return $arr;
    }
    //单个客户管理搜索列表页获取拜访次数信息-wangs
    public function singleVisitInfo($id){
        //本周
        $weekStart = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y")));
        $weekEnd = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")));
        //本月
        $monthStart = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y")));
        $monthEnd = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y")));
        //本季度
        $quarterStart = date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')+1));
        $quarterEnd = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')+1));
        //整合数据
        $sqlT = "select visit_at from erui_buyer.buyer_visit where buyer_id =".$id;
        $info = $this->db->query($sqlT);
        $weekArr = [];
        $monthArr = [];
        $quarterArr = [];
        foreach($info as $v){
            if($weekStart <= $v['visit_at'] && $v['visit_at'] <= $weekEnd){
                $weekArr[]=$v['visit_at'];
            }
            if($monthStart <= $v['visit_at'] && $v['visit_at'] <= $monthEnd){
                $monthArr[]=$v['visit_at'];
            }
            if($quarterStart <= $v['visit_at'] && $v['visit_at'] <= $quarterEnd){
                $quarterArr[]=$v['visit_at'];
            }
        }
        $totalVisit=count($info);    //本周
        $week=count($weekArr);    //本周
        $month=count($monthArr);    //本月
        $quarter=count($quarterArr);    //本季
        $arr['totalVisit'] = $totalVisit;
        $arr['week'] = $week;
        $arr['month'] = $month;
        $arr['quarter'] = $quarter;
        return $arr;
    }
    public function singleVisitDemandInfo($buyer_id){
        $cond = "buyer_id=$buyer_id  and is_demand='Y'";
        $info = $this
            ->field('visit_at')
            ->where($cond)
            ->select();
        if(empty($info)){
            $arr['totalDemand'] = 0;
            $arr['week'] = 0;
            $arr['month'] = 0;
            $arr['quarter'] = 0;
            return $arr;
        }
        foreach($info as $k => $v){
            $info[$k]['visit_at'] = substr($v['visit_at'],0,10);
        }
        //本周
        $weekStart = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y")));
        $weekEnd = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y")));
        //本月
        $monthStart = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y")));
        $monthEnd = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y")));
        //本季度
        $quarterStart = date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')+1));
        $quarterEnd = date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y')+1));
        //整合数据
        $weekArr = [];
        $monthArr = [];
        $quarterArr = [];
        foreach($info as $v){
            if($weekStart <= $v['visit_at'] && $v['visit_at'] <= $weekEnd){
                $weekArr[]=$v['visit_at'];
            }
            if($monthStart <= $v['visit_at'] && $v['visit_at'] <= $monthEnd){
                $monthArr[]=$v['visit_at'];
            }
            if($quarterStart <= $v['visit_at'] && $v['visit_at'] <= $quarterEnd){
                $quarterArr[]=$v['visit_at'];
            }
        }
        $totalVisit=count($info);    //本周
        $week=count($weekArr);    //本周
        $month=count($monthArr);    //本月
        $quarter=count($quarterArr);    //本季
        $arr['totalDemand'] = $totalVisit;
        $arr['week'] = $week;
        $arr['month'] = $month;
        $arr['quarter'] = $quarter;
        return $arr;
    }

    /**
     * @param $data
     */
    public function buyerVisitStatisList($data){
        $cond = "1=1";
        if(!empty($data['buyer_name'])){
            $cond .= " and buyer.name like '%$data[buyer_name]%'";  //客户名称
        }
        if(!empty($data['buyer_code'])){
            $cond .= " and buyer.buyer_code like '%$data[buyer_code]%'";  //客户代码
        }
        if(!empty($data['visit_level'])){
            $cond .= " and visit.visit_level=$data[visit_level]";  //拜访级别=
        }
        if(!empty($data['visit_position'])){
            $cond .= " and visit.visit_position=$data[visit_position]";  //职位拜访类型=
        }
        if(!empty($data['visit_start_date'])){
            $cond .= " and visit.visit_at >= '$data[visit_start_date]'";  //拜访开始时间=
        }
        if(!empty($data['visit_end_date'])){
            $cond .= " and visit.visit_at <= '$data[visit_end_date]'";  //拜访结束时间=
        }
        $field = 'buyer.id,buyer.name,buyer.buyer_code';
        $fieldArr = array(
            'visit_at', //拜访时间
            'visit_type', //目的拜访类型
            'visit_position', //职位拜访类型
            'visit_level', //拜访级别
            'demand_type', //客户需求类别
        );
        foreach($fieldArr as $v){
            $field .= ',visit.'.$v;
        }
        $info = $this->alias('visit')
            ->join('erui_buyer.buyer buyer on visit.buyer_id=buyer.id','inner')
            ->field($field)
            ->where($cond)
            ->order('buyer.id desc,visit.id desc')
            ->select();
        return $info;
    }

    //excel导出
    public function exportStatisVisit($data){
        //整理数据,获取文件路径
        $excelDir = $this->getVisitStatiaList($data,$length = 1000);
        if(!is_array($excelDir)){
            return false;
        }
        if(count($excelDir)==1){    //单个excel文件
            $excelName = $excelDir[0];
            $data['tmp_name'] = $excelName;
            $data['type'] = 'application/excel';
            $data['name'] = pathinfo($excelName, PATHINFO_BASENAME);
        }else{
            $excelDir = dirname($excelDir[0]);  //获取目录,多个excel文件,压缩打包
            ZipHelper::zipDir($excelDir, $excelDir . '.zip');   //压缩文件
            $data['tmp_name'] = $excelDir . '.zip';
            $data['type'] = 'application/excel';
            $data['name'] = pathinfo($excelDir . '.zip', PATHINFO_BASENAME);
        }
        //把导出的文件上传到文件服务器指定目录位置
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $fileId = postfile($data, $url);    //上传到fastDFSUrl访问地址,返回name和url
        //删除文件和目录
        if(file_exists($excelName)){
            unlink($excelName); //删除文件
            ZipHelper::removeDir(dirname($excelName));    //清除目录
        }
        if(file_exists($excelDir . '.zip')){
            unlink($excelDir . '.zip'); //删除压缩包
            ZipHelper::removeDir($excelDir);    //清除目录
        }
        if ($fileId) {
            return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
        }
    }
    /**
     * 整理数据,获取文件路径
     * @param array $_input
     * 统计excel导出数据
     * 返回数组,已上传到服务器临时路径
     */
    public function getVisitStatiaList($data = [],$length = 1000){
        $condition = $this->getVisitOfCond($data);
        if($condition === false){
            return false;   //该条件下客户信息为空数据返回空
        }
        $total = $this->field('id')->where($condition)->count();
        if($total==0){
            return false;   ///该条件下拜访记录为空数据
        }
        $i = 0;
        do {
            //按条件获取拜访记录数据
            $result = $this->condGetVisitData($condition,$i,$length);
            $info = $this->getVisitStatisData($result); //整理excel导出的数据
            if($i==0){
                $excelName = 'visit';
            }else{
                $excelName = 'visit_'.($i/$length);
            }
            $excelDir[] = $this->exportModel($excelName,$info); //导入excel,获取excel临时文件路径信息
            $i = $i+$length;
            $total =$total-$length;
        } while ($total > 0);
        return $excelDir;   //返回数组,已上传到服务器临时路径
    }
    /**
     * sheet名称 $sheetName
     * execl导航头 $tableheader
     * execl导出的数据 $data
     * wangs
     */
    public function exportModel($excelName,$data){
        ini_set("memory_limit", "1024M"); // 设置php可使用内存
        set_time_limit(0);  # 设置执行时间最大值
        //存放excel文件目录
        $excelDir = MYPATH.DS.'public'.DS.'tmp'.DS.'excelvisit';
        if (!is_dir($excelDir)) {
            mkdir($excelDir,0777,true);
        }
        //创建对象
        $excel = new PHPExcel();
        $objActSheet = $excel->getActiveSheet();
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle('sheet1');
        //填充表头信息
        $letter = range(A,Z);
        $tableheader = array('序号','客户名称','客户代码（CRM）','拜访时间','目的拜访类型','职位拜访类型','拜访级别','客户需求类别');
        for($i = 0;$i < count($tableheader);$i++) {
            //单独设置D列宽度为20
            $objActSheet->getColumnDimension($letter[$i])->setWidth(20);
            $objActSheet->setCellValue("$letter[$i]1","$tableheader[$i]");
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(10);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        //填充表格数据信息
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $objActSheet->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }
        //创建Excel输入对象
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save($excelDir.'/'.$excelName.'.xlsx');    //文件保存
        return $excelDir.'/'.$excelName.'.xlsx';    //返回excel上传服务器的路径信息
    }
    /**
     * @整合excel导出的数据格式
     * wangs
     * @return array
     */
    public function getVisitStatisData($data){
        //整合数据
        $arr=array();
        foreach($data as $k => $v){
            $arr[$k]['id'] = $v['id'];    //序号
            $arr[$k]['buyer_name'] = $v['buyer_name'];    //客户名称
            $arr[$k]['buyer_code'] = $v['buyer_code'];    //客户代码（CRM）
            $arr[$k]['visit_at'] = $v['visit_at'];    //拜访时间
            $arr[$k]['visit_type'] = $v['visit_type'];    //目的拜访类型
            $arr[$k]['visit_position'] = $v['visit_position'];    //职位拜访类型
            $arr[$k]['visit_level'] = $v['visit_level'];    //拜访级别
            $arr[$k]['demand_type'] = $v['demand_type'];    //客户需求类别
        }
        return $arr;
    }
    /**按条件获取拜访记录的数据列表
     * @条件 $condition
     * @每页数据条数 $pageSize
     * @数据偏移量 $offset
     * wangs
     */
    public function condGetVisitData($condition = [],$offset = 0,$pageSize = 10){
        $vtModel = new VisitTypeModel();    //拜访类型
        $vpModel = new VisitPositionModel();    //拜访位置类型
        $vlModel = new VisitLevelModel();   //拜访级别
        $buyerModel = new BuyerModel(); //客户
        $dpModel = new VisitDemadTypeModel();   //需求类型
        $bvrModel = new BuyerVisitReplyModel(); //拜访回复记录
        $result = $this->field('id,buyer_id,name,phone,visit_at,visit_type,visit_level,visit_position,demand_type,demand_content,visit_objective,visit_personnel,visit_result,is_demand,created_by,created_at')
            ->where($condition)
            ->limit($offset,$pageSize)
            ->select();
        foreach($result as $index => $r){
            //客户信息
            $buyInfo = $buyerModel->field('name,buyer_code,buyer_no')->where(array('id'=>$r['buyer_id']))->find();
            $result[$index]['buyer_name'] = $buyInfo ? $buyInfo['name'] : '';
            $result[$index]['buyer_code'] = $buyInfo ? $buyInfo['buyer_code'] : '';
            $result[$index]['buyer_no'] = $buyInfo ? $buyInfo['buyer_no'] : '';
        }
        foreach($result as $index => $r){
            //业务部门反馈时间
            $replyInfo = $bvrModel->field('created_at')->where(['visit_id'=>$r['id']])->order('created_at')->find();
            $result[$index]['reply_time'] =$replyInfo['created_at'];
        }
        foreach($result as $index => $r){
            //业务部门反馈时间
            $replyInfo = $bvrModel->field('created_at')->where(['visit_id'=>$r['id']])->order('created_at')->find();
            $result[$index]['reply_time'] =$replyInfo['created_at'];
        }
        foreach($result as $index => $r){
            //目的拜访类型
            $vtype = json_decode($r['visit_type']);
            $visitTypeInfo = $vtModel->field('name')->where(['id'=>['in',$vtype]])->select();
            $visit_type = '';
            foreach($visitTypeInfo as $info){
                $visit_type.= '、'.$info['name'];
            }
            $result[$index]['visit_type'] = $visit_type ? mb_substr($visit_type,1) : '';
        }
        foreach($result as $index => $r){
            //职位拜访类型
            $vposition = json_decode($r['visit_position']);
            $vpInfo = $vpModel->field('name')->where(['id'=>['in',$vposition]])->select();
            $visit_position = '';
            foreach($vpInfo as $info){
                $visit_position.= '、'.$info['name'];
            }
            $result[$index]['visit_position'] = $visit_position ? mb_substr($visit_position,1) : '';

        }
        foreach($result as $index => $r){
            //拜访级别
            $vlevel = json_decode($r['visit_level']);
            $vlInfo = $vlModel->field('name')->where(['id'=>['in',$vlevel]])->select();
            $visit_level = '';
            foreach($vlInfo as $info){
                $visit_level.= '、'.$info['name'];
            }
            $result[$index]['visit_level'] = $visit_level ? mb_substr($visit_level,1) : '';
        }
        foreach($result as $index => $r){
            //客户需求类型
            $dtype = json_decode($r['demand_type']);
            $dpInfo = $dpModel->field('name')->where(['id'=>['in',$dtype]])->select();
            $demand_type = '';
            foreach($dpInfo as $info){
                $demand_type.= '、'.$info['name'];
            }
            $result[$index]['demand_type'] = $demand_type ? mb_substr($demand_type,1) : '';
        }
        return $result;
    }
    /**
     * 获取拜访记录搜索条件
     * wangs
     */
    public function getVisitOfCond($data = [])
    {
        $condition = [];
        //按拜访记录id为条件
        if (!empty($data['all_id'])) {
            $condition['id'] = ['in', $data['all_id']];
        }
        //客户名称或客户CRM编码为条件
        $cond = ' 1=1';
        if (isset($data['buyer_name']) || !empty($data['buyer_name'])) {  //客户名称
            $cond .= " and name like '%$data[buyer_name]%'";
        }
        if (isset($data['buyer_code']) && !empty($data['buyer_code'])) {  //客户code
            $cond .= " and buyer_code like '%$data[buyer_code]%'";
        }
        if (!empty($data['buyer_name']) || !empty($data['buyer_code'])) { //
            $buyer_ids = $buyerModel->field('id')->where($cond)->order('id desc')->select();
            if (empty($buyer_ids)) {
                return false;   //数据为空
            }
            $buyer_id = [];
            foreach ($buyer_ids as $v) {
                $buyer_id[] = $v['id'];
            }
            $condition['buyer_id'] = ['in', $buyer_id];
        }
        if (isset($data['visit_level']) && !empty($data['visit_level'])) {    //拜访级别
            $condition['visit_level'] = ['exp', 'regexp \'"' . $data['visit_level'] . '"\''];
        }
        if (isset($data['visit_position']) && !empty($data['visit_position'])) {  //拜访职位类型
            $condition['visit_position'] = ['exp', 'regexp \'"' . $data['visit_position'] . '"\''];
        }
        //	拜访时间visit_at_start开始时间   visit_at_end结束时间条件
        $this->_getValue($condition, $data, 'visit_at', 'between'); //搜索条件end
        return $condition;
    }
}