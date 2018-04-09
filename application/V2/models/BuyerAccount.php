<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class BuyerAccountModel extends PublicModel {

    protected $tableName = 'buyer_account';
    protected $dbName = 'erui_buyer'; //数据库名称

    public function __construct($str = '') {

        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 判断用户是否存在
     * @param  string $data 用户名
     * @param  string $sql 语言
     * @return mix
     * @author jhw
     */

    public function Exist($data, $sql = 'or') {
        if (isset($data['email']) && isset($data['user_name'])) {
            if ($sql == 'or') {
                $map1['email'] = $data['email'];
                $map1['user_name'] = $data['user_name'];
                $map1['_logic'] = 'or';
                $map['_complex'] = $map1;
            } else {
                $map['email'] = $data['email'];
                $map['user_name'] = $data['user_name'];
            }
            $map['deleted_flag'] = 'N';
            $row = $this->table('erui_buyer.buyer_account')->where($map)->select();
        } else {
            $row = $this->table('erui_buyer.buyer_account')->where($data)->select();
        }
        return empty($row) ? false : $row;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'ug.id desc') {
        $sql = 'SELECT ug.id,ug.group_id,g.name as group_name,ug.user_id,u.name as user_name ';
        $sql .= ' FROM ' . $this->tableName . 'as ug';
        $sql .= ' LEFT JOIN t_group AS g ON t_group.`id` = ug.`group_id`';
        $sql .= ' LEFT JOIN t_user AS u ON u.`id` = ug.`user_id`';
        if (!empty($data['group_id'])) {
            $sql .= ' WHERE g.`group_id` = ' . $data['group_id'];
        }
        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit['page'] . ',' . $limit['num'];
        }
        $sql .= ' ORDER BY ' . $order;
        $res = $this->query($sql);
        return $res;
    }

    /**
     * 获取用户信息
     * @param  array  $data
     * @return array
     * @author jhw
     */
    public function info($data) {
        if (!empty($data['buyer_id'])) {
            $row = $this->where(['buyer_id' => $data['buyer_id'], 'deleted_flag' => 'N'])
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function login($data) {
        $where = array();
        if (!empty($data['email'])) {
            $where['email'] = $data['email'];
        }
        if (!empty($data['user_name'])) {
            $where['user_name'] = $data['user_name'];
        }
        if (empty($where['user_name']) && empty($where['email'])) {
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if (!empty($data['password'])) {
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'VALID';
        $row = $this->where($where)
                ->field('id,customer_id,email,user_name,mobile,role,first_name,last_name,phone,status,login_count,last_login_time,login_failure_count')
                ->find();
        return $row;
    }

    /**
     * 修改数据(更新)
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {

        if (isset($data['email'])) {
            $arr['email'] = $data['email'];
        }
        if (isset($data['user_name'])) {
            $arr['user_name'] = $data['user_name'];
        }
        if (isset($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
        }
//        if (isset($data['password_hash'])) {
//            $arr['password_hash'] = md5($data['password_hash']);
//        }
        if (isset($data['role'])) {
            $arr['role'] = $data['role'];
        }
        if (isset($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];
        }
        if (isset($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if (isset($data['status'])) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DELETE:
                    $arr['status'] = $data['status'];
                    break;
            }
        }
        if (!empty($where)) {
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['buyer_id'])) {
            $arr['buyer_id'] = $create['buyer_id'];
        }
        if (isset($create['email'])) {
            $arr['email'] = $create['email'];
        }
        if (isset($create['user_name'])) {
            $arr['user_name'] = $create['user_name'];
        }
        if (isset($create['mobile'])) {
            $arr['mobile'] = $create['mobile'];
        }
//        if (isset($create['password_hash'])) {
//            $arr['password_hash'] = $create['password_hash'];
//        }
        if (isset($create['role'])) {
            $arr['role'] = $create['role'];
        }
        if (isset($create['show_name'])) {
            $arr['show_name'] = $create['show_name'];
        }
        if (isset($create['phone'])) {
            $arr['phone'] = $create['phone'];
        }
        $arr['created_at'] = Date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }

    /**
     * 密码校验
     * @author klp
     */
//    public function checkPassword($data) {
//        if (!empty($data['id'])) {
//            $where['id'] = $data['id'];
//        } else {
//            jsonReturn('', '-1001', '用户id不可以为空');
//        }
//        if (!empty($data['password'])) {
//            $password = $data['password'];
//        }
//        $pwd = $this->where($where)->field('password_hash')->find();
//        if ($pwd == $password) {
//            return true;
//        } else {
//            return false;
//        }
//    }

    /**
     * 密码修改
     * @author klp
     * return bool
     */
//    public function update_pwd($data, $token) {
//
//        if (!empty($token['customer_id'])) {
//            $where['customer_id'] = $token['customer_id'];
//        } else {
//            jsonReturn('', '-1001', '用户id不可以为空');
//        }
//        if (!empty($data['password_hash'])) {
//            $new['password_hash'] = $data['password_hash'];
//        } else {
//            jsonReturn('', '-1001', '新密码不可以为空');
//        }
//        return $this->where($where)->save($new);
//    }

    /*
     * 根据用户ID 获取用户名 姓
     * @param array $buyer_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyerNamesByBuyerids($buyer_ids) {

        try {
            $where = [];

            if (is_string($buyer_ids)) {
                $where['buyer_id'] = $buyer_ids;
            } elseif (is_array($buyer_ids) && !empty($buyer_ids)) {
                $where['buyer_id'] = ['in', $buyer_ids];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('buyer_id,show_name,first_name,last_name')->select();
            $buyer_names = [];
            foreach ($buyers as $buyer) {
                $buyer_names[$buyer['buyer_id']] = $buyer['first_name'] . $buyer['last_name'];
                $buyer_names['show_name'] = $buyer['show_name'];
            }
            return $buyer_names;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }
    /**
     * buyer_id 获取客户账号email
     * wangs
     */
    public function getBuyerAccount($buyer_id){
        return $this->field('email')->where(array('buyer_id'=>$buyer_id))->find();
    }
    //CRM-wangs
    public function setPwdEmail($buyer_id){
        $pwd=$this->randStr(6);
        $password=md5($pwd);
//        $this->where(array('buyer_id'=>$buyer_id))->save(array('password_hash'=>md5($pwd)));
        $save="update erui_buyer.buyer_account set password_hash='$password',sent_email=sent_email+1 WHERE buyer_id=$buyer_id";
        $this->query($save);
        $cond="account.buyer_id=$buyer_id and account.deleted_flag='N'";
        $account=$this->alias('account')
            ->join('erui_buyer.buyer buyer on account.buyer_id=buyer.id and buyer.deleted_flag=\'N\'','left')
            ->join('erui_buyer.buyer_agent agent on account.buyer_id=agent.buyer_id and agent.deleted_flag=\'N\'','left')
            ->field('buyer.name as company_name,account.email as account_email,account.show_name,account.created_by,agent.agent_id')
            ->where($cond)
            ->select();

        $company_name=$account[0]['company_name'];   //客户公司
        $show_name=$account[0]['show_name'];   //客户姓名
        $account_email=$account[0]['account_email'];   //客户账号
        $account_pwd=$pwd;   //客户账号密码
        $created_by=$account[0]['created_by'];   //创建姓名id
        $agentArr=[$created_by];
        if(!empty($account[0]['agent_id'])){
            foreach($account as $k => $v){
                $agentArr[]=$v['agent_id'];
            }
        }

        $agent_arr=array_values(array_flip(array_flip($agentArr)));
        $agent_str=implode(',',$agent_arr);
        print_r($agent_str);die;
        $agentInfo=$this->query("select id,user_no,email,`name`,mobile from erui_sys.employee WHERE deleted_flag='N' AND id in ($agent_str)");
        if(count($agentInfo)>1){
            foreach($agentInfo as $k => $v){
                if($v['id']==$created_by){
                    $self=$v;
                    unset($agentInfo[$k]);
                }
            }
            array_unshift($agentInfo,$self);
        }

        $arr['customer']['company_name']=$company_name;
        $arr['customer']['show_name']=$show_name;
        $arr['customer']['account_email']=$account_email;
        $arr['customer']['account_pwd']=$account_pwd;
        $arr['agent_info']=$agentInfo;
        return $arr;
    }
    private function randStr($length)
    {
        $pattern = 'abcdefghjkmnpqrstuvwxy';
        $len=strlen($pattern);
        $str='';
        for($i=0;$i<3;$i++)
        {
            $str .= $pattern{mt_rand(0,$len-1)};    //生成php随机数
        }

        $num = '23456789';
        $num_len=strlen($num);
        for($i=0;$i<3;$i++)
        {
            $str .= $num{mt_rand(0,$num_len-1)};    //生成php随机数
        }
        $arr=str_split($str);
        shuffle($arr);
        $pwd=implode('',$arr);
        return $pwd;
    }
}
