<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/11/28
 * Time: 10:10
 */

class ShopMsg {
    /**
     * 系统级
     */
    const FAILED = 0;
    const SUCCESS = 1;
    const EXIST =100;
    /**
     * 门户登录注册级错误
     */
    const SHOP_FAILED = -101;
    const SHOP_SUCCESS = 1;
    const SHOP_REGISTER = -105;
    const SHOP_LOGIN = -106;



    /**
     * 信息映射
     */
    private static $message = array(
        'zh' => array(
            '0' => '失败',
            '1' => '成功',
            '100' => '已经存在',
            '302' => '请求方法有误',

            /**
             * KLP定义门户登录注册流程
             */
            '130' => '易瑞平台密码找回',

            '102' => '登陆成功',
            '103' => '注册成功',
            '104' => '邮箱验证码发送成功',

            '-101' => '失败',
            '-105' => '注册失败',
            '-106' => '登录失败',

            '-110' => '请输入你的密码',
            '-111' => '请输入公司邮箱',
            '-112' => '邮箱格式不正确',
            '-113' => '请输入您的手机号码',
            '-114' => '选择国家',
            '-115' => '请输入联系人姓名',

            '-116' => '帐号不可以都为空',
            '-117' => '邮箱已存在',
            '-118' => '请输入您的公司名称',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => '链接失效',
            '-122' => '请输入注册邮箱',
        ),
        'en' => array(
            '130' => 'Password retrieval on ERUI platform',

            '103' => 'Registered successfully',

            '-105' => 'Registration failed',
            '-106' => 'Login failed',
            '-110' => 'Please enter your password',
            '-111' => 'Please enter your company Email',
            '-112' => 'The email format is incorrect',
            '-113' => 'Please enter your cellphone number',
            '-114' => 'Country',
            '-115' => 'Please enter the contact person\'s name',
            '-116' => '帐号不可以都为空',
            '-117' => 'Email already exists',
            '-118' => 'Please enter your company name',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => 'Link invalid',
            '-122' => 'Enter e-mail address',
        ),
        'es' => array(
            '130' => 'Recuperación de contraseña en la plataforma ERUI ',

            '103' => 'Registrado correctamente',

            '-105' => 'Registro fallido',
            '-106' => 'Error de inicio de sesion',
            '-110' => 'Por favor, introduzca su contraseña',
            '-111' => 'Por favor, introduzca su empresa correo electrónico',
            '-112' => 'El formato del correo electrónico es incorrecto',
            '-113' => 'Por favor ingrese su número de teléfono celular',
            '-114' => 'Seleccionar un país',
            '-115' => 'Por favor ingrese el nombre de la persona de contacto',
            '-116' => '帐号不可以都为空',
            '-117' => 'El Email ya existe',
            '-118' => 'Por favor ingrese el nombre de su compañía',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => 'Enlace inválido',
            '-122' => 'Por favor ingrese el correo electrónico registrado',
        ),
        'ru' => array(
            '130' => 'Восстановление пароля платформы ERUI',

            '103' => 'Успегная регистрация',

            '-105' => 'Ошибка регистрации',
            '-106' => 'Ошибка входа',
            '-110' => 'Пожалуйста, введите ваш пароль',
            '-111' => 'Пожалуйста, введите адрес вашей компании',
            '-112' => 'Неправильный формат электронной почты',
            '-113' => 'Пожалуйста, введите номер вашего мобильного телефона',
            '-114' => 'Выберите страну',
            '-115' => 'Введите имя контактного лица',
            '-116' => '帐号不可以都为空',
            '-117' => 'Электронная почта уже существует',
            '-118' => 'Введите название вашей компании',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => 'Ошибка при связи',
            '-122' => ' введите зарегистрированную электронную почту',
        ),
    );



    /**
     * 返回信息
     * @param number $code 错误码
     * @param string $msg  自定义错误信息
     * @param string $lang 语言(默认英文)
     */
    public static function getMessage($code = '1', $lang='en', $msg = '') {
        $msg = $msg ? $msg : (isset(self::$message[$lang][$code]) ? self::$message[$lang][$code] : '');
        return $msg;
    }

}