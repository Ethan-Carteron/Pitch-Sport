<?php
// api/index.php
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'prod';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = 0;
return require __DIR__ . '/../public/index.php';
