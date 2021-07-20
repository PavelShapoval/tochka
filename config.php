<?php
// HTTP
define('HTTP_SERVER', 'http://tochka.loc/');

// HTTPS
define('HTTPS_SERVER', 'http://tochka.loc/');

// DIR
define('DIR_APPLICATION', 'E:/OPENSERVER2020/domains/localhost/tochka.loc/catalog/');
define('DIR_SYSTEM', 'E:/OPENSERVER2020/domains/localhost/tochka.loc/system/');
define('DIR_IMAGE', 'E:/OPENSERVER2020/domains/localhost/tochka.loc/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
define('TWIG_DEBUG', true);

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'mysql');
define('DB_PASSWORD', 'mysql');
define('DB_DATABASE', 'electro');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');