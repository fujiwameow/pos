<?php

if (PHP_VERSION_ID < 70000) {
    return;
}

spl_autoload_register(function ($class) {
    $namespace = 'ParagonIE_Sodium_';
        $len = strlen($namespace);
    if (strncmp($namespace, $class, $len) !== 0) {
                return false;
    }

        $relative_class = substr($class, $len);

                $file = dirname(__FILE__) . '/src/' . str_replace('_', '/', $relative_class) . '.php';
        if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
});
