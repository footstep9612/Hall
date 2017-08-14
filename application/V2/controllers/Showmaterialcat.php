<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Showmaterialcat
 *
 * @author zhongyg
 * @date 2017-07-26 10:16
 */
class ShowmaterialcatController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    public function listAction() {
        $model = new ShowMaterialCatModel();
        $material_cat_no = $this->getPut('material_cat_no');
        if (!$material_cat_no) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $show_cat_nos = $model->getshowcatnosBymatcatno($material_cat_no, 'zh');
        $show_cat_nos_arr = [];
        if ($show_cat_nos) {
            foreach ($show_cat_nos as $show_cat_no) {
                $show_cat_nos_arr[] = $show_cat_no['cat_no'];
            }
        }
        $show_cat_model = new ShowCatModel();
        $data = $show_cat_model->getshow_cats($show_cat_nos_arr, 'zh');
        rsort($data);
        if ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn();
        } elseif ($data === false) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        }
    }

}
