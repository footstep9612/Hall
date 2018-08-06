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
    public function getList($data = []){
//                $data=array(
//            'created_by'=>37850,
//            'admin'=>array(
//                'role'=>array(
//                    'CRM客户管理1','area-customers1','201711242','A001','A012','A013','查看客户管理所有菜单','A015'
//                ),
//                'country'=>array(
//                    'Russia','Malaysia','Myanmar','Japan','India'
//                )
//            ),
//            'lang'=>'zh'
//        );
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $condition = $this->getVisitOfCond($data);
        $total_flag=isset($data['total_flag'])?$data['total_flag']:false;
        if($condition === false){
            return false;   //该条件下客户信息为空数据返回空
        }
        if(!empty($data['current_no']) && $data['current_no']>0){
            $current_no = ceil($data['current_no']);
        }else{
            $current_no = 1;
        }
        $length = 10;
        $offset = ($current_no-1)*$length;
//        $total = $this->field('id')->where($condition)->count();
        $total_sql='select count(*) as total';
        $total_sql.=' from erui_buyer.buyer_visit visit ';
        $total_sql.=' left join erui_buyer.buyer on visit.buyer_id=buyer.id and deleted_flag=\'N\'';  //buyer
        $total_sql.=' inner join erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag=\'N\' and country.lang=\''.$lang."'";  //buyer
        $total_sql.=' left join erui_buyer.buyer_visit_reply reply on visit.id=reply.visit_id ';  //reply
        $total_sql.=' left join erui_sys.employee employee on reply.created_by=employee.id '; //employee
        $total_sql.=' where ';
        $total_sql.=$condition;
        $total=$this->query($total_sql);
        $total=$total[0]['total'];
        if($total_flag===true){
            $arr=array('total'=>$total);
            return $arr;
        }
        //按条件获取拜访记录数据
        $result = $this->condGetVisitData($lang,$condition,$offset,$length);
        if(empty($result)){
            $arr = [
                'current_no' => 1,
                'pagesize' => 0,
                'total' => 0,
                'result' => []
            ];
        }else{
            $arr = [
                'current_no' => $current_no,
                'pagesize' => $length,
                'total' => $total,
                'result' => $result
            ];
        }

        return $arr;
    }
    //获取客户需求反馈的条件
    public function getDemadCond($data){

//        if(empty($data['admin']['role'])){
//            return false;
//        }
//        $buyer=new BuyerModel();
//        $access=$buyer->accessCountry($data);
//        if($access===false){
//            return false;
//        }
//        $condition=$access;
        $condition='visit.is_demand=\'Y\' ';
        if(!empty($data['reply_name'])){  //需求反馈提交人姓名
            $condition.=" and employee.name like '%$data[reply_name]%'";
        }
        if(!empty($data['user_no'])){  //需求反馈提交人工号
            $condition.=" and employee.user_no like '%$data[user_no]%'";
        }
        if(!empty($data['phone'])){  //需求反馈提交人联系方式
            $condition.=" and employee.mobile like '%$data[phone]%'";
        }


        if(!empty($data['reply'])){  //是否需求反馈提状态
            if($data['reply']=='Y'){
                $condition.=" and reply.created_at>='1970-01-01 00:00:00'";
            }
            if($data['reply']=='N'){
                $condition.=" and reply.created_at is null";
            }
        }
        if(!empty($data['reply_strart_at'])){  //需求反馈时间strart
            $condition.=" and reply.created_at>='$data[reply_strart_at]'";
        }
        if(!empty($data['reply_end_at'])){  //需求反馈时间end
            $condition.=" and reply.created_at<='$data[reply_end_at]'";
        }


        if(!empty($data['buyer_name'])){  //客户名称
            $condition.=" and buyer.name like '%$data[buyer_name]%'";
        }
        if(!empty($data['buyer_code'])){  //CRM客户代码
            $condition.=" and buyer.buyer_code like '%$data[buyer_code]%'";
        }
        if(!empty($data['country_search'])){  //国家搜索
            $condition.=" and buyer.country_bn='$data[country_search]'";
        }
        return $condition;
    }
    /**
     * 需求列表
     * @param array $_input
     * @return array|bool|mixed
     */
    public function getDemadList($_input = [],$lang='zh'){
//        $length = isset($_input['pagesize']) ? intval($_input['pagesize']) : 10;
        $admin=$_input['admin']['role_no'];
        $length = 10;
        $current_no = isset($_input['current_no']) ? intval($_input['current_no']) : 1;
        $total_flag=isset($_input['total_flag'])?$_input['total_flag']:false;
        $offset=($current_no-1)*$length;
        $demadCond=$this->getDemadCond($_input);
        if($demadCond==false){
            return false;
        }
//        部门对接
        if(in_array('CRM客户管理',$admin)){    //CRM客户管理-所有数据

        }else{
            $org=$_input['admin']['group_id'];
            $handler=$_input['created_by'];
            if(empty($org)){
                return false;
            }
            $accessStr=implode(',',$org);
            $demadCond.=" and (visit.department in ($accessStr)";    //部门
            $demadCond.=" or visit.handler=$handler)";    //对接人员
        }
        
        //总条数
        $total_sql='select count(*) as total';
        $total_sql.=' from erui_buyer.buyer_visit visit ';
        $total_sql.=' left join erui_buyer.buyer on visit.buyer_id=buyer.id and deleted_flag=\'N\'';  //buyer
        $total_sql.=' left join erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag=\'N\' and country.lang=\''.$lang."'";  //buyer
        $total_sql.=' left join erui_buyer.buyer_visit_reply reply on visit.id=reply.visit_id ';  //reply
        $total_sql.=' left join erui_sys.employee employee on reply.created_by=employee.id '; //employee
        $total_sql.=' where ';
        $total_sql.=$demadCond;
        $total=$this->query($total_sql);
        $total=$total[0]['total'];
        if($total_flag===true){
            $arr=array('total'=>$total);
            return $arr;
        }
        //数据信息
        $sql='select ';
        $sql.=' buyer.id as buyer_id,buyer.buyer_no,buyer.name as buyer_name,buyer.buyer_code,country.name as country_name,visit.id as visit_id,reply.created_at as reply_at, ';
        $sql.=' employee.name as reply_name,';
        $sql.=' visit.demand_type';
        $sql.=' from erui_buyer.buyer_visit visit ';
        $sql.=' left join erui_buyer.buyer on visit.buyer_id=buyer.id and deleted_flag=\'N\'';  //buyer
        $sql.=' left join erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag=\'N\' and country.lang=\''.$lang."'";  //buyer
        $sql.=' left join erui_buyer.buyer_visit_reply reply on visit.id=reply.visit_id ';  //reply
        $sql.=' left join erui_sys.employee employee on reply.created_by=employee.id '; //employee
        $sql.=' where ';
        $sql.=$demadCond;
        $sql.=' order by reply.created_at desc ';
        $sql.=' limit '.$offset.','.$length;
        $info=$this->query($sql);
//        echo $this->getLastSql();die;
        $visit_product=new VisitProductModel();
        $visit_demand_type=new VisitDemadTypeModel();
        foreach($info as $key => $value){
            $product=$visit_product->getProductName($value['visit_id'],$lang);  //品类信息
            $info[$key]['product_cate']=$product;

            $demand_type_str=implode(',',json_decode($value['demand_type'],true));  //需求类型
            if(!empty($demand_type_str)){
                $demand_type=$visit_demand_type->getInfoByIds($demand_type_str,$lang);
            }else{
                $demand_type='';
            }
            $info[$key]['demand_type']=$demand_type;
            if(empty($info[$key]['reply_at'])){
                $info[$key]['reply_at']='';
            }
            if($lang=='zh'){  //是否反馈
                $info[$key]['reply']=!empty($value['reply_at'])?'是':'否';
            }else{
                $info[$key]['reply']=!empty($value['reply_at'])?'YES':'NO';
            }
        }
        $arr['total']=$total;
        $arr['current_no']=$current_no;
        $arr['result']=$info;
        return $arr;
    }

    /**
     * 更加id获取详情
     * @param $id
     * @return array|bool|mixed
     */
    public function getInfoById($data,$is_show_name=false){
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $condition = [
            'id' => $data['id'],
        ];
        try{
            $result = $this->field('id,buyer_id,name,phone,visit_at,visit_type,visit_level,visit_position,demand_type,demand_content,visit_objective,visit_personnel,visit_customer,visit_result,is_demand,department,handler,feedback_content,created_by,created_at')->where($condition)->find();

            if($result){
                //产品信息
                $visit_product=new VisitProductModel();
//                if($is_show_name==true){
//                    $product=$visit_product->getProductInfo($result['id'],'id,visit_id,product_cate,product_desc,purchase_amount,supplier,remark',true,$lang);
//                }else{
//                }
                $product=$visit_product->getProductInfo($result['id'],'id,visit_id,product_cate,product_desc,purchase_amount,supplier,remark');
                //user
                $user_model = new UserModel();
                $userInfo = $user_model->field('name,user_no')->where(['id'=>$result['created_by']])->find();
                $result['created_by_name'] = $userInfo['name'];
                //回复
                $reply = new BuyerVisitReplyModel();
                $replyInfo = $reply->field('visit_reply')->where(['visit_id'=>$result['id']])->find();
                //客户
                $buyer_model = new BuyerModel();
                $buyerInfo = $buyer_model->alias('buyer')
                ->join("erui_dict.country country on buyer.country_bn=country.bn",'left')
                ->field('buyer.country_bn,buyer.buyer_no,buyer.buyer_code,buyer.name,country.name as country_name')
                ->where(['buyer.id'=>$result['buyer_id'],"country.lang"=>$lang])->find();
                $area=$buyer_model->table('erui_operation.market_area_country')->alias('country')
                    ->join('erui_operation.market_area area on country.market_area_bn=area.bn')
                    ->field('area.name')
                    ->where(array('country.country_bn'=>$buyerInfo['country_bn'],'lang'=>$lang,'deleted_flag'=>'N'))->find();
                $buyerInfo['area']=$area['name'];
                $result['buyer_name'] = $buyerInfo['name'];
                $result['buyer_no'] = $buyerInfo['buyer_no'];
                $result['buyer_code'] = $buyerInfo['buyer_code'];
                $result['country_name'] = $buyerInfo['country_name'];
                $result['area'] = $buyerInfo['area'];
                $result['demand_content'] = $result['demand_content'];
                $result['visit_type'] = json_decode( $result['visit_type']);
                $result['visit_level'] = json_decode( $result['visit_level']);
                $result['visit_position'] = json_decode( $result['visit_position']);
                $result['demand_type'] = json_decode( $result['demand_type']);
                $result['visit_reply'] = $replyInfo['visit_reply'];
                $result['product_info'] = $product;     //产品信息

                if(!empty($result['department'])){  //部门名称
                    $org=new OrgModel();
                    $fieldPart=$lang=='zh'?'name':'name_en as name';
                    $orgInfo=$org->field($fieldPart)
                        ->where(array('id'=>$result['department'],'deleted_flag'=>'N'))
                        ->find();
                    $result['department_name']=$orgInfo['name'];
                }else{
                    $result['department_name']='';
                }
                if(!empty($result['handler'])){ //对接人
                    $emInfo=$this->table('erui_sys.employee')
                        ->field('user_no,name')
                        ->where(array('id'=>$result['handler'],'deleted_flag'=>'N'))->find();
                    $result['handler_name']=$emInfo['name'].'('.$emInfo['user_no'].')';
                }else{
                    $result['handler_name']='';
                }
                if($is_show_name){
                    $vdt_model = new VisitDemadTypeModel();
                    if(!empty($result['demand_type'])){
                        $demandInfo = $vdt_model->field('name')->where(['id'=>['in', $result['demand_type']]])->select();
                        $result['demand_type']=$this->packStrData($demandInfo);
                    }else{
                        $result['demand_type']='';
                    }
                    if(!empty($result['department'])){  //部门名称
                        $org=new OrgModel();
                        $fieldPart=$lang=='zh'?'name':'name_en as name';
                        $orgInfo=$org->field($fieldPart)
                            ->where(array('id'=>$result['department'],'deleted_flag'=>'N'))
                            ->find();
                        $result['department']=$orgInfo['name'];
                    }
                    if(!empty($result['handler'])){ //对接人
                        $emInfo=$this->table('erui_sys.employee')
                            ->field('user_no,name')
                            ->where(array('id'=>$result['handler'],'deleted_flag'=>'N'))->find();
                        $result['handler']=$emInfo['name'].'('.$emInfo['user_no'].')';
                    }

                    $vp_model = new VisitPositionModel();
                    $positionInfo = $vp_model->field('name')->where(['id'=>['in', $result['visit_position']]])->select();
                    $result['visit_position']=$this->packStrData($positionInfo);

                    $vl_model = new VisitLevelModel();
                    $levelInfo = $vl_model->field('name')->where(['id'=>['in', $result['visit_level']]])->select();
                    $result['visit_level']=$this->packStrData($levelInfo);

                    $vt_model = new VisitTypeModel();
                    $typeInfo = $vt_model->field('name')->where(['id'=>['in', $result['visit_type']]])->select();
                    $result['visit_type']=$this->packStrData($typeInfo);
                }
            }
            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerVisit】getInfoById:' . $e , Log::ERR);
            return false;
        }
    }
    private function packStrData($data){
        $str='';
        foreach($data as $k => $v){
            $str.=','.$v['name'];
        }
        return substr($str,1);
    }
    /**
     * 编辑（新增/修改）
     * @param array $_input
     * @return bool
     */
    public function edit($_input = []){
        $date=time();
        $at=$this->field('created_at')->where(array('created_by'=>$_input['created_by']))->order('id desc')->limit(1)->select();
        if(!empty($at)){
            $ex_at=strtotime($at[0]['created_at']);
            $diff=$date-$ex_at;
            if($diff<5){
                return 'warn';
            }

        }
        if(!isset($_input['buyer_id']) || empty($_input['buyer_id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('buyer_id'));    //客户不能为空
        }

        if(!isset($_input['visit_at']) || empty($_input['visit_at'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('visit_at'));   //请输入拜访时间
        }

        if(!isset($_input['name']) || empty($_input['name'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('contact_name')); //客户联系人不能为空
        }
        if(!isset($_input['phone']) || empty($_input['phone'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('contact_phone'));   //客户联系人方式不能为空
        }

        if(!isset($_input['visit_type']) || empty($_input['visit_type']) || !is_array($_input['visit_type'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('visit_type')); //请选择目的拜访类型
        }

        if(!isset($_input['visit_level']) || empty($_input['visit_level']) || !is_array($_input['visit_level'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('visit_level'));   //请选择拜访级别
        }

        if(!isset($_input['visit_position']) || empty($_input['visit_position']) || !is_array($_input['visit_position'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('visit_position')); //请选择职位拜访类型
        }

//        if(!isset($_input['demand_type']) || empty($_input['demand_type']) || !is_array($_input['demand_type'])){
//            jsonReturn('', ErrorMsg::ERROR_PARAM, '请选择需求反馈种类');
//        }

        if(!isset($_input['visit_objective']) || empty($_input['visit_objective'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('visit_objective'));   //请输入拜访目的
        }

        if(!isset($_input['visit_result']) || empty($_input['visit_result'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, L('visit_result'));   //请输入拜访结果
        }

        $userInfo = getLoinInfo();
        $data = $where = [];
        $data['visit_at'] = $_input['visit_at'];    //拜访时间
        $data['buyer_id'] = $_input['buyer_id'];
        $data['name'] = trim($_input['name']);  //客户联系人
        $data['phone'] = trim($_input['phone']);    //联系方式
        $data['visit_type'] = json_encode( $_input['visit_type']);      //目的拜访类型
        $data['visit_level'] = json_encode( $_input['visit_level']);    //拜访级别
        $data['visit_position'] = json_encode( $_input['visit_position']);    //拜访职位
        $data['demand_type'] = json_encode( $_input['demand_type']);    //需求类型

        $data['visit_objective'] = trim($_input['visit_objective']);    //拜访目的
        $data['visit_personnel'] = trim($_input['visit_personnel']);    //拜访陪同人员
        $data['visit_customer'] = trim($_input['visit_customer']);    //参与拜访人员(客户)
        $data['visit_result'] = trim($_input['visit_result']);    //拜访结果
        $data['customer_note'] = trim($_input['customer_note']);    //客户痛点
        $data['is_demand'] = $_input['is_demand'];    //是否有需求

        if($data['is_demand']=='N'){
            $data['department'] = '';    //部门
            $data['handler'] = '';    //部门
            $data['feedback_content'] = '';    //部门
        }
        $data['demand_content'] = trim($_input['demand_content']);    //需求内容
        //$data['visit_reply'] = trim($_input['visit_reply']);    //需求答复
        if(!empty($_input['department'])){
            $data['department'] = trim($_input['department']);    //部门
        }else{
            $data['department'] ='';    //部门
        }
        if(!empty($_input['handler'])){
            $data['handler'] = trim($_input['handler']);    //对接人员
        }else{
            $data['handler'] = '';    //对接人员
        }
        if(!empty($_input['feedback_content'])){
            $data['feedback_content'] = trim($_input['feedback_content']);    //反馈内容
        }else{
            $data['feedback_content'] = '';    //反馈内容
        }
        
        try{
            if(isset($_input['id']) && !empty($_input['id'])) {
                //$data['deleted_flag'] = self::DELETED_N;
                $where[ 'id' ] = intval( $_input[ 'id' ] );
                $where[ 'buyer_id' ] = intval( $_input[ 'buyer_id' ] );
                $this->where( $where )->save( $data );
                $result = $_input[ 'id' ];
                $visit_product=new VisitProductModel();
                $visit_product->updateProductInfo($_input['product_info'],$result,$userInfo['id']);
            }else{
                $data['created_by'] = $userInfo['id'] ? $userInfo['id'] : null;
                $data['created_at'] = date('Y-m-d H:i:s',time());
                //$data['deleted_flag'] =  self::DELETED_N;
                $result = $this->add($data);
                //产品分类信息
                $visit_product=new VisitProductModel();
                $visit_product->addProductInfo($_input['product_info'],$result,$userInfo['id']);
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
    //统计会员的拜访记录数量
    public function statisVisitCount($buyer_id){
        $visit=$this->singleVisitInfo($buyer_id);
        $demand=$this->singleVisitDemandInfo($buyer_id);
        $arr['visit_count']=$visit['totalVisit'];
        $arr['demand_count']=$demand['totalDemand'];
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
    public function exportStatisVisit($data,$report=false){
        //整理数据,获取文件路径
        if($report==false){ //拜访记录
            $excelDir = $this->getVisitStatiaList($data,$length = 1000);
        }else{  //调研报告
            $excelDir = $this->getReportStatiaList($data);
        }
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
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $condition = $this->getVisitOfCond($data);
        if($condition === false){
            return false;   //该条件下客户信息为空数据返回空
        }
//        $total = $this->field('id')->where($condition)->count();
        $total_sql='select count(*) as total';
        $total_sql.=' from erui_buyer.buyer_visit visit ';
        $total_sql.=' left join erui_buyer.buyer on visit.buyer_id=buyer.id and deleted_flag=\'N\'';  //buyer
        $total_sql.=' left join erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag=\'N\' and country.lang=\''.$lang."'";  //buyer
        $total_sql.=' left join erui_buyer.buyer_visit_reply reply on visit.id=reply.visit_id ';  //reply
        $total_sql.=' left join erui_sys.employee employee on reply.created_by=employee.id '; //employee
        $total_sql.=' where ';
        $total_sql.=$condition;
        $total=$this->query($total_sql);
        $total=$total[0]['total'];
        if($total==0){
            return false;   ///该条件下拜访记录为空数据
        }
        $i = 0;
        do {
            //按条件获取拜访记录数据
            $result = $this->condGetVisitData($lang,$condition,$i,$length);
            $info = $this->getVisitStatisData($result); //整理excel导出的数据
            if($i==0){
                $excelName = 'visit';
            }else{
                $excelName = 'visit_'.($i/$length);
            }
            $excelDir[] = $this->exportModel($lang,$excelName,$info); //导入excel,获取excel临时文件路径信息
            $i = $i+$length;
            $total =$total-$length;
        } while ($total > 0);
        return $excelDir;   //返回数组,已上传到服务器临时路径
    }
    //导出调研报告-wangs
    public function getReportStatiaList($data){
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $condition = $this->getVisitOfCond($data);
        if($condition === false){
            return false;   //该条件下客户信息为空数据返回空
        }
        $vtModel = new VisitTypeModel();    //拜访类型
        $vpModel = new VisitPositionModel();    //拜访位置类型
        $vlModel = new VisitLevelModel();   //拜访级别
        $buyerModel = new BuyerModel(); //客户
        $dpModel = new VisitDemadTypeModel();   //需求类型
        $bvrModel = new BuyerVisitReplyModel(); //拜访回复记录
        //数据信息
        $sql='select ';
        $sql.=' visit.id as visit_id,';   //拜访id
        $sql.=' (select name from erui_operation.market_area_country country';
        $sql.=' left join erui_operation.market_area area on country.market_area_bn=area.bn ';
        $sql.=' where  country.country_bn=buyer.country_bn and area.lang=\''.$lang.'\' and area.deleted_flag=\'N\'';
        $sql.=' ) as region_name,'; //地区
        $sql.=' country.name as country_name, ';    //国家
        $sql.=' employee.name as created_name,'; //提报人
        $sql.=' buyer.name as buyer_name,';    //客户名称
        $sql.=' business.is_purchasing_relationship as relationship,';    //是否与erui有采购关系
        $sql.=' buyer.is_oilgas,';    //类别:油气
        $sql.=' business.settlement,';    //结算方式
        $sql.=' business.is_local_settlement,';    //是否支持本地结算

        $sql.=' visit.customer_note,';   //痛点
        $sql.=' visit.demand_type,';   //需求类型
        $sql.=' visit.created_at';   //需求类型

        $sql.=' from erui_buyer.buyer_visit visit ';
        $sql.=' left join erui_buyer.buyer  on visit.buyer_id=buyer.id and deleted_flag=\'N\'';  //buyer
        $sql.=' left join erui_buyer.buyer_business business on buyer.id=business.buyer_id';  //buyer
        $sql.=' left join erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag=\'N\' and country.lang=\''.$lang."'";  //buyer
        $sql.=' left join erui_sys.employee employee on visit.created_by=employee.id '; //employee
        $sql.=' where ';
        $sql.=$condition;
        $sql.=' order by visit.created_at desc ';
        $result=$this->query($sql);
        $visit_product=new VisitProductModel();
        $pay=new PaymentModeModel();
        foreach($result as $index => $r) {
            $product = $visit_product->getProductArr($r['visit_id'], $lang);  //品类信息
            $result[$index]['product_info'] = $product;
            if($lang=='zh'){    //客户类型
                $result[$index]['is_oilgas'] = $r['is_oilgas']=='Y'?'油气':'非油气';
            }else{
                $result[$index]['is_oilgas'] = $r['is_oilgas']=='Y'?'oil gas':'Non oil gas';
            }
            if($lang=='zh'){    //是否有采购关系
                $result[$index]['relationship'] = $r['relationship']=='Y'?'是':'否';
            }else{
                $result[$index]['relationship'] = $r['relationship']=='Y'?'YES':'NO';
            }
            if($lang=='zh'){    //是否支持当地结算
                $result[$index]['is_local_settlement'] = $r['is_local_settlement']=='Y'?'是':'否';
            }else{
                $result[$index]['is_local_settlement'] = $r['is_local_settlement']=='Y'?'YES':'NO';
            }
            $pay_mode=$pay->getSettlementNameById($r['settlement'],$lang);  //结算方式
            $result[$index]['settlement']=$pay_mode['name'];

            //客户需求类型
            $dtype = json_decode($r['demand_type']);
            if(!empty($dtype)){
                if($lang=='zh'){
                    $dpInfo = $dpModel->field('name as name')->where(['id'=>['in',$dtype],'deleted_flag'=>'N'])->select();
                }else{
                    $dpInfo = $dpModel->field('en as name')->where(['id'=>['in',$dtype],'deleted_flag'=>'N'])->select();
                }
                $demand_type = '';
                foreach($dpInfo as $info){
                    $demand_type.= ','.$info['name'];
                }
            }
            $result[$index]['demand_type'] = $demand_type ? mb_substr($demand_type,1) : '';
        }
        $arr=$this->packageReportData($result);
        $excelDir[] = $this->exportModel($lang,'report',$arr); //导入excel,获取excel临时文件路径信息
        return $excelDir;   //返回数组,已上传到服务器临时路径
    }
    private function packageReportData($data){
        $arr=[];
        foreach($data as $k => $v){
            $arr[$k]['region_name']=$v['region_name'];  //地区
            $arr[$k]['country_name']=$v['country_name'];  //国家
            $arr[$k]['created_name']=$v['created_name'];  //提报人
            $arr[$k]['buyer_name']=$v['buyer_name'];  //客户名称
            $arr[$k]['relationship']=$v['relationship'];  //是否为已有客户
            $arr[$k]['is_oilgas']=$v['is_oilgas'];  //客户类别
            $arr[$k]['settlement']=$v['settlement'].'/'.$v['is_local_settlement'];  //结算模式及付款条件
            $arr[$k]['product_info']=$v['product_info'];  //品类信息
            $arr[$k]['demand_type']=$v['demand_type'];  //客户需求
            $arr[$k]['customer_note']=$v['customer_note'];  //客户痛点
            $arr[$k]['remark']='';  //备注
            $arr[$k]['created_at']=$v['created_at'];  //更新时间
        }
        return $arr;
    }
    /**
     * sheet名称 $sheetName
     * execl导航头 $tableheader
     * execl导出的数据 $data
     * wangs
     */
    public function exportModel($lang='zh',$excelName,$data){
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
//        if($lang=='zh'){
//            $tableheader = array('序号','客户名称','客户代码（CRM）','拜访时间','目的拜访类型','职位拜访类型','拜访级别','客户需求类别','拜访目的','随访人员','拜访结果','创建人');
//        }else{
//            $tableheader = array('Serial','Customer name','Customer code','Visit time','Visit type','Position','Visit level','Customer demand category','Purpose of visiting','Follow-up personnel','Visit the result','Founder');
//        }

        if($excelName=='report'){
            if($lang=='zh'){
                $tableheader = array('地区','国家','提报人','客户名称','是否为已有客户','客户类别','结算模式及付款条件','产品品类(一)|产品品类(二)|年采购金额（万美元)|主要供应商','客户所需服务','客户痛点','备注','更新时间');
            }else{
                $tableheader = array('Area','Country','Reporter','Customer name','existing customer','Customer category','Settlement model and payment condition','Product category(一)|Product category(二)|Annual purchase amount（Million / US dollar)|Major suppliers','Customer service','Customer pain point','Remarks','Update time');
            }
        }else{
            if($lang=='zh'){
                $tableheader = array('序号','创建人','地区','国家','客户代码（CRM）','拜访时间','中方参会人员','客户参会人员','拜访目的','拜访职位','客户联系人','联系方式','客户需求','商品描述','客户痛点','拜访结果');
            }else{
                $tableheader = array('Serial','creater','Area','Country','Customer code','Visit time','Chinese participants','Customer participants','The purpose of visiting','Visit a position','customer contact','mobile','customer demand','Commodity Description','Customer pain point','Visit the result');
            }
        }
        $excel->getActiveSheet()->getStyle('H')->getAlignment()->setWrapText(true);
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
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
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
            $arr[$k]['visit_id'] = $v['visit_id'];    //序号
            $arr[$k]['created_name'] = $v['created_name'];    //创建人
            $arr[$k]['region_name'] = $v['region_name'];    //地区
            $arr[$k]['country_name'] = $v['country_name'];    //国家
            $arr[$k]['buyer_code'] = $v['buyer_code'];    //客户代码（CRM）
            $arr[$k]['visit_at'] = $v['visit_at'];    //拜访时间
            $arr[$k]['visit_personnel'] = $v['visit_personnel'];    //中方参会人员
            $arr[$k]['visit_customer'] = $v['visit_customer'];    //客户参会人员
            $arr[$k]['visit_objective'] = $v['visit_objective'];    //拜访目的
            $arr[$k]['visit_position'] = $v['visit_position'];    //拜访职位
            $arr[$k]['contact_name'] = $v['contact_name'];    //客户联系人
            $arr[$k]['contact_phone'] = $v['contact_phone'];    //联系人方式phone
            $arr[$k]['demand_type'] = $v['demand_type'];    //客户需求
            $arr[$k]['product_cate'] = $v['product_cate'];    //商品描述
            $arr[$k]['demand_content'] = $v['demand_content'];    //客户痛点
            $arr[$k]['visit_result'] = $v['visit_result'];    //拜访结果
        }
//        foreach($data as $k => $v){
//            $arr[$k]['visit_id'] = $v['visit_id'];    //序号
//            $arr[$k]['buyer_name'] = $v['buyer_name'];    //客户名称
//            $arr[$k]['buyer_code'] = $v['buyer_code'];    //客户代码（CRM）
//            $arr[$k]['visit_at'] = $v['visit_at'];    //拜访时间
//            $arr[$k]['visit_type'] = $v['visit_type'];    //目的拜访类型
//            $arr[$k]['visit_position'] = $v['visit_position'];    //职位拜访类型
//            $arr[$k]['visit_level'] = $v['visit_level'];    //拜访级别
//            $arr[$k]['demand_type'] = $v['demand_type'];    //客户需求类别
//
//            $arr[$k]['visit_objective'] = $v['visit_objective'];    //拜访目的
//            $arr[$k]['visit_personnel'] = $v['visit_personnel'];    //随访人员
//            $arr[$k]['visit_result'] = $v['visit_result'];    //拜访结果
//            $arr[$k]['created_name'] = $v['created_name'];    //创建人
//        }
        return $arr;
    }
    /**按条件获取拜访记录的数据列表
     * @条件 $condition
     * @每页数据条数 $pageSize
     * @数据偏移量 $offset
     * wangs
     */
    public function condGetVisitData($lang='zh',$condition = [],$offset = 0,$pageSize = 10){
        $vtModel = new VisitTypeModel();    //拜访类型
        $vpModel = new VisitPositionModel();    //拜访位置类型
        $vlModel = new VisitLevelModel();   //拜访级别
        $buyerModel = new BuyerModel(); //客户
        $dpModel = new VisitDemadTypeModel();   //需求类型
        $bvrModel = new BuyerVisitReplyModel(); //拜访回复记录

        //数据信息
        $sql='select ';
        $sql.=' buyer.id as buyer_id,buyer.buyer_no,buyer.name as buyer_name,buyer.buyer_code,country.name as country_name, ';
        $sql.=' (select name from erui_operation.market_area_country country';
        $sql.=' left join erui_operation.market_area area on country.market_area_bn=area.bn ';
        $sql.=' where  country.country_bn=buyer.country_bn and area.lang=\''.$lang.'\' and area.deleted_flag=\'N\'';
        $sql.=' ) as region_name,';
        $sql.=' visit.id as visit_id,visit.visit_at,visit.created_at,';
        $sql.=' reply.created_at as reply_time,';
        $sql.=' visit.demand_content,';
        $sql.=' visit.visit_type,visit.visit_level,visit.visit_position,visit.demand_type,visit.visit_objective,visit.visit_personnel,visit.visit_customer,visit.visit_result,visit.customer_note,';
        $sql.=' visit.name as contact_name,visit.phone as contact_phone,';
        $sql.=' employee.name as created_name';
        $sql.=' from erui_buyer.buyer_visit visit ';
        $sql.=' left join erui_buyer.buyer on visit.buyer_id=buyer.id and deleted_flag=\'N\'';  //buyer
        $sql.=' left join erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag=\'N\' and country.lang=\''.$lang."'";  //buyer
        $sql.=' left join erui_buyer.buyer_visit_reply reply on visit.id=reply.visit_id ';  //reply
        $sql.=' left join erui_sys.employee employee on visit.created_by=employee.id '; //employee
        $sql.=' where ';
        $sql.=$condition;
        $sql.=' group by visit.id ';
        $sql.=' order by visit.created_at desc ';
        $sql.=' limit '.$offset.','.$pageSize;
        $result=$this->query($sql);
        $visit_product=new VisitProductModel();
        foreach($result as $index => $r) {
            $product = $visit_product->getProductName($r['visit_id'], $lang);  //品类信息
            $result[$index]['product_cate'] = $product;
            if($lang=='zh'){
                $result[$index]['reply'] = !empty($r['reply_time'])?'是':'否';
            }else{
                $result[$index]['reply'] = !empty($r['reply_time'])?'YES':'NO';
            }

        }
        foreach($result as $index => $r){
            //目的拜访类型
            $vtype = json_decode($r['visit_type']);
            if($lang=='zh'){
                $visitTypeInfo = $vtModel->field('name as name')->where(['id'=>['in',$vtype]])->select();
            }else{
                $visitTypeInfo = $vtModel->field('en as name')->where(['id'=>['in',$vtype]])->select();
            }
            $visit_type = '';
            foreach($visitTypeInfo as $info){
                $visit_type.= ','.$info['name'];
            }
            $result[$index]['visit_type'] = $visit_type ? mb_substr($visit_type,1) : '';
        }
        foreach($result as $index => $r){
            //职位拜访类型
            $vposition = json_decode($r['visit_position']);
            if($lang=='zh'){
                $vpInfo = $vpModel->field('name as name')->where(['id'=>['in',$vposition]])->select();
            }else{
                $vpInfo = $vpModel->field('en as name')->where(['id'=>['in',$vposition]])->select();
            }
            $visit_position = '';
            foreach($vpInfo as $info){
                $visit_position.= ','.$info['name'];
            }
            $result[$index]['visit_position'] = $visit_position ? mb_substr($visit_position,1) : '';

        }
        foreach($result as $index => $r){
            //拜访级别
            $vlevel = json_decode($r['visit_level']);
            if($lang=='zh'){
                $vlInfo = $vlModel->field('name as name')->where(['id'=>['in',$vlevel]])->select();
            }else{
                $vlInfo = $vlModel->field('en as name')->where(['id'=>['in',$vlevel]])->select();
            }
            $visit_level = '';
            foreach($vlInfo as $info){
                $visit_level.= ','.$info['name'];
            }
            $result[$index]['visit_level'] = $visit_level ? mb_substr($visit_level,1) : '';
        }
        foreach($result as $index => $r){
            //客户需求类型
            $dtype = json_decode($r['demand_type']);
            if(!empty($dtype)){
                if($lang=='zh'){
                    $dpInfo = $dpModel->field('name as name')->where(['id'=>['in',$dtype],'deleted_flag'=>'N'])->select();
                }else{
                    $dpInfo = $dpModel->field('en as name')->where(['id'=>['in',$dtype],'deleted_flag'=>'N'])->select();
                }
                $demand_type = '';
                foreach($dpInfo as $info){
                    $demand_type.= ','.$info['name'];
                }
            }
            $result[$index]['demand_type'] = $demand_type ? mb_substr($demand_type,1) : '';
        }
        return $result;
    }
    //合并创建,和经办人-wang
    public function validAgent($createdArr,$list){
        $flag=[];
        if(empty($createdArr) && empty($list)){
            $flag=null;
        }elseif(!empty($createdArr) && empty($list)){
            $flag=$createdArr;
        }elseif(empty($createdArr) && !empty($list)){
            $flag=$list;
        }elseif(!empty($createdArr) && !empty($list)){
            $flag=array_merge($createdArr,$list);
        }
        return $flag;
    }
    /**
     * 获取拜访记录搜索条件
     * wangs
     */
    public function getVisitOfCond($data){
//        $condition=' 1=1 ';


        if(empty($data['admin']['role'])){
            return false;
        }
        $buyer=new BuyerModel();
        $access=$buyer->accessCountry($data);
        if($access===false){
            return false;
        }
        $condition=$access;
//        if(!empty($data['visit_level'])){  //拜访级别
//            $condition.=" and visit_level like '%\"".$data['visit_level']."\"%'";
//        }
//        if(!empty($data['visit_position'])){  //拜访职位
//            $condition.=" and visit_position like '%\"".$data['visit_position']."\"%'";
//        }
        if(!empty($data['buyer_name'])){  //客户名称
            $condition.=" and buyer.name like '%$data[buyer_name]%'";
        }
        if(!empty($data['buyer_code'])){  //CRM客户代码
            $condition.=" and buyer.buyer_code like '%$data[buyer_code]%'";
        }
        if(!empty($data['buyer_no'])){  //CRM客户代码
            $condition.=" and buyer.buyer_no like '%$data[buyer_no]%'";
        }
        if(!empty($data['country_search'])){  //国家搜索
            $condition.=" and buyer.country_bn='$data[country_search]'";
        }

        if(!empty($data['created_name'])){  //拜访记录创建人姓名
            $condition.=" and employee.name like '%$data[created_name]%'";
        }

        if(!empty($data['reply'])){  //是否需求反馈提状态
            if($data['reply']=='Y'){
                $condition.=" and reply.created_at>='1970-01-01 00:00:00'";
            }
            if($data['reply']=='N'){
                $condition.=" and reply.created_at is null";
            }
        }
        if(!empty($data['visit_at_start'])){  //拜访时间strart
            $condition.=" and visit.visit_at>='$data[visit_at_start]'";
        }
        if(!empty($data['visit_at_end'])){  //拜访时间end
            $condition.=" and visit.visit_at<='$data[visit_at_end]'";
        }
        if(!empty($data['visit_start_time'])){  //拜访记录创建时间strart
            $condition.=" and visit.created_at>='$data[visit_start_time] 00:00:00'";
        }
        if(!empty($data['visit_end_time'])){  //拜访记录创建时间end
            $condition.=" and visit.created_at<='$data[visit_end_time] 23:59:59'";
        }
        //按拜访记录id为条件
        if (!empty($data['all_id'])) {
            $condition.=" and visit.id in ($data[all_id])";
        }
        if (!empty($data['buyer_id'])) {
            $condition.=" and visit.buyer_id=$data[buyer_id] ";
        }
        return $condition;
    }
    public function getVisitOfCond1($data = [])
    {
        $condition = [];
        //按拜访记录id为条件
        if (!empty($data['all_id'])) {
            $condition['buyer_visit.id'] = ['in', $data['all_id']];
        }
        //客户名称或客户CRM编码为条件
        if (!empty($data['buyer_id'])) {
            $condition['buyer_id'] = ['in', $data['buyer_id']];
        }
        $cond = ' 1=1';
        if (!empty($data['buyer_name'])) {  //客户名称
            $cond .= " and name like '%$data[buyer_name]%'";
        }
        if (!empty($data['buyer_no'])) {  //客户编号
            $cond .= " and buyer_no like '%$data[buyer_no]%'";
        }
        if (!empty($data['buyer_code'])) {  //客户code
            $cond .= " and buyer_code like '%$data[buyer_code]%'";
        }
        if (!empty($data['country_bn']) || !empty($data['country_search'])) {  //国家权限 || 国家搜索
            if(!empty($data['country_bn'])){    //国家权限
                $countryArr=explode(',',$data['country_bn']);
                $countryStr='';
                foreach($countryArr as $v){
                    $countryStr.=",'".$v."'";
                }
                $countryStr=substr($countryStr,1);
                if($data['admin']==0){  //没有查看所有的权限
                    $cond .= " and buyer.country_bn in ($countryStr)";
                }
            }
            if(!empty($data['country_search'])){    //国家搜索
                $cond .= " and buyer.country_bn='".$data['country_search']."'";
            }
            $buyerModel= new BuyerModel();
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
//        if (!empty($data['buyer_name']) || !empty($data['buyer_code'])) { //
//            $buyerModel= new BuyerModel();
//            $buyer_ids = $buyerModel->field('id')->where($cond)->order('id desc')->select();
//            if (empty($buyer_ids)) {
//                return false;   //数据为空
//            }
//            $buyer_id = [];
//            foreach ($buyer_ids as $v) {
//                $buyer_id[] = $v['id'];
//            }
//            $condition['buyer_id'] = ['in', $buyer_id];
//        }
        if (isset($data['visit_level']) && !empty($data['visit_level'])) {    //拜访级别
            $condition['visit_level'] = ['exp', 'regexp \'"' . $data['visit_level'] . '"\''];
        }
        if (isset($data['visit_position']) && !empty($data['visit_position'])) {  //拜访职位类型
            $condition['visit_position'] = ['exp', 'regexp \'"' . $data['visit_position'] . '"\''];
        }
        //	拜访时间visit_at_start开始时间   visit_at_end结束时间条件
        $this->_getValue($condition, $data, 'visit_at', 'between'); //搜索条件end
        //	创建时间时间created_at_start开始时间   created_at_end结束时间条件
        $this->_getValue($condition, $data, 'created_at', 'between'); //搜索条件end
        return $condition;  //
    }
}