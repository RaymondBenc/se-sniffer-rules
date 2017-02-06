<?php

echo "Working with PHP: " . PHP_VERSION . PHP_EOL;

$errors = array();
$files = '/app/docker-files.log';
foreach (file($files) as $file) {
    $file = trim($file);

    if (empty($file)) {
        continue;
    }

    $lines = explode("\n", shell_exec('php -d display_errors=1 -l ' . $file));
    foreach ($lines as $line) {
        $line = trim($line);

        if (empty($line) || preg_match('/no syntax errors/i', $line)) {
            continue;
        }

        if (!isset($errors[$file])) {
            $errors[$file] = array();
        }

        $errors[$file][] = $line;
    }
}

echo "<docker-error-log>";
echo var_export($errors, true);
echo "</docker-error-log>";
