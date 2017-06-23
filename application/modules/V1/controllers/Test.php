<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**

 * Description of Test
 *
 * @author zhongyg
 */
class TestController extends Yaf_Controller_Abstract {

    //put your code here
    public function createAction() {

        $testmodel = new TestModel();
        $testmodel->create_data();
    }

    public function indexAction() {
        $testmodel = new TestModel();
        $condition = [
                // 'show_name' => '童装',
                // 'cate_id' => 4
        ];
        $re = $testmodel->getshow_catlist($condition);
        echo '<pre>';
        var_dump($re);
//        $data = [
//            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjI3NiIsImV4dCI6MTQ5ODExMjI2NCwiaWF0IjoxNDk4MTEyMjY0LCJuYW1lIjoiXHU5NDlmXHU5NGY2XHU2ODQyIn0.GQF50uD3at0jpyWp4VgvtlrgPMyizPssVMdcHD3aQxc',
//            'inquiry_no'=>date('YmdHis'),
//            'created_at'=>date('Y-m-d H:i:s'),
//            'quantity'=>1,
//            'page'=>1,
//            'countPerPage'=>10,
//        ];
//        $re = $this->post($data, '/V1/Inquiry/addItem');
// 
//        var_dump(json_decode($re,true));
    }

    function post($data, $action, $ContentType = 'json', $timeout = 30) {

        $url = 'http://localhost' . $action;

        if ($ContentType == "json") {
            $header = array(
                'Content-type: application/json;charset=UTF-8',
                'Accept: */*',
                'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
                'Connection: Keep-Alive',
            );
            $formdata = json_encode($data);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);

        $response = curl_exec($ch);


        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    public function importAction() {

        $testmodel = new TestModel();
        $testmodel->import();
    }

}
