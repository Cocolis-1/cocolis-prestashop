<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6832f015dfc0d8ecde151c1efa3ebb51
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PrestaShop\\Module\\Cocolis\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PrestaShop\\Module\\Cocolis\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit6832f015dfc0d8ecde151c1efa3ebb51::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit6832f015dfc0d8ecde151c1efa3ebb51::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}