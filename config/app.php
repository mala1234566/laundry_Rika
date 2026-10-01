<?php

$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$viewsPosition = strpos($scriptPath, '/views/');
$applicationPath = $viewsPosition === false
	? str_replace('\\', '/', dirname($scriptPath))
	: substr($scriptPath, 0, $viewsPosition);
$applicationPath = $applicationPath === '/' ? '' : rtrim($applicationPath, '/');

define('BASE_URL', $applicationPath . '/');