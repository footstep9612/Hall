<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PublicModel
 *
 * @author zyg
 */
class PublicModel extends Model {

    const MSG_SUCCESS = 0; //成功
    const MSG_TOKEN_ERR = -101; //成功
    const MSG_SERVER_ERR = 500; //服务器内部发生错误
    const MSG_PARAMETER_ERR = 100000; //参数错误
    // const MSG_TOKEN_ERR = 100001; //您所提供的 AuthToken 无效！
    const MSG_NO_PERMISSIONS = 100002; //没有权限
    const MSG_MOBILE_EXIST = 300000; //手机号已经注册
    const MSG_PASSWORD_DIFFER = 300001; //两次输入密码不一致
    const MSG_VERIFICATION_ERR = 300002; //输入验证码不正确
    const MSG_VERIFICATION_NOT_OBTAIN = 300003; //您还没有获取验证码
    const MSG_MOBILE_NOT_EXIST = 300004; //该手机号还没有注册
    const MSG_PASSWORD_ERR = 300005; //输入密码不正确
    const MSG_INFO_ERR = 300006; //您的信息不完整
    const MSG_NAME_ERR = 300007; //没有找到该用户
    const MSG_INVALID_USER = 300008; //无效的用户
    const MSG_USERNAME_CANNOTEMPTY = 300009; //用户名不能为空
    const MSG_OTHER_ERR = 300010; //其他错误
    const MSG_PASSWORD_CANNOTEMPTY = 300011; //密码不能为空
    const MSG_EMAIL_CANNOTEMPTY = 300012; //邮件不能为空

//put your code here
// 数据表前缀

    protected $tablePrefix = 't_';
    protected $tableName = '';

    public function __construct($str='') {
        parent::__construct($str);
    }

//
//    /**
//     * 根据条件获取查询条件
//     * @param mix $condition
//     * @return mix
//     * @author zyg
//     */
//    protected function getcondition($condition = []) {
//
//    }
//
//    /**
//     * 获取数据条数
//     * @param mix $condition
//     * @return mix
//     * @author zyg
//     */
//    public function getcount($condition = []) {
//
//    }
//
//    /**
//     * 获取列表
//     * @param mix $condition
//     * @return mix
//     * @author zyg
//     */
//    public function getlist($condition = []) {
//
//    }
//
//    /**
//     * 获取列表
//     * @param  string $code 编码
//     * @param  int $id id
//     * @param  string $lang 语言
//     * @return mix
//     * @author zyg
//     */
//    public function info($code = '', $id = '', $lang = '') {
//
//    }
//
//    /**
//     * 删除数据
//     * @param  string $code 编码
//     * @param  int $id id
//     * @param  string $lang 语言
//     * @return bool
//     * @author zyg
//     */
//    public function delete_data($code = '', $id = '', $lang = '') {
//
//    }
//
//    /**
//     * 更新数据
//     * @param  mix $upcondition 更新条件
//     * @return bool
//     * @author zyg
//     */
//    public function update_data($upcondition = []) {
//
//    }
//
//    /**
//     * 新增数据
//     * @param  mix $createcondition 新增条件
//     * @return bool
//     * @author zyg
//     */
//    public function create_data($createcondition = []) {
//
//    }

    public function getMessage($code) {
        switch ($code) {
            case self::MSG_SUCCESS :
                return ['code' => self::MSG_SUCCESS, 'message' => '成功'];
            case self::MSG_SERVER_ERR :
                return ['code' => self::MSG_PARAMETER_ERR,
                    'message' => '服务器内部发生错误'];

            case self::MSG_PARAMETER_ERR :
                return ['code' => self::MSG_PARAMETER_ERR,
                    'message' => '参数错误'];
            case self::MSG_TOKEN_ERR :
                return ['code' => self::MSG_TOKEN_ERR,
                    'message' => '您所提供的 AuthToken 无效'];
            case self::MSG_NO_PERMISSIONS :
                return ['code' => self::MSG_NO_PERMISSIONS,
                    'message' => '没有权限'];
            case self::MSG_MOBILE_EXIST :
                return ['code' => self::MSG_MOBILE_EXIST,
                    'message' => '手机号已经注册'];
            case self::MSG_PASSWORD_DIFFER :
                return ['code' => self::MSG_MOBILE_EXIST,
                    'message' => '两次输入密码不一致'];

            case self::MSG_VERIFICATION_ERR :
                return ['code' => self::MSG_VERIFICATION_ERR,
                    'message' => '输入验证码不正确'];

            case self::MSG_MOBILE_NOT_EXIST :

                return ['code' => self::MSG_MOBILE_NOT_EXIST,
                    'message' => '该手机号还没有注册'];

            case self::MSG_PASSWORD_ERR :

                return ['code' => self::MSG_PASSWORD_ERR,
                    'message' => '输入密码不正确'];

            case self::MSG_INFO_ERR :
                return ['code' => self::MSG_INFO_ERR,
                    'message' => '您的信息不完整'];
            case self::MSG_NAME_ERR :
                return ['code' => self::MSG_NAME_ERR,
                    'message' => '没有找到该用户'];
            case self::MSG_INVALID_USER :
                return ['code' => self::MSG_INVALID_USER,
                    'message' => '无效的用户'];

            case self::MSG_USERNAME_CANNOTEMPTY :
                return ['code' => self::MSG_USERNAME_CANNOTEMPTY,
                    'message' => '用户名不能为空'];
            case self::MSG_PASSWORD_CANNOTEMPTY :
                return ['code' => self::MSG_PASSWORD_CANNOTEMPTY,
                    'message' => '密码不能为空'];
            case self::MSG_EMAIL_CANNOTEMPTY:
                return ['code' => self::MSG_EMAIL_CANNOTEMPTY,
                    'message' => '邮件不能为空'];
            default :
                return ['code' => self::MSG_OTHER_ERR,
                    'message' => '其他错误!'];
        }
    }

}
