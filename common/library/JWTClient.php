<?php

/*
  jwt token加密
 */
include_once MYPATH . "/vendor/autoload.php";

use \Firebase\JWT\JWT;

class JWTClient {

    private $tokenkey; //key
    private $tokenalg; //加密方式

    public function __construct() {
        $this->tokenkey = Yaf_Application::app()->getConfig()->tokenkey;
        $this->tokenalg = Yaf_Application::app()->getConfig()->tokenalg;
    }

    /*
      加密
      @param $payload array
      @return string
     */

    public function encode($payload) {
        return JWT::encode($payload, $this->tokenkey, $this->tokenalg, $keyId = null, $head = null);
    }

    /*
      解密
      @param $jwt	string
      @return array
     */

    public function decode($jwt) {
        return JWT::decode($jwt, $this->tokenkey, array($this->tokenalg));
    }

}
