<?php

/**
 * 当有未捕获的异常, 则控制流会流到这里
 */
class ErrorController extends Yaf_Controller_Abstract {

    protected $code = "1";
    protected $message = '';
    protected $lang = 'en';

    public function init() {
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function errorAction($exception) {
        /* error occurs */
        switch ($exception->getCode()) {
            case YAF_ERR_NOTFOUND_MODULE:
                LOG::write($exception->getMessage(), LOG::ERR);
                $this->setCode(MSG::MSG_MODULE_NOT_EXIST);
                $this->jsonReturn();
                break;
            case YAF_ERR_NOTFOUND_CONTROLLER:
                LOG::write($exception->getMessage(), LOG::ERR);
                $this->setCode(MSG::MSG_CONTROLLER_NOT_EXIST);
                $this->jsonReturn();
                break;
            case YAF_ERR_NOTFOUND_ACTION:
                LOG::write($exception->getMessage(), LOG::ERR);
                $this->setCode(MSG::MSG_ERROR_ACTION);
                $this->jsonReturn();
                break;
            case YAF_ERR_NOTFOUND_VIEW:
                LOG::write($exception->getMessage(), LOG::ERR);
                $this->setCode(MSG::MSG_OTHER_ERR);
                $this->jsonReturn();
            default :

                LOG::write($exception->getMessage(), LOG::ERR);
                $this->setCode(MSG::MSG_OTHER_ERR);
                $this->jsonReturn();
                break;
        }
    }

    public function testAction() {
        
    }

    /*
     * 设置语言
     */

    public function setLang($lang = 'en') {
        $this->lang = $lang;
    }

    /*
     * 获取语言
     */

    public function getLang() {
        return $this->lang;
    }

    /*
     * 设置信息编码
     */

    public function setCode($code) {
        $this->code = $code;
    }

    /*
     * 设置提示信息
     * 以后会和错误码同一起来
     */

    public function setMessage($message) {
        $this->message = $message;
    }

    /*
     * 获取信息编码
     */

    public function getCode() {
        return $this->code;
    }

    /*
     * 获取提示信息
     */

    public function getMessage() {
        if (!$this->message) {
            $message = MSG::getMessage($this->getCode(), $this->getLang());
            $this->message = $message;
            return $message;
        }
        return $this->message;
    }

    /*     * *******************------公共输出JSON函数------*************************
     * @param mix $data // 发送到客户端的数据 如果$data 中含有code 则直接输出
     * 否则 与$this->code $this->message 组合输出
     * $this ->message 有待完善 如果错误码都有对应的message
     * 可以和错误码表经过对应 输出错误信息
     * @return json
     */

    public function jsonReturn($data = [], $type = 'JSON') {
        header('Content-Type:application/json; charset=utf-8');
        if (isset($data['code'])) {
            exit(json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            if ($data) {
                $send['data'] = $data;
            }
            $send['code'] = $this->getCode();
            if ($send['code'] == "1" && !$this->getMessage()) {
                $send['message'] = '成功!';
            } elseif (!$this->getMessage()) {
                $send['message'] = '未知错误!';
            } else {
                $send['message'] = $this->getMessage();
            }
            exit(json_encode($send, JSON_UNESCAPED_UNICODE));
        }
    }

}
