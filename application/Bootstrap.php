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
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
        $AutoloadPlugin = new AutoloadPlugin();
        $dispatcher->registerPlugin($AutoloadPlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {

        $router = Yaf_Dispatcher::getInstance()->getRouter();
        //创建一个路由协议实例
        $route = new Yaf_Route_Rewrite('/v1/material_cat/:name', ['module' => 'V1',
            'controller' => 'Materialcat',
            'action' => ':name']);
        //使用路由器装载路由协议
        $router->addRoute('Materialcat', $route);

        //echo "_initRoute call second<br/>\n";
        //$router = Yaf_Dispatcher::getInstance()->getRouter();
        /**
         * add the routes defined in ini config file
         */
        //$router->addConfig(Yaf_Registry::get("config")->routes);
        /**
         * test this route by access http://yourdomain.com/product/list/?/?/
         */
        /* $route  = new Yaf_Route_Rewrite(
          "/product/list/:id/:name",
          array(
          "controller" => "product",
          "action"	 => "info",
          )
          ); */
        //$route  = new Yaf_Route_Rewrite(
        //"/User/index/:id/:name",
        //array(
        //"controller" => "User",
        //"action"	 => "index",
        //)
        //);
        //$router->addRoute('User', $route);
    }

}
