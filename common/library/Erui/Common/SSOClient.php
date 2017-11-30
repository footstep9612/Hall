<?php
namespace Erui\Common;
/**
* SSO客户端
* SSO客户端验证工具，
* @author: zhengkq
**/
class SSOClient{
    /**
    * SSO服务器验证地址
    **/
    private $sso_server = "http://sso.eruidev.com/api/checkToken";
    
    /**
    * Token 名称
    **/
    const TOKENT_NAME = 'eruitoken';
    /**
    * SSO Token
    **/
    private $sso_token = "";
    
    /*
    * 当前登录账号信息
    **/
    private $user = [];
    
    /**
    * 应用实例
    * 接口实现为单例模式
    **/
    private static $instance = null;
    
    public function __construct(){
        if($this->getSsoToken() != ""){
            $this->Validate();
        }
        if(defined('SSO_URL')){
            $this->sso_server = SSO_URL;
        }
    }
    
    /**
    * 获取SSO Token
    * 默认由Cookie读取，其次从Http header读取，token变量名称为eruitoken
    * @author: zhengkq
    * @return string $sso_token 返回sso token
    **/
    private function getSsoToken(){
        $httpToken = 'HTTP_'.strtoupper(self::TOKENT_NAME);
        if(isset($_COOKIE[self::TOKENT_NAME]) && !empty($_COOKIE[self::TOKENT_NAME])){
            $this->sso_token = $_COOKIE[self::TOKENT_NAME] ;
        }elseif(isset($_SERVER[$httpToken]) && !empty($_SERVER[$httpToken])){
            $this->sso_token = $_SERVER[$httpToken] ;
        }else{
            $this->sso_token = "";
        }
        $GLOBALS['SSO_TOKEN'] = $this->sso_token;
        return $this->sso_token;
    }
    
    /**
    * 验证Token的合法性
    * 根据token获取用户信息
    **/
    private function Validate(){
        $http = [
            'http'=>[
                'method'=>'POST',
                'header'=>"Content-Type:application/json\r\n",
                'content'=>json_encode(['token'=>$this->sso_token])
            ]
        ];
        $context = stream_context_create($http);
        $result = file_get_contents($this->sso_server,true,$context);
        $this->user = json_decode($result,true);
        return $this->user;
    }
    
    /**
    * 检查用户是否登录
    * 检查标准为：根据token获取的用户ID 大于0
    **/
    public function IsLogined(){
        return isset($this->user['id']) && $this->user['id'] >0;
    }
    
    /**
    * 获取用户登录信息
    **/
    public function getUser(){
        return $this->user;
    }
    
    /**
    * 开始验证用户信息
    **/
    public static function Start($url = ''){
        if(!empty($url)){
            $this->sso_server = $url;
        }
        if(self::$instance == null){
            self::$instance = new self();
        }
        if(self::$instance->IsLogined() == false){
            header("Content-Type: application/json");
            exit(json_encode(['code'=>403,'message'=>'Token Expired.']));
        }else{
            $GLOBALS['SSO_USER'] =  self::$instance->getUser();
        }
    }
}
