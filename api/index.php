<?php
// api/index.php
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'prod';
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = 0;
$_SERVER['VERCEL'] = $_ENV['VERCEL'] = 1;
return require_once __DIR__ . '/../public/index.php';
