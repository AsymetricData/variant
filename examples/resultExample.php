<?php

use CedricCourteau\Variant\Users\Types\ApiResult;
use CedricCourteau\Variant\Users\Types\NotFound;
use CedricCourteau\Variant\Users\Types\Unauthorized;
use CedricCourteau\Variant\Users\Types\Unknown;

require 'vendor/autoload.php';

function callApi(): ApiResult
{
    $rnd = mt_rand(0, 3);
    return match($rnd) {
        0 => ApiResult::ok("Tout va bien, ouf !"),
        1 => ApiResult::error(new NotFound()),
        2 => ApiResult::error(new Unknown(500, message: 'Une erreur 500')),
        3 => ApiResult::error(new Unauthorized()),
    };
}

$r = callApi();
if ($r->isOk()) {
    echo $r->unwrap();
} else {
    $err = $r->getError();
    echo match($err::class) {
        NotFound::class => 'Page non trouvée',
        Unauthorized::class => 'Accès interdit !',
        Unknown::class => $err->message,
        default => "For the sake phpstan",
    };
}
