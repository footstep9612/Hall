<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Ap调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    protected $config;

    public function _initSession($dispatcher) {
        Yaf_Session::getInstance()->start();
    }

    public function _initConfig() {
        $this->config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config", $this->config);
        //关闭自动加载模板
        Yaf_Dispatcher::getInstance()->autoRender(false);
        //设置默认时区
        date_default_timezone_set("PRC");
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
        $AutoloadPlugin = new AutoloadPlugin();
        $dispatcher->registerPlugin($AutoloadPlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {

        $router = Yaf_Dispatcher::getInstance()->getRouter();

        $dispatcher->disableView();

        $Request = $dispatcher->getRequest();

        preg_match('/\/v1\/([a-zA-Z0-9\_\-]+)\/(.*?)/', $Request->getRequestUri(), $out);

        $ControllerName = ucfirst($out[1]);
        $ControllerName = str_replace('_', '', $ControllerName);
        //创建一个路由协议实例
        $route = new Yaf_Route_Rewrite('/v1/:Controller/:Action', ['module' => 'V1',
            'controller' => $ControllerName,
            'action' => ':Action']);
        //使用路由器装载路由协议
        $router->addRoute('Materialcat', $route);
    }

}
