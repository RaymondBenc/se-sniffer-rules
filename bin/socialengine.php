<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
$options = getopt(null, ['path:']);
$path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/';
if (isset($options['path'])) {
  $path = rtrim($options['path'], '/') . '/';
}

if (!file_exists($autoload)) {
  $autoload = $path . 'application/vendor/autoload.php';
} else {
  spl_autoload_register(function ($class) {
    $prefix = 'SocialEngine\\Console\\';
    $base = __DIR__ . '/../src/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
      return;
    }

    $relativeClass = substr($class, $len);
    $file = $base . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
      require $file;
    }
  });
}

define('SE_CONSOLE_DIR', $path);

require($autoload);

new SocialEngine\Console\Console;
