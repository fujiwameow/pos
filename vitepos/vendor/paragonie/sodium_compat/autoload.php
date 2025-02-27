<?php

if (PHP_VERSION_ID < 70000) {
    if (!is_callable('sodiumCompatAutoloader')) {
        /**
         * Sodium_Compat autoloader.
         *
         * @param string $class Class name to be autoloaded.
         *
         * @return bool         Stop autoloading?
         */
        function sodiumCompatAutoloader($class)
        {
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
        }

                spl_autoload_register('sodiumCompatAutoloader');
    }
} else {
    require_once dirname(__FILE__) . '/autoload-php7.php';
}


if (!class_exists('ParagonIE_Sodium_Compat', false)) {
    require_once dirname(__FILE__) . '/src/Compat.php';
}

if (!class_exists('SodiumException', false)) {
    require_once dirname(__FILE__) . '/src/SodiumException.php';
}
if (PHP_VERSION_ID >= 50300) {
            require_once dirname(__FILE__) . '/lib/namespaced.php';
    require_once dirname(__FILE__) . '/lib/sodium_compat.php';
} else {
    require_once dirname(__FILE__) . '/src/PHP52/SplFixedArray.php';
}
if (PHP_VERSION_ID < 70200 || !extension_loaded('sodium')) {
    if (PHP_VERSION_ID >= 50300 && !defined('SODIUM_CRYPTO_SCALARMULT_BYTES')) {
        require_once dirname(__FILE__) . '/lib/php72compat_const.php';
    }
    if (PHP_VERSION_ID >= 70000) {
        assert(class_exists('ParagonIE_Sodium_Compat'), 'Possible filesystem/autoloader bug?');
    } else {
        assert(class_exists('ParagonIE_Sodium_Compat'));
    }
    require_once(dirname(__FILE__) . '/lib/php72compat.php');
} elseif (!function_exists('sodium_crypto_stream_xchacha20_xor')) {
        require_once(dirname(__FILE__) . '/lib/php72compat.php');
}
require_once(dirname(__FILE__) . '/lib/stream-xchacha20.php');
require_once(dirname(__FILE__) . '/lib/ristretto255.php');
