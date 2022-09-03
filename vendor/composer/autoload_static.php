<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita606c1cdbcae9bffb1c75f53784b6386
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PostTypes\\' => 10,
        ),
        'B' => 
        array (
            'BTBEvents\\Plugin\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PostTypes\\' => 
        array (
            0 => __DIR__ . '/..' . '/jjgrainger/posttypes/src',
        ),
        'BTBEvents\\Plugin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita606c1cdbcae9bffb1c75f53784b6386::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita606c1cdbcae9bffb1c75f53784b6386::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita606c1cdbcae9bffb1c75f53784b6386::$classMap;

        }, null, ClassLoader::class);
    }
}
