<?php
require 'vendor/autoload.php';
use Auth0\SDK\Auth0;

$config = require 'config/auth0.php';
$auth0 = new Auth0($config);

// Redirige al login de Auth0
$auth0->login();
