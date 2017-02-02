<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
$options = getopt(null, ['path:', 'docgenerator']);
$path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/';
$base = __DIR__ . '/../';
$configFile = $base . '.config.json';
$config = [];

if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
}

if (isset($options['path'])) {
    $config['path'] = rtrim($options['path'], '/') . '/';
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

require($autoload);

try {
    $console = new SocialEngine\Console\Console();
    if (isset($options['docgenerator'])) {
        new SocialEngine\Console\Helper\DocGenerator($console);
    }
    $console->run();
} catch (Exception $e) {
    fwrite(STDOUT, $e->getMessage() . PHP_EOL);
    exit(1);
}
