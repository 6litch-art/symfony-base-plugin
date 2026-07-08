<?php

// Package is either standalone (CI: own vendor/) or installed inside a host app.
foreach ([
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
] as $autoload) {
    if (file_exists($autoload)) {
        $loader = require $autoload;

        if ($loader instanceof \Composer\Autoload\ClassLoader) {
            $loader->addPsr4('Base\\Composer\\Tests\\', __DIR__);
        }

        return;
    }
}

throw new RuntimeException('No composer autoloader found. Run "composer install" first.');
