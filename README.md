# 环境设置

## Nginx 配置:

```
server {
    listen       80;
    server_name  allwood.tp.com;
    root   /mnt/hgfs/www/tp_wood;
    charset     utf-8;
    index       index.php index.html index.htm;
    error_log /data001/nginxlog/tp_allwood.error.log error;
      access_log /data001/nginxlog/tp_allwood.access.cn.log main;

   try_files   $uri $uri/ @rewrite;
    location @rewrite {
        rewrite ^/(.*)$ /index.php?s=/$1;
    }

    location ~ \.php$ {
        fastcgi_pass    127.0.0.1:9000;
        fastcgi_index   index.php;
        include        fastcgi_params;
         client_max_body_size 5m;
        fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    }
}

```

# 项目安装

## 自动安装运行环境

1.从gitlab 上拉取代码
```
git clone http://git.sztesu.com/lushixin/tp_wood.git
```

2.进入~/Application/Common/Conf/目录， 将所有带.sample的文件复制一份，去掉.sample,如config.sample.ini.php 去掉 .sample 后 为config.ini.php


3.复制图片资源文件
拷贝  \\192.168.0.225\public  下 upload20160701.rar 文件，解压到~/data/目录下，覆盖upload 文件夹

6.成功


##检查UTF-8

find . -type f -iname "*.html"  -o -iname "*.txt" -o -iname "*.md" -o -iname "*.sql" -o -iname "*.html"  | xargs -I {} bash -c "iconv -f utf-8 -t utf-16 {} &>/dev/null || echo {}"

##检查BOM

grep -rl --include \*.txt  --include \*.php --include \*.md  --include \*.html  --include \*.sql $'\xEF\xBB\xBF' .



p.allwood.com.cn 测试账号

买家账号：
username:18666666666
pwd:123456

经销商账号：
username:12222222222
pwd:123456a

装修公司账号：
username:13333333333
pwd:123456a

工厂账号：
username:14444444444
pwd:123456a

