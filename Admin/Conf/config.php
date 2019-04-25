<?php

$siteconfig = require './siteconfig.inc.php';
$config = array(
    /*
     * 0:普通模式 (采用传统癿URL参数模式 )
     * 1:PATHINFO模式(http://<serverName>/appName/module/action/id/1/)
     * 2:REWRITE模式(PATHINFO模式基础上隐藏index.php)
     * 3:兼容模式(普通模式和PATHINFO模式, 可以支持任何的运行环境, 如果你的环境不支持PATHINFO 请设置为3)
     */
    'URL_MODEL' => 1,
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'liaocheng',
    'DB_USER' => 'root',
    'DB_PWD' => 'qqq111',
    
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'tb_',
    'APP_AUTOLOAD_PATH' => '@.TagLib', //

    /* SESSION配置 */
    'SESSION_AUTO_START' => true,
    'SESSION_OPTIONS' => array(
        'expire' => 3600 * 24, //SESSION保存1天
        'use_trans_sid' => 1, //跨页传递
        'use_only_cookies' => 1, //是否只开启基于cookies的session的会话方式
    ), //

    /* 权限配置 */
    'VAR_PAGE' => 'pageNum',
    'USER_AUTH_ON' => true,
    'USER_AUTH_TYPE' => 1, // 默认认证类型 1 登录认证 2 实时认证
    'USER_AUTH_KEY' => 'authId', // 用户认证SESSION标记
    'ADMIN_AUTH_KEY' => 'administrator',
    'USER_AUTH_MODEL' => 'User', // 默认验证数据表模型
    'AUTH_PWD_ENCODER' => 'md5', // 用户认证密码加密方式
    'USER_AUTH_GATEWAY' => '/Public/login', // 默认认证网关
    'NOT_AUTH_MODULE' => 'Public', // 默认无需认证模块
    'REQUIRE_AUTH_MODULE' => '', // 默认需要认证模块
    'NOT_AUTH_ACTION' => '', // 默认无需认证操作
    'REQUIRE_AUTH_ACTION' => '', // 默认需要认证操作
    'GUEST_AUTH_ON' => false, // 是否开启游客授权访问
    'GUEST_AUTH_ID' => 0, // 游客的用户ID
    'DB_LIKE_FIELDS' => 'title|remark',
    'RBAC_ROLE_TABLE' => 'tb_role',
    'RBAC_USER_TABLE' => 'tb_role_user',
    'RBAC_ACCESS_TABLE' => 'tb_access',
    'RBAC_NODE_TABLE' => 'tb_node', //

    /* 数据缓存设置 */
    'DATA_CACHE_TIME' => 3600, // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_TYPE' => 'File', // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_SUBDIR' => true, // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)

    /* 自定义配置 */
    'UPLOAD_PATH' => './Public/upload/',
    'WEB_PATH' => 'http://www.daoqiuxiang.top/dwz/Public/upload/',
);

return array_merge($config, $siteconfig);
?>
