<?php

require_once dirname(dirname(__FILE__)) . '/autoload.php';

if (PHP_VERSION_ID < 50300) {
    return;
}


spl_autoload_register(function ($class) {
    if ($class[0] === '\\') {
        $class = substr($class, 1);
    }
    $namespace = 'ParagonIE\\Sodium';
        $len = strlen($namespace);
    if (strncmp($namespace, $class, $len) !== 0) {
                return false;
    }

        $relative_class = substr($class, $len);

                $file = dirname(dirname(__FILE__)) . '/namespaced/' . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});
