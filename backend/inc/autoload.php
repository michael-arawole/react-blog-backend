<?php 
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (Logad Networks)
// | @author_url    : https://www.logad.net
// | @author_email  : logadscripts@gmail.com
// | @date          : 19 Sep, 2022 03:22PM
// +------------------------------------------------------------------------+

// +----------------------------+
// | Class Autoloader
// +----------------------------+

define('APP_BASE', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
spl_autoload_register('appAutoLoader');

function appAutoLoader($className) {
    $fullPath = __DIR__. "/../classes/" . $className . ".class.php";
    if (file_exists($fullPath)) {
        include_once $fullPath;
    }
    return false;
}