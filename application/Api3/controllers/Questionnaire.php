<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Questionnaire
 * @author  zhongyg
 * @date    2018-4-23 13:38:05
 * @version V2.0
 * @desc
 */
class QuestionnaireController extends PublicController {

    //put your code here
    public function init() {
        $this->token = false;
        // parent::init();
    }

    public function CreatedAction() {
        $jsondata = $data = $this->getPut();
        $buyer_id = 0;
        if (!empty($jsondata["token"])) {
            $token = $jsondata["token"];
            $tokeninfo = JwtInfo($token); //解析token
            $userinfo = json_decode(redisGet('user_info_' . $tokeninfo['id']), true);
            if (empty($userinfo)) {
                echo json_encode(array("code" => "-104", "message" => "用户不存在"));
                exit;
            } else {
                $buyer_id = $userinfo['id'];
            }
        } elseif (!empty($jsondata["source_token"])) {
            $buyer_id = (new BuyerSourceModel)->getBuyerIdByToken($jsondata["source_token"]);
            if (empty($buyer_id)) {
                echo json_encode(array("code" => "-104", "message" => "source_token 不正确!"));
                exit;
            }
        }

        if ($buyer_id) {
            $flag = (new BuyerQuestionnaireModel)->create_data($buyer_id, $jsondata['questionnaire']);
            if ($flag) {
                (new BuyerSourceModel)->update_questionnaire($buyer_id);
                echo json_encode(array("code" => "1", "message" => "提交成功!"));
            } else {
                echo json_encode(array("code" => "-1", "message" => "提交失败!"));
            }

            exit;
        } else {
            echo json_encode(array("code" => "-104", "message" => "用户不存在"));
            exit;
        }
    }

}
