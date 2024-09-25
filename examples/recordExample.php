<?php

use CedricCourteau\Variant\Users\Types\CustomUser;
use CedricCourteau\Variant\Users\Types\User;

require 'vendor/autoload.php';

$user = new User("Cédric", 0);
$customUser = new CustomUser("Cédric", 0);

// Error, properties are read-only
$user->name = "lol";
