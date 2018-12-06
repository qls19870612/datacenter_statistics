<?php

/**
 * Created by PhpStorm.
 * User: Su
 * Date: 8/13/14
 * Time: 10:15
 */
class AutoLoader
{
    public static function autoload($className)
    {
        if (strpos($className, '\\') == false) {
            return;
        }

        $classFile = ROOTPATH .'/'. str_replace('\\', '/', $className) . '.php';
        if (!is_file($classFile)) {
            return;
        }

        include($classFile);

        if (DEBUG && !class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

}

spl_autoload_register(array('AutoLoader', 'autoload'), true, true);

