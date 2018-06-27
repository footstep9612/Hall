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
     * 定制服务
     */
    const CUSTOM_FAILED = -201;
    const CUSTOM_SUCCESS = 1;
    /**
     * 授信
     */
    const CREDIT_FAILED = -1;
    const CREDIT_SUCCESS = 1;

    /**
     * 信息映射
     */
    private static $message = array(
        'zh' => array(
            '0' => '失败',
            '1' => '成功',
            '-1' => '失败',
            '100' => '已经存在',
            '302' => '请求方法有误',


            /**
             * KLP定义门户登录注册流程
             */
            '130' => '易瑞平台密码找回',

            '102' => '登录成功',
            '103' => '注册成功',
            '104' => '邮箱验证码发送成功',
            '135' => '恭喜您，订单提交成功！',
            '136' => '验证成功！',
            '137' => '邮箱激活成功!',
            '138' => '恭喜您，登录成功并提交定制!',
            '139' => '恭喜您，注册成功并提交定制！',
            '140' => '恭喜您，订单提交成功！',
            '141' => '已发送',
            '142' => '密码重置成功!',
            '143' => '恭喜您，提交成功!',
            '144' => '密码修改成功!',
            '-145' => '账号不存在或已冻结',

            '-101' => '失败',
            '-105' => '注册失败',
            '-106' => '登录失败',
            '-107' => '抱歉，提交失败',

            '-110' => '请输入你的密码',
            '-111' => '请输入公司邮箱',
            '-112' => '邮箱格式不正确',
            '-113' => '请输入您的手机号码',
            '-114' => '请选择国家',
            '-115' => '请输入联系人姓名',

            '-116' => '帐号不可以都为空',
            '-117' => '邮箱已存在',
            '-118' => '请输入您的公司名称',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => '链接失效',
            '-122' => '邮箱不存在',
            '-123' => '请输入正确格式的内容',
            '-124' => '账号或密码错误',
            '-125' => '公司名字已存在',
            '-126' => '抱歉，提交失败',
            '-127' => '6到20字符，字母加数字组合',
            '-128' => '8-20个字符，包括字母和数字',
            '-129' => '公司名称不能为空',
            '-130' => '联系人姓名不能为空',
            '-131' => '国家不能为空',
            '-132' => '密码不匹配',
            '-133' => '链接失效',
            '-134' => '邮箱不能为空',
            '-135' => '密码修改失败!',
            '-136' => '原密码错误!',
            '-137' => '验证失败!',

        ),
        'en' => array(
            '1'   => 'Success!',
            '-1'  => 'Failed!',
            /**
             * KLP定义邮件信息
             */
            '2001-1'=>'Hello! Your order has been',  //客户
            '2001-2'=>'submitted successfully',
            '2002-1'=>'Hello! You receive a',
            '2002-2'=>'pending order',

            '102' => 'Login successfully',
            '103' => 'Registered successfully',
            '130' => 'Password retrieval on ERUI platform',
            '135' => 'Congratulations, the order was submitted successfully！',
            '136' => 'The verification is successful！',
            '137' => 'E-mail activation success!',
            '138' => 'Congratulations, login successfully and submit your request!',
            '139' => 'Congratulations, register successfully and submit your request!',

            '-105' => 'Registration failed',
            '-106' => 'Login failed',
            '-107' => 'Sorry, the submission failed',
            '-110' => 'Please enter your password',
            '-111' => 'Please enter your company Email',
            '-112' => 'The email format is incorrect',
            '-113' => 'Please enter your cellphone number',
            '-114' => 'Please select country',
            '-115' => 'Please enter the contact person\'s name',
            '-116' => '帐号不可以都为空',
            '-117' => 'Email already exists',
            '-118' => 'Please enter your company name',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => 'Link invalid',
            '-122' => 'Email does not exist',
            '-123' => 'Please enter the content in the correct format',
            '-124' => 'Account or password error',
            '-125' => 'Company name already exists',
            '-126' => 'Sorry, the submission failed',
            '-132' => 'Passwords do not match',
            '-133' => 'Link invalid',
            '-134' => 'The email address is required and can\'t be empty',
            '-135' => 'Password is reset failed!',
            '-136' => 'Current password is error!',
            '-137' => 'Verification failed!',
            '142'  => 'Password is reset successfully!',
            '-145' => 'The account does not exit or has been frozen',
        ),
        'es' => array(
            '102' => '登陆成功',
            '103' => 'Registrado correctamente',
            '130' => 'Recuperación de contraseña en la plataforma ERUI',

            '-105' => 'Registro fallido',
            '-106' => 'Error de inicio de sesion',
            '-107' => 'Lo siento, la presentación falló',
            '-110' => 'Por favor, introduzca su contraseña',
            '-111' => 'Por favor, introduzca su empresa correo electrónico',
            '-112' => 'El formato del correo electrónico es incorrecto',
            '-113' => 'Por favor ingrese su número de teléfono celular',
            '-114' => 'Por favor seleccione un país',
            '-115' => 'Por favor ingrese el nombre de la persona de contacto',
            '-116' => '帐号不可以都为空',
            '-117' => 'El Email ya existe',
            '-118' => 'Por favor ingrese el nombre de su compañía',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => 'Enlace inválido',
            '-122' => 'El  Email no existe',
            '-123' => 'Por favor ingrese el contenido en el formato correcto',
            '-124' => 'Error de cuenta o contraseña',
            '-125' => 'El nombre de la compañía ya existe',
            '136' => 'La verificación es exitosa！',
            '-137' => 'Fallo en la verificación!',
            '142' => 'Restablecimiento de contraseña con éxito!',
            '-145' => 'La cuenta no existe o ha sido congelada',

        ),
        'ru' => array(
            '1' => 'успешно',
            '-1' => 'неудача',
            '102' => 'Успешный Вход',
            '103' => 'Успешная регистрация',
            '130' => 'Восстановление пароля платформы ERUI',

            '2001-1'=>'Здравствуйте! Ваш заказ отправлен',//客户
            '2001-2'=>'успешно',
            '2002-1'=>'Здравствуйте! Вы получаете',
            '2002-2'=>'отложенный ордер',

            '-105' => 'Ошибка регистрации',
            '-106' => 'Ошибка входа',
            '-107' => 'Прошу прощения! Ошибка отправки',
            '-110' => 'Пожалуйста, введите ваш пароль',
            '-111' => 'Пожалуйста, введите адрес вашей компании',
            '-112' => 'Неправильный формат электронной почты',
            '-113' => 'Пожалуйста, введите номер вашего мобильного телефона',
            '-114' => 'Пожалуйста,  выберите страну',
            '-115' => 'Введите имя контактного лица',
            '-116' => '帐号不可以都为空',
            '-117' => 'Электронная почта уже существует',
            '-118' => 'Введите название вашей компании',
            '-119' => '经营范围不可以都为空',
            '-120' => '意向产品不可以都为空',
            '-121' => 'Ошибка при связи',
            '-122' => 'Электронная почта не существует',
            '-123' => 'Введите содержимое в правильном формате',
            '-124' => 'Ошибка учетной записи или пароля',
            '-125' => 'Название компании уже существует',
            '-127' => 'От 6 до 20 символов, цифр и буквы',
            '-128' => '8-20 символов, включая буквы и цифры',
            '-129' => 'Название компании не может быть пустым',
            '-130' => 'Контакт не может быть пустым',
            '-131' => 'Страна не может быть пуста',
            '-132' => 'Пароль не совпадает',
            '-133' => 'Ошибка при связи',
            '-134' => 'Требуется адрес электронной почты и не может быть пустым',
            '-135' => 'Ошибка смены пароля!',
            '-136' => 'Исходный пароль неверен!',
            '-137' => 'Проверка не пройдена!',

            '136' => 'Проверка выполнена успешно！',
            '138' => 'Поздравляем, авторизация прошла успешно , отправьте свой запрос!',
            '139' => 'Поздравляем!Регистрация прошла успешна, отправьте запрос!',
            '140'  => 'Поздравляем, заказ был отправлен успешно',
            '141' => 'Отправлено',
            '142' => 'Восстановление пароля успешно',
            '143' => 'Поздравляем!Отправлено успешно',
            '144' => 'Пароль успешно изменен!',
            '-145' => 'Учетная запись не существует или была заморожена',
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