<?php

/**
  附件文档Controller
 */
class Material_CategoryController extends Yaf_Controller_Abstract {

    public function init() {
        // parent::init();
        $this->_model = new PublicModel("material_category");
    }

    /**
     * 列表展示
     * @param 列表 post => token(string) s(int开始位置) l(int每页显示)
     * @param 搜索 post => token(string) s(int开始位置) l(int每页显示) w(string搜索条件)
     * @return array (id:文档id, a_type:文档类型(0/1), o_name:原始名, m_type:格式, url_r:相对地址, url_s:U域名部分, a_size:大小, a_desc:描述)
     * @author Wen
     */
    public function ListAction() {

        echo 'Material_Category';
        die();
        $data = json_decode(file_get_contents("php://input"), true);
        $code3 = !empty($data['code3']) ? trim($data['code3']) : '';
        $code2 = !empty($data['code2']) ? trim($data['code2']) : '';
        $code1 = !empty($data['code1']) ? trim($data['code1']) : '';
        $lang = !empty($data['lang']) ? trim($data['lang']) : '';
        $arr = $this->_model->Where($s, $l, $where);



        $res['data'] = array();
        if (!empty($arr)) {
            foreach ($arr as $a) {
                $a['a_size'] = format_size($a['a_size']);
                $res['data'][] = $a;
            }
        }
        if (count($res['data']) > 0) {
            $res['num'] = $this->_attachment->AttCount($where);
            $arr = array('code' => '0', 'message' => '成功', 'data' => $res);
        } else {
            $arr = array('code' => '-103', 'message' => '操作失败', 'data' => '不存在数据');
        }
        echo json_encode($arr);
        die();
    }

}
