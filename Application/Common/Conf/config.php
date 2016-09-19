<?php
return array(
    //函数库
    'LOAD_EXT_FILE'       =>'sms,core,goods',  
    //配置文件
    'LOAD_EXT_CONFIG'     => 'database.config,api.config,env.config,pay.config,shop.config,add.config,upload.config',  
    //URL访问模式
    'URL_MODEL'           => 2,
    //模块设定
    'MODULE_ALLOW_LIST'   => array('Shop','Tsht','Admin','Home'),
    // 默认模块
    'DEFAULT_MODULE'      => 'Shop',  
    //开关
    'VAR_URL_PARAMS'      => '_URL_', //PATHINFO URL参数变量
    //模板编译缓存,设为false则每次都会重新编译
    'TMPL_CACHE_ON'       =>  false, 
    //默认图
    'default_goods_image' => '05164733457866082.jpg',
    //开发模式 0 debug 1 test 2 product
    'MODE'                => '0',
);