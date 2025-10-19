<?php
require 'vendor/autoload.php';
use Auth0\SDK\Auth0;

$config = require 'config/auth0.php';
$auth0 = new Auth0($config);

session_start();
session_destroy();

// Redirige al logout de Auth0
$auth0->logout('http://localhost:8000/login.php');
exit;