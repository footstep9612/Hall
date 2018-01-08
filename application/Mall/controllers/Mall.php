<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/6
 * Time: 16:20
 */
class MallController extends PublicController
{

    public function init() {
        $this->token = false;
        parent::init();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getListAction() {
        $data = $this->getPut();
        $model = new BuyerCustomModel();
        $data['buyer_id'] = $this->user['buyer_id'];
        $res = $model->getlist($data);
        $count = $model->getCount($data);
        if (!empty($res)) {
            foreach($res as $item){
                $arr[$item['lang']][] = $item;
            }
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
            $datajson['count'] = $count;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = ShopMsg::CUSTOM_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }
        $this->jsonReturn($datajson);
    }
   /* public function getListAction() {
        $data = $this->getPut();
        $limit = [];
        if(!empty($data['pageSize'])){
            $limit['num'] = $data['pageSize'];
        }
        if(!empty($data['currentPage'])) {
            $limit['page'] = ($data['currentPage'] - 1) * $limit['num'];
        }
        $model = new BuyerCustomModel();
        $res = $model->getlist($data, $limit);
        if (!empty($res)) {
            $datajson['code'] = ShopMsg::CUSTOM_SUCCESS;
            $datajson['count'] = $res['count'];
            $datajson['data'] = $res['data'];
        } else {
            $datajson['code'] = ShopMsg::CUSTOM_FAILED;
            $datajson['data'] = "";
            $datajson['message'] = 'Data is empty!';
        }
        $this->jsonReturn($datajson);
    }*/

    /**
     * 获取服务类型列表
     * @param mix $condition
     * @author klp
     */
    public function catnameListAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $catModel = new CustomCatModel();
        $catInfo = $catModel->listName($lang);
        if($catInfo) {
            jsonReturn($catInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'data is empty!');
        }

    }

    /**
     * 展示所有定制信息详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function customInfoAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        $catModel = new CustomCatModel();
        $itemModel = new CustomCatItemModel();
        $catInfo = $catModel->info($lang,'');
        if($catInfo) {
             foreach ($catInfo as $k =>$v) {
                 $itemInfo = $itemModel->info($lang, $v['id'],'');
                 $catInfo[$k]['item'] = $itemInfo;
             }
            jsonReturn($catInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED ,'failed!');
        }

    }

    /**
     * 获取用户定制信息详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getUcustomInfoAction() {
        $data = $this->getPut();
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if(!isset($data['id']) || empty($data['id'])) {
            jsonReturn(null, -203, '定制服务ID不能为空!');
        }
        $buyer_custom_model = new BuyerCustomModel();
        $customInfo = $buyer_custom_model->info($data['id'],$lang);
        $this->_setBuyerName($customInfo);
        if($customInfo) {
            jsonReturn($customInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }
    }

    /**
     * 获取用户物流信息详情
     * @author klp
     */
    public function getUlogisticsInfoAction() {
        $data = $this->getPut();
//        if(!isset($data['buyer_id']) || empty($data['buyer_id'])) {
//            jsonReturn(null, -203, '用户ID不能为空!');
//        }
        $logisticsModel = new BuyerLogisticsModel();
        $logisticsInfo = $logisticsModel->info($this->user['buyer_id']);
        if($logisticsInfo) {
            jsonReturn($logisticsInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }

    }

    /**
     * 物流信息编辑(新增/更新)--(暂用一个接口)
     */
    public function editUlogisticsAction() {
        $data = $this->getPut();
        $where['buyer_id'] = $this->user['buyer_id'];
        $logisticsModel = new BuyerLogisticsModel();
        $check = $logisticsModel->where(['buyer_id'=>$where['buyer_id'],'deleted_flag'=>'N'])->find();
        if($check) {
            $res = $logisticsModel->update_data($data, $where);
        } else {
            $res = $logisticsModel->create_data($data, $where);
        }
        if($res) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED , 'failed!');
        }
        exit;
    }

    /**
     * 物流信息新增
     */
//    public function createUlogisticsAction() {
//        $data = $this->getPut();
//        $where['buyer_id'] = $this->user['buyer_id'];
//        $logisticsModel = new BuyerLogisticsModel();
//        $add = $logisticsModel->create_data($data, $where);
//        if($add) {
//            jsonReturn($add, ShopMsg::CUSTOM_SUCCESS, 'success!');
//        } else {
//            jsonReturn('', ShopMsg::CUSTOM_FAILED , 'failed!');
//        }
//    }
    /**
     * 物流信息更新
     */
//    public function updateUlogisticsAction() {
//        $data = $this->getPut();
//        $where['buyer_id'] = $this->user['buyer_id'];
//        $logisticsModel = new BuyerLogisticsModel();
//        $update = $logisticsModel->update_data($data, $where);
//        if($update) {
//            jsonReturn('', ShopMsg::CUSTOM_SUCCESS, 'success!');
//        } else {
//            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
//        }
//    }


    /**
     * 用户定制信息新增
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function createUcustomAction() {
        $data = $this->getPut();
        $where['buyer_id'] = $this->user['buyer_id'];
        $limit['pagesize'] = 1;
        $limit['current_no'] = 0;
        $buyer_custom_model = new BuyerCustomModel();
        $data_t_custom = $buyer_custom_model->getlist($limit);
        if ($data_t_custom && substr($data_t_custom['data'][0]['service_no'], 1, 8) == date("Ymd")) {
            $no = substr($data_t_custom['data'][0]['service_no'], 9, 6);
            $no++;
        } else {
            $no = 1;
        }
        $temp_num = 1000000;
        $new_num = $no + $temp_num;
        $real_num = "S" . date("Ymd") . substr($new_num, 1, 6);
        $data['service_no'] = $real_num;
        $res = $buyer_custom_model->create_data($data, $where);
        if($res) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }
    }

    /**
     * 用户定制信息更新
     * @param mix $condition
     * @author klp
     */
    public function updateUcustomAction() {
        $data = $this->getPut();
        $where['buyer_id'] = $this->user['buyer_id'];
        $buyer_custom_model = new BuyerCustomModel();
        $res = $buyer_custom_model->update_data($data, $where);
        if($res) {
            jsonReturn('', ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }
    }

    /**
     * 用户定制信息删除
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function delUcustomAction() {
        $data = $this->getPut();
        if (isset($data['buyer_id']) && !empty($data['buyer_id'])) {
            $where['buyer_id'] = trim($data['buyer_id']);
        } else{
            if (isset($data['id']) && !empty($data['id'])) {
                $where['id'] = trim($data['id']);
            } else {
                jsonReturn(null ,-201, 'ID不能为空!');
            }
        }
        $buyer_custom_model = new BuyerCustomModel();
        $res = $buyer_custom_model->delete_data($where);
        if($res) {
            jsonReturn($res, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }
    }

    //获取采购商name
    private function _setBuyerName(&$info) {
        if ($info['buyer_id']) {
            $buyer_model = new BuyerAccountModel();
            $custom_buyer_contact = $buyer_model->getBuyerNamesByBuyerids([$info['buyer_id']]);
            if (isset($custom_buyer_contact[$info['buyer_id']]) && isset($custom_buyer_contact['user_name'])) {
                $info['show_name'] = $custom_buyer_contact[$info['buyer_id']];
                $info['user_name'] = $custom_buyer_contact['user_name'];
            } else {
                $info['show_name'] = null;
                $info['user_name'] = null;
            }
        } else {
            $info['show_name'] = '';
            $info['user_name'] = '';
        }
    }

}













