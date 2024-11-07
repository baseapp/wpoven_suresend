<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitde26600883c79fc5938b1f1a6d41b83e
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitde26600883c79fc5938b1f1a6d41b83e', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitde26600883c79fc5938b1f1a6d41b83e', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        \Composer\Autoload\ComposerStaticInitde26600883c79fc5938b1f1a6d41b83e::getInitializer($loader)();

        $loader->register(true);

        $includeFiles = \Composer\Autoload\ComposerStaticInitde26600883c79fc5938b1f1a6d41b83e::$files;
        foreach ($includeFiles as $fileIdentifier => $file) {
            composerRequirede26600883c79fc5938b1f1a6d41b83e($fileIdentifier, $file);
        }

        return $loader;
    }
}

/**
 * @param string $fileIdentifier
 * @param string $file
 * @return void
 */
function composerRequirede26600883c79fc5938b1f1a6d41b83e($fileIdentifier, $file)
{
    if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;

        require $file;
    }
}