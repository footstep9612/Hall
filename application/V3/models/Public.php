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

    private static $op_log_id = 0;
//put your code here
// 数据表前缀

    protected $tablePrefix = '';
    protected $tableName = '';

    public function __construct($str = '') {
        parent::__construct($str);
    }

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

    /* 查询条件判断
     * @param array $where //引用 返回的where条件
     * @param array $condition //引用 搜索条件
     * @param string $name // 查询的字段
     * @param string $type // 默认值 string bool  like array
     * @param string $field // 组合条件的字段
     * @param string $default 默认值 暂时只支持 string 和bool
     * @date  2017-8-1 9:13:41
     * @return null
     */

    protected function _getValue(&$where, &$condition, $name, $type = 'string', $field = null, $default = null) {
        if (!$field) {
            $field = $name;
        }
        if ($type === 'int') {
            if (isset($condition[$name])) {
                $where[$field] = intval($condition[$name]);
            }
        } elseif ($type === 'string') {
            if (isset($condition[$name]) && trim($condition[$name])) {
                $where[$field] = trim($condition[$name]);
            } elseif ($default) {
                $where[$field] = trim($default);
            }
        } elseif ($type === 'bool') {
            if (isset($condition[$name]) && trim($condition[$name])) {
                $flag = trim($condition[$name]) == 'Y' ? 'Y' : 'N';
                $where[$field] = $flag;
            } elseif ($default) {
                $where[$field] = trim($default);
            }
        } elseif ($type === 'like') {
            if (isset($condition[$name]) && trim($condition[$name])) {
                $where[$field] = ['like', '%' . trim($condition[$name]) . '%'];
            }
        } elseif ($type === 'array') {
            if (isset($condition[$name]) && is_array($condition[$name])) {
                $where[$field] = ['in', $condition[$name]];
            }
        } elseif ($type == 'between') {

            if (isset($condition[$name . '_start']) && isset($condition[$name . '_end']) && $condition[$name . '_end'] && $condition[$name . '_start']) {
                $created_at_start = trim($condition[$name . '_start']);
                $created_at_end = trim($condition[$name . '_end']);
                $where[$field] = ['between', $created_at_start . ',' . $created_at_end,];
            } elseif (isset($condition[$name . '_start']) && $condition[$name . '_start']) {
                $created_at_start = trim($condition[$name . '_start']);

                $where[$field] = ['egt', $created_at_start];
            } elseif (isset($condition[$name . '_end']) && $condition[$name . '_end']) {
                $created_at_end = trim($condition[$name . '_end']);
                $where[$field] = ['elt', $created_at_end,];
            }
        }
    }

    /**
     * SQL指令安全过滤
     * @access public
     * @param string $str  SQL字符串
     * @return string
     */
    public function escapeString($str) {

        return $this->db()->escapeString(trim($str));
    }

    /**
     * 分页处理
     * @param array $condition 条件
     * @return array
     * @author zyg
     *
     */
    protected function _getPage($condition) {
        $pagesize = 10;
        $start_no = 0;
        if (isset($condition['pagesize'])) {
            $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        if (isset($condition['current_no'])) {
            $start_no = intval($condition['current_no']) > 0 ? (intval($condition['current_no']) * $pagesize - $pagesize) : 0;
        }
        return [$start_no, $pagesize];
    }

    /**
     * 判断是否存在
     * @param  mix $where 搜索条件
     * @return mix
     * @date 2017-08-01
     * @author zyg
     */
    protected function _exist($where) {
        try {
            $row = $this->where($where)
                    ->field('id')
                    ->find();
            return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
