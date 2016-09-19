<?php 
return array(
    //模版设置
        'TMPL_PARSE_STRING'      => array(
                    '__CSS__'    => __ROOT__.'/Public/css', 
                    '__I__'    => __ROOT__.'/Public/i', 
                    '__JS__'     => __ROOT__.'/Public/js', 
                    '__IMG__'    => __ROOT__.'/Public/images',
                    '__UPLOAD__' => __ROOT__.'/data/upload',
                    '__HOST__'   => "http://".$_SERVER['SERVER_NAME'],
    )
)
?>