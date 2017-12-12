<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/10
 * Time: 17:57
 */
class CustomservicesController extends PublicController
{

    public function init() {
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
         $limit = [];
         if(!empty($data['pageSize'])){
             $limit['num'] = $data['pageSize'];
         } else {
             $limit['num'] = 10;
         }
         if(!empty($data['currentPage'])) {
             $limit['page'] = ($data['currentPage'] - 1) * $limit['num'];
         } else {
             $limit['page'] = 1;
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
        if(!isset($data['custom_id']) || empty($data['custom_id'])) {
            jsonReturn(null, -203, '定制服务ID不能为空!');
        }
        $buyer_custom_model = new BuyerCustomModel();
        $customInfo = $buyer_custom_model->info($data['custom_id'], $lang);
        if($customInfo) {
            jsonReturn($customInfo, ShopMsg::CUSTOM_SUCCESS, 'success!');
        } else {
            jsonReturn('', ShopMsg::CUSTOM_FAILED, 'failed!');
        }
    }

    /**
     * 服务类型新增
     * @author klp
     */
    public function createCatAction() {
        $data = $this->getPut();

        $cat_model = new CustomCatModel();
        $res = $cat_model->edit($data);
    }

}





















