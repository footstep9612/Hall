<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MSG
 *
 * @author zhongyg
 */
class MSG {

    //put your code here
    const MSG_SUCCESS = 1; //更新或插入成功
    const MSG_FAILED = -1; //更新或插入错误
    const MSG_USER_NOT_EXIST = -104; //用户不存在
    const MSG_USER_PASSWORD_EMPTY = -101; //密码不可以为空
    const MSG_USER_NAME_EMPTY = -102; //帐号不可以为空
    const MSG_USER_LOGIN_FAIL = -103; //登录失败
    const MSG_USER_MOBILE_ERR = -105; //手机格式不正确
    const MSG_USER_MOBILE_EMPTY = -106; //手机不可以为空
    const MSG_USER_EMAIL_ERR = -107; //邮箱格式不正确
    const MSG_USER_EMAIL_EMPTY = -108; //邮箱不可以为空
    const MSG_USER_NAME_EXIST = -109; //手机或账号已存在 
    const MSG_USER_REGISTER_FAIL = -110; //注册失败 
    const MSG_OTHER_ERR = -10000; //其他错误
    const MSG_MODULE_NOT_EXIST = -601; //模块不存在
    const MSG_CONTROLLER_NOT_EXIST = -602; //控制器不存在
    const MSG_ERROR_ACTION = -603; //方法不存
    const MSG_LANGUAGE_NOT_LOAD = -604; //无法加载语言包
    const MSG_MODEL_NOT_EXIST = -605; //模型不存在或者没有定义
    const MSG_XML_TAG_ERROR = -606; //XML标签语法错误
    const MSG_DATA_TYPE_INVALID = -607; //非法数据对象
    const MSG_OPERATION_WRONG = -608; //操作出现错误
    const MSG_NOT_LOAD_DB = -609; //无法加载数据库
    const MSG_NO_DB_DRIVER = -610; //无法加载数据库驱动
    const MSG_NOT_SUPPORT_DB = -611; //系统暂时不支持数据库
    const MSG_NO_DB_CONFIG = -612; //没有定义数据库配置
    const MSG_NOT_SUPPORT = -613; //没有定义数据库配置
    const MSG_CACHE_TYPE_INVALID = -614; //无法加载缓存类型      
    const MSG_FILE_NOT_WRITABLE = -615; //目录（文件）不可写    
    const MSG_METHOD_NOT_EXIST = -616; //方法不存在 
    const MSG_CLASS_NOT_EXIST = -617; //实例化一个不存在的类 
    const MSG_CLASS_CONFLICT = -618; //类名冲突 
    const MSG_CACHE_WRITE_ERROR = -619; //缓存文件写入失败 
    const MSG_TAGLIB_NOT_EXIST = -620; //标签库未定义 
    const MSG_SELECT_NOT_EXIST = -621; //记录不存在 
    const MSG_EXPRESS_ERROR = -622; //表达式错误 
    const MSG_TOKEN_ERROR = -623; //表单令牌错误 
    const MSG_RECORD_HAS_UPDATE = -624; //记录已经更新 
    const MSG_PARAM_ERROR = -625; //参数错误或者未定义 
    const MSG_ERROR_QUERY_EXPRESS = -626; //错误的查询条件    
    const MSG_HTTP_200 = 200; //正常；请求已完成。
    const MSG_HTTP_201 = 201; //正常；紧接 POST 命令。  
    const MSG_HTTP_202 = 202; //正常；已接受用于处理，但处理尚未完成。  
    const MSG_HTTP_203 = 203; //正常；部分信息 — 返回的信息只是一部分。  
    const MSG_HTTP_204 = 204; //正常；无响应 — 已接收请求，但不存在要回送的信息。 

    const MSG_HTTP_301 = -301; //已移动 — 请求的数据具有新的位置且更改是永久的。  
    const MSG_HTTP_302 = -302; //已找到 — 请求的数据临时具有不同 URI。  
    const MSG_HTTP_303 = -303; //请参阅其它 — 可在另一 URI 下找到对请求的响应，且应使用 GET 方法检索此响应。  
    const MSG_HTTP_304 = -304; //未修改 — 未按预期修改文档。  
    const MSG_HTTP_305 = -305; //使用代理 — 必须通过位置字段中提供的代理来访问请求的资源。  
    const MSG_HTTP_306 = -306; //未使用 — 不再使用；保留此代码以便将来使用。  
    const MSG_HTTP_400 = -400; //错误请求 — 请求中有语法问题，或不能满足请求。  
    const MSG_HTTP_401 = -401; //未授权 — 未授权客户机访问数据。  
    const MSG_HTTP_402 = -402; //需要付款 — 表示计费系统已有效。  
    const MSG_HTTP_403 = -403; //禁止 — 即使有授权也不需要访问。  
    const MSG_HTTP_404 = -404; //找不到 — 服务器找不到给定的资源；文档不存在。  
    const MSG_HTTP_407 = -407; //代理认证请求 — 客户机首先必须使用代理认证自身。  
    const MSG_HTTP_415 = -415; //介质类型不受支持 — 服务器拒绝服务请求，因为不支持请求实体的格式。  
    const MSG_HTTP_500 = -500; //内部错误 — 因为意外情况，服务器不能完成请求。  
    const MSG_HTTP_501 = -501; //未执行 — 服务器不支持请求的工具。  
    const MSG_HTTP_502 = -502; //错误网关 — 服务器接收到来自上游服务器的无效响应。  
    const MSG_HTTP_503 = -503; //无法获得服务 — 由于临时过载或维护，服务器无法处理请求。

    public static function getMessage($code, $lang = 'en') {
        $map = [
            self::MSG_SUCCESS => '更新或插入成功',
            self::MSG_FAILED => '更新或插入错误',
            self::MSG_MODULE_NOT_EXIST => '模块不存在',
            self::MSG_CONTROLLER_NOT_EXIST => '控制器不存在',
            self::MSG_ERROR_ACTION => '方法不存',
            self::MSG_LANGUAGE_NOT_LOAD => '无法加载语言包',
            self::MSG_MODEL_NOT_EXIST => '模型不存在或者没有定义',
            self::MSG_XML_TAG_ERROR => 'XML标签语法错误',
            self::MSG_DATA_TYPE_INVALID => '非法数据对象',
            self::MSG_OPERATION_WRONG => '操作出现错误',
            self::MSG_NOT_LOAD_DB => '无法加载数据库',
            self::MSG_NO_DB_DRIVER => '无法加载数据库驱动',
            self::MSG_NOT_SUPPORT_DB => '系统暂时不支持数据库',
            self::MSG_NO_DB_CONFIG => '没有定义数据库配置',
            self::MSG_NOT_SUPPORT => '没有定义数据库配置',
            self::MSG_CACHE_TYPE_INVALID => '无法加载缓存类型      ',
            self::MSG_FILE_NOT_WRITABLE => '目录（文件）不可写    ',
            self::MSG_METHOD_NOT_EXIST => '方法不存在 ',
            self::MSG_CLASS_NOT_EXIST => '实例化一个不存在的类 ',
            self::MSG_CLASS_CONFLICT => '类名冲突 ',
            self::MSG_CACHE_WRITE_ERROR => '缓存文件写入失败 ',
            self::MSG_TAGLIB_NOT_EXIST => '标签库未定义 ',
            self::MSG_SELECT_NOT_EXIST => '记录不存在 ',
            self::MSG_EXPRESS_ERROR => '表达式错误 ',
            self::MSG_TOKEN_ERROR => '表单令牌错误 ',
            self::MSG_RECORD_HAS_UPDATE => '记录已经更新 ',
            self::MSG_PARAM_ERROR => '参数错误或者未定义 ',
            self::MSG_ERROR_QUERY_EXPRESS => '错误的查询条件',
            self::MSG_HTTP_200 => '正常；请求已完成。',
            self::MSG_HTTP_201 => '正常；紧接 POST 命令。  ',
            self::MSG_HTTP_202 => '正常；已接受用于处理，但处理尚未完成。  ',
            self::MSG_HTTP_203 => '正常；部分信息 — 返回的信息只是一部分。  ',
            self::MSG_HTTP_204 => '正常；无响应 — 已接收请求，但不存在要回送的信息。  ',
            self::MSG_HTTP_301 => '已移动 — 请求的数据具有新的位置且更改是永久的。  ',
            self::MSG_HTTP_302 => '已找到 — 请求的数据临时具有不同 URI。  ',
            self::MSG_HTTP_303 => '请参阅其它 — 可在另一 URI 下找到对请求的响应，且应使用 GET 方法检索此响应。  ',
            self::MSG_HTTP_304 => '未修改 — 未按预期修改文档。  ',
            self::MSG_HTTP_305 => '使用代理 — 必须通过位置字段中提供的代理来访问请求的资源。  ',
            self::MSG_HTTP_306 => '未使用 — 不再使用；保留此代码以便将来使用。  ',
            self::MSG_HTTP_400 => '错误请求 — 请求中有语法问题，或不能满足请求。  ',
            self::MSG_HTTP_401 => '未授权 — 未授权客户机访问数据。  ',
            self::MSG_HTTP_402 => '需要付款 — 表示计费系统已有效。  ',
            self::MSG_HTTP_403 => '禁止 — 即使有授权也不需要访问。  ',
            self::MSG_HTTP_404 => '找不到 — 服务器找不到给定的资源；文档不存在。  ',
            self::MSG_HTTP_407 => '代理认证请求 — 客户机首先必须使用代理认证自身。  ',
            self::MSG_HTTP_415 => '介质类型不受支持 — 服务器拒绝服务请求，因为不支持请求实体的格式。  ',
            self::MSG_HTTP_500 => '内部错误 — 因为意外情况，服务器不能完成请求。  ',
            self::MSG_HTTP_501 => '未执行 — 服务器不支持请求的工具。  ',
            self::MSG_HTTP_502 => '错误网关 — 服务器接收到来自上游服务器的无效响应。  ',
            self::MSG_HTTP_503 => '无法获得服务 — 由于临时过载或维护，服务器无法处理请求。',
            self::MSG_OTHER_ERR => '其他错误',
            self::MSG_USER_NOT_EXIST => '用户不存在',
            self::MSG_USER_NOT_EXIST => '用户不存在!',
            self::MSG_USER_PASSWORD_EMPTY => '密码不可以为空!',
            self::MSG_USER_NAME_EMPTY => '帐号不可以为空!',
            self::MSG_USER_LOGIN_FAIL => '登录失败!',
            self::MSG_USER_MOBILE_ERR => '手机格式不正确!',
            self::MSG_USER_MOBILE_EMPTY => '手机不可以为空!',
            self::MSG_USER_EMAIL_ERR => '邮箱格式不正确!',
            self::MSG_USER_EMAIL_EMPTY => '邮箱不可以为空!',
            self::MSG_USER_NAME_EXIST => '手机或账号已存在!',
            self::MSG_USER_REGISTER_FAIL => '注册失败!',
        ];
        return isset($map[$code]) ? $map[$code] : $map[self::MSG_OTHER_ERR];
    }

}
