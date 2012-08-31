<?php

spl_autoload_register(function($className) {
            $className = ltrim($className, '\\');
            $fileName = '';
            $namespace = '';
            if ($lastNsPos = strripos($className, '\\')) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
            $folder = dirname(dirname(__DIR__));
            
            if (file_exists($file = $folder . DIRECTORY_SEPARATOR . $fileName)) {
                require $file;
            }
        }
);
