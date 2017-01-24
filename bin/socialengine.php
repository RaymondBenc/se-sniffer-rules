#!/usr/bin/env php
<?php

define('SE_CONSOLE_DIR', __DIR__ . '/');

require(__DIR__ . '/application/vendor/autoload.php');

new SocialEngine\Console\Console;
