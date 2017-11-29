<?php
//客户档案管理 wangs
class BuyerBusinessModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'buyer'; //客户表名
    protected $tableAccount = 'buyer_account'; //客户账号表名
    protected $tableBusiness = 'buyer_business'; //采购商业务信息表名
    public function __construct()
    {
        parent::__construct();
    }

    //客户档案管理搜索列表index,wangs
    public function buyerList($data)
    {
        $page = isset($data['page'])&&!empty($data['page']) ? $data['page'] : 1;
        $offset = ($page-1)*2;
        $map = array('buyer.created_by'=>$data['created_by']);
        if(!empty($data['area_bn'])){
            $map += array('buyer.area_bn'=>$data['area_bn']);
        }
        if(!empty($data['country_bn'])){
            $map += array('buyer.country_bn'=>$data['country_bn']);
        }
        if(!empty($data['buyer_code'])){
            $map += array('buyer.buyer_code'=>$data['buyer_code']);
        }
        if(!empty($data['name'])){
            $map += array('buyer.name'=>$data['name']);
        }
        if(!empty($data['buyer_level'])){
            $map += array('buyer.buyer_level'=>$data['buyer_level']);
        }
        if(!empty($data['reg_capital'])){
            $map += array('buyer.reg_capital'=>$data['reg_capital']);
        }
        if(!empty($data['line_of_credit'])){
            $map += array('buyer.line_of_credit'=>$data['line_of_credit']);
        }
        $info = $this->alias('buyer')
            ->join('erui_buyer.buyer_account account on buyer.id=account.buyer_id','left')
            ->join('erui_buyer.buyer_business business on account.buyer_id=business.buyer_id','left')
            ->field('buyer.id,buyer.buyer_code,buyer.name,buyer.area_bn,buyer.country_bn,buyer.line_of_credit,buyer.credit_available,buyer.buyer_level,buyer.level_at,buyer.credit_level,buyer.reg_capital,buyer.created_by,account.email,business.is_local_settlement,business.is_purchasing_relationship,business.is_net,business.net_at,business.net_invalid_at')
            ->where($map)
            ->limit($offset,2)
            ->select();
        return $info;
    }

}