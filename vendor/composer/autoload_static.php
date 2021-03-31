<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit25e6a4139d918b21922886b776894607
{
    public static $prefixLengthsPsr4 = array (
        'L' => 
        array (
            'LINE\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'LINE\\' => 
        array (
            0 => __DIR__ . '/..' . '/linecorp/line-bot-sdk/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit25e6a4139d918b21922886b776894607::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit25e6a4139d918b21922886b776894607::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit25e6a4139d918b21922886b776894607::$classMap;

        }, null, ClassLoader::class);
    }
}
