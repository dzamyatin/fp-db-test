<?php

use FpDbTest\DatabaseTest;
use FpDbTest\ServiceLocator;

spl_autoload_register(function ($class) {
    $a = array_slice(explode('\\', $class), 1);
    if (!$a) {
        throw new Exception();
    }
    $filename = implode('/', [__DIR__, ...$a]) . '.php';
    require_once $filename;
});

$serviceLocator = new ServiceLocator(
    require __DIR__ .'/config/services.php',
    require __DIR__ .'/config/afterCreateServiceHooks.php'
);

$serviceLocator->get(DatabaseTest::class)->testBuildQuery();

exit('OK');
