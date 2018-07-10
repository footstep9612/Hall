<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 功能
 */

class FuncController extends PublicController {

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
    }

    /*
     * 所有功能清单
     */

    public function listAction() {
        if ($this->getMethod() === 'GET') {
            $condition = $this->getParam();
            $condition['lang'] = $this->getParam('lang', 'zh');
        } else {
            $condition = $this->getPut();
            $condition['lang'] = $this->getPut('lang', 'zh');
        }
        $roleUserModel = new System_RoleUserModel();
        $userId = !empty($condition['user_id']) ? trim($condition['user_id']) : $this->user['id'];
        $data = $roleUserModel->getUserMenu($userId, $condition, $this->lang);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function shortcutsAction() {

        if ($this->getMethod() === 'GET') {
            $data = $this->getParam();
            $data['lang'] = $this->getParam('lang', 'zh');
        } else {
            $data = $this->getPut();
            $data['lang'] = $this->getPut('lang', 'zh');
        }

        $redis_key = 'user_fastentrance_' . $this->user['id'];
        $data = null;

        if (!redisExist($redis_key)) {
            $mapping = [
                'show_create_inquiry' => '询单管理',
                'show_create_order' => '订单列表',
                'show_create_buyer' => '客户信息管理',
                'show_create_visit' => '客户信息管理',
                'show_demand_feedback' => '客户需求反馈',
                'show_request_permission' => '授信管理',
                'show_supplier_check' => '供应商审核',
                'show_goods_check' => 'SPU审核'
            ];
            $values = array_values($mapping);
            $roleUserModel = new System_RoleUserModel();
            $menu = $roleUserModel->getUserMenu($this->user['id'], ['fn' => $values], $this->lang);
            foreach ($mapping as $k => $v) {
                $data[$k]['show'] = 'N';
            }
            $this->_scanMenu($menu, $mapping, $data);
            redisSet($redis_key, json_encode($data), 360);
        } else {
            $data = json_decode(redisGet($redis_key), true);
        }
        $this->jsonReturn([
            'code' => 1,
            'message' => L('SUCCESS'),
            'data' => $data
        ]);
    }

    /**
     * @desc 扫描菜单
     *
     * @param array $menu 菜单数据
     * @param array $mapping 菜单映射
     * @param array $data 需处理的数据
     * @author liujf
     * @time 2018-06-19
     */
    private function _scanMenu($menu, $mapping, &$data) {

        foreach ($menu as $item) {
            foreach ($mapping as $k => $v) {
                if ($item['fn'] == $v) {
                    $data[$k]['show'] = 'Y';
                    $data[$k]['parent_id'] = $item['top_parent_id'];
                }
            }
        }
    }

    /**
     * @desc 扫描菜单
     *
     * @param array $menu 菜单数据
     * @param array $mapping 菜单映射
     * @param array $data 需处理的数据
     * @author liujf
     * @time 2018-06-19
     */
    public function batchSetTopParentIdAction() {

        set_time_limit(0);
        $urlperm_model = new System_UrlPermModel();
        $list = $urlperm_model->where(['isNull(top_parent_id) or top_parent_id=0'])->select();
        foreach ($list as $val) {
            $falg = $urlperm_model->update_data($val, ['id' => $val['id']]);
        }
        $this->jsonReturn([
            'code' => 1,
            'message' => L('SUCCESS'),
            'data' => null
        ]);
    }

}
